<?php

namespace App\Services\AI;

use App\DTO\AI\AiAskDTO;
use App\Exceptions\ServiceUnavailableException;
use App\Exceptions\TooManyRequestsException;
use App\Models\AiInsightLog;
use App\Models\User;
use App\Services\Report\ReportService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class AiInsightService
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    public function insights(User $user): array
    {
        $key = "ai:insights:{$user->id}:" . now()->format('Y-m-d-H');

        return Cache::remember($key, (int) config('app.ai_insights_cache_ttl', 3600), function () use ($user) {
            $summary  = $this->reportService->summary($user->id);
            $context     = $this->buildContext($user, $summary);
            $prompt      = $this->insightPrompt($context);
            $response    = $this->callAI($prompt, 'insights', $user->id, 1000);

            $this->log($user->id, 'insights', $prompt, $response, $context);

            return [
                'period'          => now()->format('F Y'),
                'summary'         => $response['summary']          ?? '',
                'trend_analysis'  => $response['trend_analysis']   ?? '',
                'health_score'    => $response['health_score']      ?? null,
                'recommendations' => $response['recommendations']   ?? [],
                'predicted_next'  => $response['predicted_next_month'] ?? null,
                'generated_at'    => now()->toIso8601String(),
                'cached_until'    => now()->addHour()->toIso8601String(),
            ];
        });
    }

    public function ask(AiAskDTO $dto): array
    {
        $rateKey = "ai:ask:rate:{$dto->userId}:" . now()->format('Y-m-d');
        $count   = Cache::get($rateKey, 0);

        if ($count >= 10) {
            throw new TooManyRequestsException(
                'Batas 10 pertanyaan per hari tercapai. Coba lagi besok.'
            );
        }

        $promptHash   = md5($dto->userId . ':' . strtolower(trim($dto->question)));
        $cached       = Cache::get("ai:ask:cache:{$promptHash}");

        if ($cached) {
            return array_merge($cached, ['from_cache' => true]);
        }

        $user    = User::find($dto->userId);
        $summary = $this->reportService->summary($dto->userId);
        $context = $this->buildContext($user, $summary);
        $prompt  = $this->askPrompt($dto->question, $context);
        $response = $this->callAI($prompt, 'ask', $dto->userId, 500);

        $this->log($dto->userId, 'ask', $prompt, $response, $context);

        Cache::put($rateKey, $count + 1, now()->endOfDay());

        $result = [
            'question'   => $dto->question,
            'answer'     => $response['answer'] ?? '',
            'context'    => $context,
            'asked_at'   => now()->toIso8601String(),
            'from_cache' => false,
        ];

        Cache::put("ai:ask:cache:{$promptHash}", $result, 7200);

        return $result;
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function buildContext(User $user, array $summary): array
    {
        $m = $summary['this_month'];
        $c = $summary['comparison_prev_month'];

        return [
            'business_name'   => $user->business_name,
            'business_type'   => $user->business_type,
            'current_month'   => now()->format('F Y'),
            'income'          => $m['income'],
            'expense'         => $m['expense'],
            'profit'          => $m['profit'],
            'profit_margin'   => $m['profit_margin'],
            'income_pct_chg'  => $c['income_pct'],
            'expense_pct_chg' => $c['expense_pct'],
            'profit_pct_chg'  => $c['profit_pct'],
        ];
    }

    private function insightPrompt(array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
        Kamu adalah analis keuangan UMKM Indonesia.

        ATURAN WAJIB:

        1. Analisis hanya berdasarkan data yang diberikan.
        2. Jangan menambahkan informasi yang tidak ada pada data.
        3. Jangan mengarang angka.
        4. Jangan menggunakan pengetahuan eksternal.
        5. Semua rekomendasi harus berasal dari data yang tersedia.

        DATA:
        {$json}

        Balas HANYA JSON valid:

        {
        "summary": "2-3 kalimat kondisi bisnis bulan ini",
        "trend_analysis": "2-3 kalimat analisis tren dibanding bulan sebelumnya",
        "health_score": 75,
        "recommendations": [
            {
            "type": "warning",
            "title": "...",
            "message": "..."
            }
        ],
        "predicted_next_month": {
            "income": 0,
            "expense": 0,
            "profit": 0,
            "confidence": "low"
        }
        }
        PROMPT;
    }

    private function askPrompt(string $question, array $context): string
    {
        $json = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
        Kamu adalah AI Asisten Keuangan UMKM.

        ATURAN WAJIB:

        1. Jawab HANYA berdasarkan data yang diberikan.
        2. Jangan menggunakan pengetahuan umum, internet, atau informasi di luar data.
        3. Jika pertanyaan tidak berhubungan dengan data keuangan yang diberikan, tolak dengan sopan.
        4. Jika data yang diperlukan tidak tersedia, katakan bahwa data tidak tersedia.
        5. Jangan membuat asumsi atau mengarang informasi.
        6. Fokus hanya pada pendapatan, pengeluaran, laba, margin laba, tren, dan kondisi bisnis.

        DATA KEUANGAN:
        {$json}

        PERTANYAAN:
        {$question}

        Jika pertanyaan tidak relevan dengan data keuangan di atas, balas:

        {
            "answer": "Maaf, saya hanya dapat menjawab pertanyaan yang berkaitan dengan data keuangan bisnis Anda."
        }

        Jika relevan, balas HANYA JSON:

        {
        "answer": "..."
        }
        PROMPT;
    }

    private function callAI(string $prompt, string $type, string $userId, int $maxTokens): array
    {
        try {

            $result = OpenAI::chat()->create([
                'model' => env('OPENAI_MODEL', 'openai/gpt-oss-120b:free'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Selalu balas JSON valid tanpa markdown dan tanpa penjelasan tambahan.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => 0.7,
            ]);

            $content = trim(
                $result->choices[0]->message->content ?? ''
            );

            // Hapus markdown jika ada
            $content = preg_replace('/^```json\s*/i', '', $content);
            $content = preg_replace('/^```\s*/i', '', $content);
            $content = preg_replace('/\s*```$/', '', $content);

            $decoded = json_decode($content, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }

            Log::warning('AI returned non-json response', [
                'user' => $userId,
                'type' => $type,
                'response' => $content,
            ]);

            return [
                'answer' => $content,
                'summary' => $content,
            ];

        } catch (\Throwable $e) {

            Log::error('AI error', [
                'user' => $userId,
                'type' => $type,
                'class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException(
                'Layanan AI sedang tidak tersedia.'
            );
        }
    }

    private function log(string $userId, string $type, string $prompt, array $response, array $context): void
    {
        AiInsightLog::create([
            'user_id'         => $userId,
            'type'            => $type,
            'prompt_hash'     => md5($prompt),
            'response_text'   => json_encode($response),
            'model'           => env('OPENAI_MODEL', 'openai/gpt-oss-120b:free'),
            'context_summary' => $context,
        ]);
    }
}

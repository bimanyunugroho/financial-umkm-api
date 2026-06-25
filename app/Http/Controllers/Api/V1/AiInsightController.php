<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\AI\AiAskDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\AiAskRequest;
use App\Services\AI\AiInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags AI Insights
 */
class AiInsightController extends Controller
{
    public function __construct(
        private readonly AiInsightService $aiService,
    ) {}

    /**
     * Auto-generated AI financial insights for the current month.
     * Response is cached per-user for 1 hour to reduce API costs.
     */
    public function insights(Request $request): JsonResponse
    {
        $data = $this->aiService->insights($request->user());

        return $this->ok($data, 'AI insights berhasil dianalisis.');
    }

    /**
     * Ask a free-form question about your finances.
     *
     * @bodyParam question string required Question in Bahasa Indonesia (5–500 chars).
     *   Example: Kenapa pengeluaran naik bulan ini?
     */
    public function ask(AiAskRequest $request): JsonResponse
    {
        $dto  = AiAskDTO::fromRequest($request->validated(), $request->user()->id);
        $data = $this->aiService->ask($dto);

        return $this->ok($data, 'Pertanyaan berhasil dijawab.');
    }
}

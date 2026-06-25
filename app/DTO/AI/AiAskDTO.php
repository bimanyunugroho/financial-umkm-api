<?php

namespace App\DTO\AI;

final class AiAskDTO
{
    public function __construct(
        public readonly string $userId,
        public readonly string $question,
    ) {}

    public static function fromRequest(array $validated, string $userId): self
    {
        return new self(
            userId:   $userId,
            question: $validated['question'],
        );
    }
}

<?php

namespace App\DTO\Category;

final class UpdateCategoryDTO
{
    public function __construct(
        public readonly ?string $name  = null,
        public readonly ?string $icon  = null,
        public readonly ?string $color = null,
    ) {}

    public static function fromRequest(array $validated): self
    {
        return new self(
            name:  $validated['name']  ?? null,
            icon:  $validated['icon']  ?? null,
            color: $validated['color'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name'  => $this->name,
            'icon'  => $this->icon,
            'color' => $this->color,
        ], fn ($v) => $v !== null);
    }
}

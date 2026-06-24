<?php

namespace App\DTO\Category;

final class StoreCategoryDTO
{
    public function __construct(
        public readonly string  $userId,
        public readonly string  $name,
        public readonly string  $type,
        public readonly string  $icon  = 'tag',
        public readonly string  $color = '#6366f1',
    ) {}

    public static function fromRequest(array $validated, string $userId): self
    {
        return new self(
            userId: $userId,
            name:   $validated['name'],
            type:   $validated['type'],
            icon:   $validated['icon']  ?? 'tag',
            color:  $validated['color'] ?? '#6366f1',
        );
    }

    public function toArray(): array
    {
        return [
            'user_id'    => $this->userId,
            'name'       => $this->name,
            'type'       => $this->type,
            'icon'       => $this->icon,
            'color'      => $this->color,
            'is_default' => false,
        ];
    }
}

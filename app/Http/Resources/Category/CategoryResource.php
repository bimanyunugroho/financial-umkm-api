<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'type'       => $this->type->value,
            'type_label' => $this->type->label(),
            'icon'       => $this->icon,
            'color'      => $this->color,
            'is_default' => (bool) $this->is_default,
            'is_custom'  => $this->user_id !== null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}

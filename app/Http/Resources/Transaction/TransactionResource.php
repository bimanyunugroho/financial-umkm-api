<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'type'             => $this->type->value,
            'type_label'       => $this->type->label(),
            'amount'           => (float) $this->amount,
            'amount_formatted' => 'Rp ' . number_format($this->amount, 0, ',', '.'),
            'description'      => $this->description,
            'date'             => $this->date->toDateString(),
            'payment_method'   => $this->payment_method->value,
            'payment_label'    => $this->payment_method->label(),
            'notes'            => $this->notes,
            'reference_number' => $this->reference_number,
            'is_deleted'       => $this->trashed(),
            'category'         => $this->whenLoaded('category', fn () => [
                'id'    => $this->category->id,
                'name'  => $this->category->name,
                'type'  => $this->category->type->value,
                'icon'  => $this->category->icon,
                'color' => $this->category->color,
            ]),
            'deleted_at'  => $this->deleted_at?->toIso8601String(),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources\Activity;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'event'        => $this->event,
            'description'  => $this->description,
            'subject_type' => class_basename($this->subject_type ?? ''),
            'subject_id'   => $this->subject_id,
            'changes'      => $this->changes,
            'ip_address'   => $this->properties['ip']         ?? null,
            'user_agent'   => $this->properties['user_agent'] ?? null,
            'created_at'   => $this->created_at->toIso8601String()
        ];
    }
}

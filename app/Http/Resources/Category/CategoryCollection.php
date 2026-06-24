<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    public $collects = CategoryResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'total'   => $this->collection->count(),
                'income'  => $this->collection->filter(fn ($c) => $c->type->value === 'income')->count(),
                'expense' => $this->collection->filter(fn ($c) => $c->type->value === 'expense')->count(),
            ],
        ];
    }
}

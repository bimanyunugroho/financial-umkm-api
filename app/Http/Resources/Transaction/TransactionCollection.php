<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TransactionCollection extends ResourceCollection
{
    public $collects = TransactionResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->toArray();
    }

    public function with(Request $request): array
    {
        return [];
    }

    public function paginationInformation(Request $request, array $paginated, array $default): array
    {
        return [];
    }
}
<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Transaction\StoreTransactionDTO;
use App\DTO\Transaction\TransactionFilterDTO;
use App\DTO\Transaction\UpdateTransactionDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\Transaction\TransactionCollection;
use App\Http\Resources\Transaction\TransactionResource;
use App\Services\Transaction\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Transactions
 */
class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionService $transactionService,
    ) {}

    /**
     * List transactions with filtering, sorting, and pagination.
     *
     * @queryParam filter[type] string Filter by type: income|expense. Example: income
     * @queryParam filter[category_id] string Filter by category UUID.
     * @queryParam filter[payment_method] string Filter by payment method.
     * @queryParam filter[date_from] date Start date. Example: 2024-01-01
     * @queryParam filter[date_to] date End date. Example: 2024-12-31
     * @queryParam filter[min_amount] number Minimum amount. Example: 100000
     * @queryParam filter[max_amount] number Maximum amount. Example: 5000000
     * @queryParam filter[description] string Search description.
     * @queryParam sort string Sort field, prefix - for desc. Example: -date
     * @queryParam per_page int Items per page (max 50). Default: 15.
     */
    public function index(Request $request): JsonResponse
    {
        $filter       = TransactionFilterDTO::fromRequest($request->query());
        $transactions = $this->transactionService->list($request->user()->id, $filter);
 
        return response()->json([
            'success' => true,
            'message' => 'Daftar transaksi.',
            'data'    => TransactionResource::collection($transactions->items())->resolve($request),
            'meta'    => [
                'current_page'  => $transactions->currentPage(),
                'per_page'      => $transactions->perPage(),
                'total'         => $transactions->total(),
                'last_page'     => $transactions->lastPage(),
                'from'          => $transactions->firstItem(),
                'to'            => $transactions->lastItem(),
                'has_more'      => $transactions->hasMorePages(),
                'next_page_url' => $transactions->nextPageUrl(),
                'prev_page_url' => $transactions->previousPageUrl(),
            ],
        ], 200);
    }

    /**
     * Create a new transaction.
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $dto         = StoreTransactionDTO::fromRequest($request->validated(), $request->user()->id);
        $transaction = $this->transactionService->store($dto);

        return $this->created(
            new TransactionResource($transaction),
            'Transaksi berhasil dibuat.'
        );
    }

    /**
     * Get transaction detail.
     *
     * @urlParam id string required Transaction UUID.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $transaction = $this->transactionService->findOrFail($id, $request->user()->id);

        return $this->ok(
            new TransactionResource($transaction),
            'Detail transaksi.'
        );
    }

    /**
     * Update a transaction.
     *
     * @urlParam id string required Transaction UUID.
     */
    public function update(UpdateTransactionRequest $request, string $id): JsonResponse
    {
        $dto         = UpdateTransactionDTO::fromRequest($request->validated());
        $transaction = $this->transactionService->update($id, $request->user()->id, $dto);

        return $this->ok(
            new TransactionResource($transaction),
            'Transaksi berhasil diperbarui.'
        );
    }

    /**
     * Soft-delete a transaction.
     *
     * @urlParam id string required Transaction UUID.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $this->transactionService->delete($id, $request->user()->id);

        return $this->noContent('Transaksi berhasil dihapus.');
    }

    /**
     * Restore a soft-deleted transaction.
     *
     * @urlParam id string required Transaction UUID.
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        $this->transactionService->restore($id, $request->user()->id);

        return $this->noContent('Transaksi berhasil dipulihkan.');
    }
}

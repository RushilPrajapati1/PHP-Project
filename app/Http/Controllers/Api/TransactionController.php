<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTransactionRequest;
use App\Http\Requests\Api\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

/**
 * Shallow nesting: index/store live under /accounts/{account}/transactions,
 * show/update/destroy under /transactions/{transaction}. Ownership is always
 * checked against the parent account via AccountPolicy.
 */
class TransactionController extends Controller
{
    public function index(Account $account): AnonymousResourceCollection
    {
        Gate::authorize('view', $account);

        $transactions = $account->transactions()
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(25);

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request, Account $account): JsonResponse
    {
        $transaction = $account->transactions()->create($request->validated());

        return TransactionResource::make($transaction)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        Gate::authorize('view', $transaction->account);

        return TransactionResource::make($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): TransactionResource
    {
        $transaction->update($request->validated());

        return TransactionResource::make($transaction);
    }

    public function destroy(Transaction $transaction): Response
    {
        Gate::authorize('update', $transaction->account);

        $transaction->delete();

        return response()->noContent();
    }
}

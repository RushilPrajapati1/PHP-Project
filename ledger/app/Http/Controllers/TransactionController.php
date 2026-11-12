<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Api\StoreTransactionRequest;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function store(StoreTransactionRequest $request, Account $account): RedirectResponse
    {
        $account->transactions()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transaction added.']);

        // back() keeps the user on the same month/page of the account screen.
        return back();
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction->account);

        $transaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Transaction deleted.']);

        return back();
    }
}

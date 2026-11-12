<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Enums\TransactionType;
use App\Http\Requests\Api\StoreAccountRequest;
use App\Http\Requests\Api\SummaryRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\SummaryResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Services\LedgerSummary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Web (Inertia) counterpart of Api\AccountController. It reuses the same Form Requests,
 * API Resources, policy and LedgerSummary service — only the response type differs.
 */
class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $accounts = $request->user()->accounts()->withBalance()->withCount('transactions')->orderBy('name')->get();

        return Inertia::render('accounts/index', [
            'accounts' => AccountResource::collection($accounts)->resolve(),
            'accountTypes' => array_column(AccountType::cases(), 'value'),
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $account = $request->user()->accounts()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => "Account “{$account->name}” created."]);

        return to_route('accounts.index');
    }

    // SummaryRequest authorizes `view` on the account and validates ?month=YYYY-MM.
    public function show(SummaryRequest $request, Account $account, LedgerSummary $ledger): Response
    {
        $month = $request->month();

        $transactions = $account->transactions()
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('accounts/show', [
            'account' => AccountResource::make($account->loadBalance())->resolve(),
            'transactions' => TransactionResource::collection($transactions),
            'summary' => SummaryResource::make($ledger->forAccount($account, $month))->resolve(),
            'months' => [
                'previous' => $month->subMonth()->format('Y-m'),
                'next' => $month->addMonth()->format('Y-m'),
                'label' => $month->format('F Y'),
            ],
            'transactionTypes' => array_column(TransactionType::cases(), 'value'),
            'categories' => $account->transactions()->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function destroy(Account $account): RedirectResponse
    {
        Gate::authorize('delete', $account);

        $account->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => "Account “{$account->name}” deleted."]);

        return to_route('accounts.index');
    }
}

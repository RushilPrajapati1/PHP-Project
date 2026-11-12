<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SummaryRequest;
use App\Http\Resources\SummaryResource;
use App\Models\Account;
use App\Services\LedgerSummary;

class SummaryController extends Controller
{
    public function __invoke(SummaryRequest $request, Account $account, LedgerSummary $ledger): SummaryResource
    {
        return SummaryResource::make($ledger->forAccount($account, $request->month()));
    }
}

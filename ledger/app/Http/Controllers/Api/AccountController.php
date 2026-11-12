<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAccountRequest;
use App\Http\Requests\Api\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class AccountController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = $request->user()->accounts()->withBalance()->orderBy('name')->get();

        return AccountResource::collection($accounts);
    }

    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = $request->user()->accounts()->create($request->validated());

        return AccountResource::make($account)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Account $account): AccountResource
    {
        Gate::authorize('view', $account);

        return AccountResource::make($account->loadBalance());
    }

    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account->update($request->validated());

        return AccountResource::make($account->loadBalance());
    }

    public function destroy(Account $account): Response
    {
        Gate::authorize('delete', $account);

        $account->delete(); // transactions cascade at the database level

        return response()->noContent();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $q = Account::query()->withCount('contacts');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('legal_name', 'like', "%{$s}%")
                   ->orWhere('vat', 'like', "%{$s}%")
                   ->orWhere('domain', 'like', "%{$s}%");
            });
        }

        return $q->paginate(25);
    }

    public function store(StoreAccountRequest $request)
    {
        $data = $request->validated();
        $account = Account::create($data);
        return response()->json($account, 201);
    }

    public function show(Account $account)
    {
        $account->load(['contacts','groups','delegations','categories']);
        return $account;
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        return $account;
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return response()->noContent();
    }
}

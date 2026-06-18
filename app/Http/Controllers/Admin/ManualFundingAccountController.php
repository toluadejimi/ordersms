<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualFundingAccount;
use Illuminate\Http\Request;

class ManualFundingAccountController extends Controller
{
    public function index()
    {
        $account = ManualFundingAccount::latest()->first();
        return view('admin.manual_funding_account', compact('account'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_name' => 'required|string',
            'account_number' => 'required|string'
        ]);

        ManualFundingAccount::truncate(); // Only one active at a time
        ManualFundingAccount::create($request->only(['bank_name', 'account_name', 'account_number']));

        return back()->with('success', 'Manual funding account updated.');
    }
}

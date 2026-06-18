<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class ReferralController extends Controller
{
    public function index()
    {
        // Users that were referred by someone
        $referredUsers = User::whereNotNull('referred_by')
            ->with(['referrer']) // eager load who referred them
            ->latest()
            ->paginate(25);

        return view('admin.referrals.index', compact('referredUsers'));
    }
}

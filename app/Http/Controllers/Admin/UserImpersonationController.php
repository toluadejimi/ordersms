<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Admin;

class UserImpersonationController extends Controller
{
    public function impersonate($id)
{
    $user = User::findOrFail($id);

    // Store the admin's ID in the session
    session(['impersonate_admin_id' => Auth::guard('admin')->id()]);

    // Logout from the admin guard
    Auth::guard('admin')->logout();

    // Login as the user using the default web guard
    Auth::guard('web')->login($user);

    return redirect('/dashboard')->with('success', 'Now impersonating user.');
}

    public function leave()
    {
        if (session()->has('impersonate_admin_id')) {
            $adminId = session()->pull('impersonate_admin_id');
            Auth::loginUsingId($adminId);
        }

        return redirect('/admin/users')->with('success', 'You have returned to your admin account.');
    }
}

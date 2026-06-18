@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Update Profile Information -->
            <section class="bg-white shadow-md rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Update Profile</h2>
                <div class="border-t border-gray-200 pt-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <!-- Update Password -->
            <section class="bg-white shadow-md rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h2>
                <div class="border-t border-gray-200 pt-4">
                    @include('profile.partials.update-password-form')
                </div>
            </section>

            <!-- Delete Account -->
            <section class="bg-white shadow-md rounded-2xl p-6 sm:p-8">
                <h2 class="text-xl font-semibold text-red-600 mb-4">Delete Account</h2>
                <div class="border-t border-gray-200 pt-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>

        </div>
    </div>
@endsection

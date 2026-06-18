<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateReferralCodes extends Command
{
    protected $signature = 'referrals:generate-codes';
    protected $description = 'Generate referral codes for users without one';

    public function handle()
    {
        $users = User::whereNull('referral_code')->get();

        foreach ($users as $user) {
            $user->referral_code = strtoupper(Str::random(8));
            $user->save();
        }

        $this->info('Referral codes generated for ' . $users->count() . ' users.');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualFundingAccount extends Model
{
    protected $fillable = ['bank_name', 'account_name', 'account_number'];
}


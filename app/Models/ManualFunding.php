<?php
// app/Models/ManualFunding.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualFunding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'reference',
        'note',
        'proof',
        'status',
    ];
    // App\Models\ManualFunding.php

public function user()
{
    return $this->belongsTo(\App\Models\User::class);
}

}


<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable implements MustVerifyEmail
{
  

     


    use HasFactory, Notifiable,  HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'wallet',
    'referral_code',
    'referred_by',
    'referral_earnings',
    'first_deposit_made',
    'virtual_account_number',
    'virtual_account_bank',
    'virtual_account_name',
    'banned',  
    'last_chat_read_at',// ✅ block access if true
    'current_token',   // ✅ used for mobile app API tracking (optional)
    'session_id',      // ✅ required for web session enforcement
];






    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
      
       
        'wallet' => 'float', // ✅ Ensure wallet is always float
    
        'password' => 'hashed',
    ];

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function referrer()
{
    return $this->belongsTo(User::class, 'referred_by', 'referral_code');
}

}

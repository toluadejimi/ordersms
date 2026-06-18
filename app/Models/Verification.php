<?php
// app/Models/Verification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'server',
        'number',
        'phone',
        'activation_id',
        'status',
        'code',
        'price',
        'markup',
        'naira_amount',
        'service',
        'service_id',
        'api_cost',
        'country',
        'country_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'price'        => 'float',
        'naira_amount' => 'float',
        'api_cost'     => 'float',
    ];
}
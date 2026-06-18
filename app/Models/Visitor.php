<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['ip_address', 'user_agent', 'user_id', 'visit_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

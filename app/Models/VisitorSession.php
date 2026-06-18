<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorSession extends Model
{
    public $timestamps = false; // using visited_at directly
    protected $fillable = ['ip_address', 'user_id', 'user_agent', 'url', 'visited_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

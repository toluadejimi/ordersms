<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'sender_type',
        'receiver_id',
        'message',
        'image_path',
        'read_at',
        'deleted_by_user'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'deleted_by_user' => 'boolean',
    ];

    /**
     * Get the sender (polymorphic relation).
     */
    public function sender()
    {
        return $this->morphTo();
    }

    /**
     * Get the receiver (User model).
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope to exclude messages deleted by user (for user view)
     */
    public function scopeNotDeletedByUser($query)
    {
        return $query->where(function($q) {
            $q->whereNull('deleted_by_user')
              ->orWhere('deleted_by_user', false);
        });
    }

    /**
     * Scope to include all messages (for admin view)
     */
    public function scopeIncludeDeleted($query)
    {
        return $query; // No filtering for admin
    }

    /**
     * Check if message is deleted by user
     */
    public function isDeletedByUser()
    {
        return $this->deleted_by_user ?? false;
    }
}
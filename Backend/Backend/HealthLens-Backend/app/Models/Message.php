<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chat_id',
        'sender_id',
        'sender_type',
        'text',
        'ai_result',
        'type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'ai_result' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Sender relationship (only when sender_type = user)
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Media attached to this message (images/files)
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /* =========================
     |  Helpers
     ========================= */

    public function isFromUser(): bool
    {
        return $this->sender_type === 'user';
    }

    public function isFromAi(): bool
    {
        return $this->sender_type === 'ai';
    }
}

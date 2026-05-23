<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'last_message_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }
}

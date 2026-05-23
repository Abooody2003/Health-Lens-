<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'username',
        'password',
        'date_of_birth',
        'gender',
        'plan',
        'avatar',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth'     => 'date',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function surgeryAnalyses()
    {
        return $this->hasMany(SurgeryAnalysis::class);
    }

    public function surgeryReports()
    {
        return $this->hasMany(SurgeryReport::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /* =========================
     |  Helpers
     ========================= */

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the avatar URL (convert path to full URL)
     * This can be used as $user->avatar_url in code
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->attributes['avatar'] ?? null) {
            return null;
        }

        return url('storage/' . ltrim($this->attributes['avatar'], '/'));
    }
}

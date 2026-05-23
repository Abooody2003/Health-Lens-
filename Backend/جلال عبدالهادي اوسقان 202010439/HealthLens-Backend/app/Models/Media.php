<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mediable_id',
        'mediable_type',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Always append the full URL to API responses.
     */
    protected $appends = ['url'];

    /* =========================
     |  Relationships
     ========================= */

    public function mediable()
    {
        return $this->morphTo();
    }

    /* =========================
     |  Helpers
     ========================= */

    /**
     * Return a full URL for file_path so existing frontend code that
     * uses `file_path` directly receives an absolute URL.
     */
    public function getFilePathAttribute($value): string
    {
        return url('storage/' . ltrim($value, '/'));
    }

    public function getUrlAttribute(): string
    {
        return url('storage/' . ltrim($this->attributes['file_path'] ?? '', '/'));
    }
}

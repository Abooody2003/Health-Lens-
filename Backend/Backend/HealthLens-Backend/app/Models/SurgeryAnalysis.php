<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryAnalysis extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'age',
        'gender',
        'kmax',
        'cct',
        'astig_value',
        'kc_probability',
        'recommended_surgery',
        'rsb_um',
        'ablation_depth_um',
        'safety_warnings',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'kmax'              => 'float',
        'cct'               => 'float',
        'astig_value'       => 'float',
        'kc_probability'    => 'float',
        'rsb_um'            => 'float',
        'ablation_depth_um' => 'float',
        'safety_warnings'   => 'array',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /* =========================
     |  Helpers
     ========================= */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}

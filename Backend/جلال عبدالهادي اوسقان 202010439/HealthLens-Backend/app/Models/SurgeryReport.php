<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryReport extends Model
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
        'astigmatism',
        'kc_probability',
        'recommended_surgery',
        'rsb_um',
        'ablation_depth_um',
        'warnings',
        'eye',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'kmax' => 'float',
        'cct' => 'integer',
        'astigmatism' => 'float',
        'kc_probability' => 'float',
        'rsb_um' => 'integer',
        'ablation_depth_um' => 'integer',
        'warnings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
}

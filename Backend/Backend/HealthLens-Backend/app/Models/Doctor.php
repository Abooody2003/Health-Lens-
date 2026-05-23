<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'specialization_id',
        'city',
        'area',
        'address',
        'phone_number',
        'email',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}

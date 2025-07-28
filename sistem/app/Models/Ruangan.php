<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ruangan extends Model
{
    /** @use HasFactory<\Database\Factories\RuanganFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_ruangan',
        'slug',
    ];

    /**
     * Get the komputers for this ruangan.
     */
    public function komputers(): HasMany
    {
        return $this->hasMany(Komputer::class);
    }
}

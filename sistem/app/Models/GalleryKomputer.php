<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GalleryKomputer extends Model
{
    /** @use HasFactory<\Database\Factories\GalleryKomputerFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'asset_id',
        'image_path',
        'image_type',
    ];
    
    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the computer asset that owns this gallery image.
     */
    public function komputer(): BelongsTo
    {
        return $this->belongsTo(Komputer::class, 'asset_id');
    }
}

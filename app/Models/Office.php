<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use HasFactory, SoftDeletes;

    const APPROVAL_PENDING = 1;
    const APPROVAL_APPROVED = 2;

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'approval_status' => 'integer',
        'hidden' => 'boolean',
        'price_per_day' => 'integer',
        'monthly_discount' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function images(): MorphMany
    {
        return $this->morphMany(Image::class, 'resource');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'offices_tags');
    }


    /**
     * scopeNearestTo
     *
     * @param  Builder $query
     * @param  int $lat
     * @param  int $lng
     * @return Builder
     */
    public function scopeNearestTo($query, $lat, $lng)
    {
        return $query->select()
            ->selectRaw(
                'SQRT(POW(69.1 * (lat - ?), 2) + POW(69.1 * (lng - ?), 2)) AS distance',
                [$lat, $lng]
            )
            ->orderBy('distance');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes, Searchable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'vin',
        'make',
        'model',
        'year',
        'color',
        'mileage',
        'price',
        'original_price',
        'status',
        'is_featured',
        'description',
        'features',
        'images',
        'seller_id',
        'slug',
        'listed_at',
        'sold_at',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'year' => 'integer',
        'mileage' => 'integer',
        'is_featured' => 'boolean',
        'features' => 'array',
        'images' => 'array',
        'listed_at' => 'datetime',
        'sold_at' => 'datetime',
    ];

    /**
     * Boot the model and generate a unique slug when creating a new vehicle.
     */
    protected static function booted(): void
    {
        static::creating(function ($vehicle) {
            $vehicle->slug = Str::slug($vehicle->make . '-' . $vehicle->model . '-' . $vehicle->year . '-' . uniqid());
        });
    }

    // Relationships

    /**
     * Get the seller that owns the vehicle.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the orders for the vehicle.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the reviews for the vehicle.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    //Scopes

    /**
     * Scope a query to only include listed vehicles.
     */
    public function scopeListed($query)
    {
        return $query->where('status', 'listed');
    }

    /**
     * Scope a query to only include featured vehicles.
     */

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     *  Scope a query to only include vehicles of a specific make.
     */
    public function scopeByMake($query, $make)
    {
        return $query->where('make', $make);
    }

    /**
     * Scope a query to only include vehicles of a specific model.
     */

    public function scopeByModel($query, $model)
    {
        return $query->where('model', $model);
    }

    /**
     * Scope a query to only include vehicles within a specific price range.
     */
    public function scopePriceBetween($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    //Accessors

    /**
     * Get the full name of the vehicle (year, make, model).
     */
    public function getFullNameAttribute()
    {
        return $this->year . ' ' . $this->make . ' ' . $this->model;
    }

    //Mutators

    /**
     * Set the VIN attribute and ensure it is stored in uppercase.
     */
    public function setVinAttribute($value)
    {
        $this->attributes['vin'] = strtoupper($value);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'price' => (float) $this->price,
            'mileage' => $this->mileage,
            'description' => $this->description,
            'status' => $this->status,
            'seller_name' => $this->seller->name ?? null,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable()
    {
        return $this->status === 'listed';
    }

}

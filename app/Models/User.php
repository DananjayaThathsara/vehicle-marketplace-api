<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'type',
        'is_active',
        'is_verified',
        'bio',
        'avatar',
        'location',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    // Relationships

    /**
     *  Get the vehicles for the user.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'seller_id');
    }
    /**
     * Get the purchases for the user.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the sales for the user.
     */

    public function sales(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    /**
     * Get the reviews for the user.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes

    /**
     * Scope a query to only include sellers.
     *
     */
    public function scopeSellers($query)
    {
        return $query->where('type', 'seller');
    }

    /**
     * Scope a query to only include buyers.
     *
     */
    public function scopeBuyers($query)
    {
        return $query->where('type', 'buyer');
    }

    /**
     * Scope a query to only include active users.
     *
     */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessors

    /**
     * Can be used to determine if the user is a seller.
     */
    public function getIsSellerAttribute()
    {
        return $this->type === 'seller';
    }
    /**
     * Can be used to determine if the user is a buyer.
     */

    public function getIsBuyerAttribute()
    {
        return $this->type === 'buyer';
    }

    //Mutators

    /**
     * Mutator to hash the password when setting it.
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }


}

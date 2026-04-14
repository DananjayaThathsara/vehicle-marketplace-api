<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_number',
        'vehicle_id',
        'buyer_id',
        'seller_id',
        'amount',
        'tax',
        'total',
        'status',
        'payment_method',
        'payment_transaction_id',
        'paid_at',
        'metadata',
        'notes',
    ];

    /**
     * The attributes that should be cast to native types.
     */

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     *  The "booted" method of the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            // Auto-generate order number
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(uniqid());
            }

            // Auto-calculate total
            if (empty($order->total)) {
                $order->total = $order->amount + $order->tax;
            }
        });
    }

    //Relationships

    /**
     * Get the vehicle associated with the order.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the buyer that owns the order.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller that owns the order.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Scopes

    /**
     * Scope a query to only include pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed orders.
     */

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include paid orders.
     */
    public function scopePaid($query)
    {
        return $query->whereNotNull('paid_at');
    }

    //Helpers

    /**
     * Check if the order is paid.
     */

    public function isPaid(): bool
    {
        return !is_null($this->paid_at);
    }

    /**
     * Check if the order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Mark the order as paid.
     */

    public function markAsPaid(string $transactionId): void
    {
        $this->update([
            'paid_at' => now(),
            'payment_transaction_id' => $transactionId,
            'status' => 'payment_completed',
        ]);
    }
}

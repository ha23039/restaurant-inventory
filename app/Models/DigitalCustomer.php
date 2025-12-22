<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'phone',
        'country_code',
        'name',
        'email',
        'is_verified',
        'verification_code',
        'code_expires_at',
        'orders_count',
        'total_spent',
        'last_order_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'code_expires_at' => 'datetime',
        'last_order_at' => 'datetime',
        'total_spent' => 'decimal:2',
    ];

    protected $hidden = [
        'verification_code',
    ];

    /**
     * Get the sales (orders) made by this customer
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'digital_customer_id');
    }

    /**
     * Get full phone with country code
     */
    public function getFullPhoneAttribute(): string
    {
        return $this->country_code . ' ' . $this->phone;
    }

    /**
     * Generate a 6-digit verification code
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'verification_code' => $code,
            'code_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    /**
     * Verify the provided code
     */
    public function verifyCode(string $code): bool
    {
        if ($this->verification_code !== $code) {
            return false;
        }

        if ($this->code_expires_at && $this->code_expires_at->isPast()) {
            return false;
        }

        $this->update([
            'is_verified' => true,
            'verification_code' => null,
            'code_expires_at' => null,
        ]);

        return true;
    }

    /**
     * Update order statistics
     */
    public function incrementOrderStats(float $amount): void
    {
        $this->increment('orders_count');
        $this->increment('total_spent', $amount);
        $this->update(['last_order_at' => now()]);
    }

    /**
     * Scope for verified customers
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for active customers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

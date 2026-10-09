<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperProducts
 */
class Products extends Model
{
    use HasFactory;

    protected $guarded = [];
     protected $casts = [
            'base_price' => 'integer',
            'price_user' => 'integer',
            'price_agent' => 'integer',
            'price_guest' => 'integer',
            'raw_response' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function transactions()
    {
        return $this->hasMany(Transactions::class, 'product_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Price Helpers
    |--------------------------------------------------------------------------
    */

    public function priceForRole(string $role): int
    {
        return match ($role) {
            'user' => $this->price_user,
            'agen' => $this->price_agent,
            default => throw new \InvalidArgumentException(
                "Role tidak memiliki harga produk: {$role}"
            ),
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Produk yang benar-benar berasal dari IAK (sync API atau import price list).
     * Keduanya selalu mengisi raw_response; produk dummy/seeder/manual tidak.
     */
    public function scopeSyncedFromIak($query)
    {
        return $query->whereNotNull('raw_response');
    }

    /** Produk aktif DAN hasil sinkron IAK: satu-satunya yang boleh dijual. */
    public function scopeSellable($query)
    {
        return $query->where('status', 'active')->syncedFromIak();
    }

    public function isSyncedFromIak(): bool
    {
        return ! empty($this->raw_response);
    }

    public function commissionRules()
    {
        return $this->hasMany(CommissionRule::class, 'product_id');
}
}

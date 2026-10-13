<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Support\Tenancy;
use Laravel\Sanctum\HasApiTokens;

/**
 * @mixin IdeHelperUser
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'parent_agent_id',
        'subdomain',
        'store_name',
        'name',
        'store_name',
        'subdomain',
        'email',
        'phone',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
    ];

    /**
     * Setiap agen WAJIB punya subdomain (toko), apa pun jalur pendaftarannya
     * (web, API/mobile, seeder). Kalau belum diisi, dibuat otomatis dari nama.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->role === 'agen') {
                if (blank($user->store_name)) {
                    $user->store_name = $user->name;
                }

                if (blank($user->subdomain)) {
                    $user->subdomain = Tenancy::uniqueSubdomain($user->store_name);
                }
            }
        });
    }

    /** Nama yang tampil sebagai nama toko. */
    public function storeLabel(): string
    {
        return $this->store_name ?: $this->name;
    }

    /** Alamat toko tanpa skema, mis. "ospay.test/tokoabc" (mode path) atau "tokoabc.ospay.test". */
    public function storeHost(): ?string
    {
        return $this->subdomain ? Tenancy::hostFor($this->subdomain) : null;
    }

    /*
     * ================================================================
     * ROLE
     * ================================================================
     */

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAgent(): bool
    {
        return $this->role === 'agen';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /*
     * ================================================================
     * STATUS
     * ================================================================
     */

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Akun ini milik toko tersebut? (pemilik toko atau sub-user-nya)
     */
    public function belongsToTenant(self $tenant): bool
    {
        return Tenancy::belongsToStore($this, $tenant);
    }

    /*
     * ================================================================
     * WALLET
     * ================================================================
     *
     * Hanya agen yang seharusnya mempunyai wallet.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(
            Wallets::class,
            'user_id'
        );
    }

    /*
     * ================================================================
     * TRANSACTIONS
     * ================================================================
     */

    public function transactions(): HasMany
    {
        return $this->hasMany(
            Transactions::class
        );
    }

    /*
     * ================================================================
     * DEPOSITS
     * ================================================================
     */

    public function deposits(): HasMany
    {
        return $this->hasMany(
            Deposits::class
        );
    }

    /*
     * ================================================================
     * PARENT AGENT
     * ================================================================
     */

    public function parentAgent(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'parent_agent_id'
        );
    }

    /*
     * ================================================================
     * SUB USERS
     * ================================================================
     */

    public function subUsers(): HasMany
    {
        return $this->hasMany(
            User::class,
            'parent_agent_id'
        );
    }

    /*
     * ================================================================
     * COMMISSIONS
     * ================================================================
     */

    public function commissions(): HasMany
    {
        return $this->hasMany(
            Commissions::class,
            'agent_id'
        );
    }
}

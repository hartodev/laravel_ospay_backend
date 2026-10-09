<?php

namespace App\Services;

use App\Models\Products;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pembelian tanpa login (tamu / guest) dari landing page.
 *
 * Semua transaksi tamu dimiliki SATU akun sistem "Tamu" (role user, status
 * suspended supaya tidak bisa dipakai login), sehingga memakai alur yang sama
 * dengan pembeli biasa: QRIS Midtrans -> callback -> order ke IAK. Tidak ada
 * perubahan tabel. Pembeli melacak pesanan memakai KODE TRANSAKSI, yaitu
 * ref_id (TRX- + 20 karakter acak), jadi tidak bisa ditebak.
 */
class GuestCheckoutService
{
    public const GUEST_EMAIL = 'guest@example.com';

    public function __construct(private TransactionService $transactionService) {}

    /** Akun sistem pemilik semua transaksi tamu (dibuat otomatis saat pertama dipakai). */
    public function guestUser(): User
    {
        return User::query()->firstOrCreate(
            ['email' => self::GUEST_EMAIL],
            [
                'name' => 'Tamu OSPay',
                'password' => Str::random(64), // di-hash oleh cast; tidak pernah dipakai login
                'role' => 'user',
                'status' => 'suspended',
            ]
        );
    }

    /**
     * Produk yang boleh dibeli tamu: aktif, hasil sinkron IAK, harga tamu (price_guest) sudah diisi dan di atas modal
     * (sudah direview admin), dan bukan tagihan pasca bayar (butuh inquiry).
     */
    public function buyable(): Builder
    {
        return Products::query()
            ->sellable()
            ->whereNotNull('price_guest')
            ->whereColumn('price_guest', '>', 'base_price')
            ->where(function (Builder $q) {
                $q->whereNull('category')->orWhere('category', '!=', 'postpaid');
            });
    }

    public function findProduct(string $productCode): Products
    {
        return $this->buyable()->where('product_code', $productCode)->firstOrFail();
    }

    /** Buat transaksi tamu + QRIS. ValidationException dari service diteruskan ke form. */
    public function checkout(string $productCode, string $customerId): Transactions
    {
        $product = $this->buyable()->where('product_code', $productCode)->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product' => ['Produk ini tidak tersedia untuk pembelian tanpa daftar.'],
            ]);
        }

        return $this->transactionService->purchase(
            $this->guestUser(),
            $product,
            $customerId,
            (int) $product->price_guest
        );
    }

    public static function normalizeCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    /** Cari transaksi tamu dari kode. Transaksi pengguna terdaftar TIDAK bisa dibuka lewat sini. */
    public function find(string $code): ?Transactions
    {
        return Transactions::query()
            ->with('product')
            ->where('ref_id', self::normalizeCode($code))
            ->where('user_id', $this->guestUser()->id)
            ->first();
    }

    public function findOrFail(string $code): Transactions
    {
        return $this->find($code) ?? abort(404);
    }
}

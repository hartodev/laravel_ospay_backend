<?php

namespace App\Console\Commands;

use App\Models\Products;
use Illuminate\Console\Command;

/**
 * Atur margin banyak produk sekaligus. Default HANYA pratinjau (dry-run).
 *
 * Contoh:
 *   php artisan products:set-margin --type=pulsa --user-flat=500 --agent-flat=200 --guest-over-user=300
 *   php artisan products:set-margin --type=pulsa --user-flat=500 --agent-flat=200 --guest-over-user=300 --apply
 *
 * Tiap harga hanya diisi kalau BELUM diatur (user/agen: masih sama dengan modal,
 * tamu: masih kosong). Pakai --force untuk menimpa yang sudah diatur.
 */
class SetProductMargin extends Command
{
    protected $signature = 'products:set-margin
        {--type= : Filter kolom type (persis, mis. pulsa, data, pln)}
        {--brand= : Filter kolom brand (persis, mis. Telkomsel)}
        {--user-flat= : Margin harga user dalam rupiah}
        {--user-percent= : Margin harga user dalam persen dari modal}
        {--agent-flat= : Margin harga agen dalam rupiah}
        {--agent-percent= : Margin harga agen dalam persen dari modal}
        {--guest-flat= : Margin harga tamu dalam rupiah dari modal}
        {--guest-percent= : Margin harga tamu dalam persen dari modal}
        {--guest-over-user= : Harga tamu = harga user + rupiah ini}
        {--round=100 : Bulatkan harga ke atas ke kelipatan ini (0 = tanpa pembulatan)}
        {--force : Timpa juga harga yang sudah pernah diatur}
        {--apply : Simpan perubahan (tanpa ini hanya pratinjau)}';

    protected $description = 'Atur margin price_user/price_agent/price_guest produk hasil sinkron IAK secara massal.';

    public function handle(): int
    {
        $doUser = $this->has('user-flat') || $this->has('user-percent');
        $doAgent = $this->has('agent-flat') || $this->has('agent-percent');
        $doGuest = $this->has('guest-flat') || $this->has('guest-percent') || $this->has('guest-over-user');

        if (! $doUser && ! $doAgent && ! $doGuest) {
            $this->error('Isi minimal satu opsi margin (--user-*, --agent-*, --guest-*).');

            return self::FAILURE;
        }

        $force = (bool) $this->option('force');
        $apply = (bool) $this->option('apply');

        $query = Products::query()
            ->syncedFromIak()
            ->where('category', 'prepaid')
            ->where('base_price', '>', 0)
            ->when($this->option('type'), fn ($q, $v) => $q->where('type', $v))
            ->when($this->option('brand'), fn ($q, $v) => $q->where('brand', $v));

        $changed = 0;
        $sample = [];

        $query->orderBy('id')->chunkById(500, function ($chunk) use ($doUser, $doAgent, $doGuest, $force, $apply, &$changed, &$sample) {
            foreach ($chunk as $p) {
                $base = (int) $p->base_price;
                $new = [];

                if ($doUser && ($force || (int) $p->price_user === $base)) {
                    $new['price_user'] = $this->price($base, 'user');
                }
                if ($doAgent && ($force || (int) $p->price_agent === $base)) {
                    $new['price_agent'] = $this->price($base, 'agent');
                }
                if ($doGuest && ($force || $p->price_guest === null)) {
                    $userPrice = (int) ($new['price_user'] ?? $p->price_user);

                    if ($this->has('guest-over-user')) {
                        if ($userPrice <= $base) {
                            // Harga user belum diatur; tamu tidak boleh lebih murah dari acuan.
                            continue;
                        }
                        $guest = $this->round($userPrice + (int) $this->option('guest-over-user'));
                        $guest = max($guest, $base + 1);
                    } else {
                        $guest = $this->price($base, 'guest');
                    }

                    $new['price_guest'] = $guest;
                }

                if (! $new) {
                    continue;
                }

                if (count($sample) < 8) {
                    $sample[] = [
                        $p->product_code,
                        $base,
                        $new['price_user'] ?? $p->price_user,
                        $new['price_agent'] ?? $p->price_agent,
                        $new['price_guest'] ?? ($p->price_guest ?? '-'),
                    ];
                }

                if ($apply) {
                    $p->update($new);
                }

                $changed++;
            }
        });

        if ($changed === 0) {
            $this->warn('Tidak ada produk yang perlu diubah. Cek --type/--brand, atau pakai --force untuk menimpa harga lama.');

            return self::SUCCESS;
        }

        $this->table(['Kode', 'Modal', 'Harga user', 'Harga agen', 'Harga tamu'], $sample);

        $this->info($apply
            ? "{$changed} produk diperbarui."
            : "{$changed} produk akan diubah. Ini hanya pratinjau; tambahkan --apply untuk menyimpan.");

        return self::SUCCESS;
    }

    private function has(string $option): bool
    {
        return $this->option($option) !== null;
    }

    private function round(int $price): int
    {
        $round = (int) $this->option('round');

        return $round > 0 ? (int) (ceil($price / $round) * $round) : $price;
    }

    private function price(int $base, string $who): int
    {
        $price = $base;

        if ($this->has("{$who}-flat")) {
            $price += (int) $this->option("{$who}-flat");
        }
        if ($this->has("{$who}-percent")) {
            $price += (int) ceil($base * ((float) $this->option("{$who}-percent")) / 100);
        }

        // Selalu di atas modal supaya lolos filter.
        return max($this->round($price), $base + 1);
    }
}

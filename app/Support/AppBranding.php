<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Logo aplikasi mobile yang diatur superadmin.
 *
 * - Metadata disimpan di tabel app_settings (key "app.branding"), jadi
 *   tidak perlu migrasi baru.
 * - File gambar disimpan di storage/app/branding (disk "local") dan dikirim
 *   lewat GET /api/app/logo, jadi tidak perlu "php artisan storage:link".
 * - "version" naik setiap logo diganti. Dipakai sebagai ?v=... supaya HP
 *   tidak menampilkan logo lama dari cache.
 */
class AppBranding
{
    public const KEY = 'app.branding';
    public const DISK = 'local';
    public const DIR = 'branding';

    /** Cache per request, supaya layout yang memanggil logo berkali-kali tidak query ulang. */
    private static ?array $cache = null;

    /** @return array{logo_path: ?string, version: int} */
    public static function current(): array
    {
        return self::$cache ??= self::load();
    }

    /** @return array{logo_path: ?string, version: int} */
    private static function load(): array
    {
        $saved = AppSetting::getValue(self::KEY);

        $path = is_array($saved) ? ($saved['logo_path'] ?? null) : null;
        $version = is_array($saved) ? (int) ($saved['version'] ?? 1) : 1;

        // File sudah hilang dari disk -> anggap belum ada logo.
        if ($path && ! Storage::disk(self::DISK)->exists($path)) {
            $path = null;
        }

        return [
            'logo_path' => $path,
            'version' => max(1, $version),
        ];
    }

    public static function logoPath(): ?string
    {
        return self::current()['logo_path'];
    }

    /** Alamat relatif; aplikasi Flutter menambahkan alamat servernya sendiri. */
    public static function logoUrl(): ?string
    {
        $c = self::current();

        return $c['logo_path'] ? '/api/app/logo?v=' . $c['version'] : null;
    }

    /**
     * Alamat logo untuk tampilan web (navbar, halaman login, landing, favicon).
     * Memakai logo dari superadmin kalau ada, kalau tidak logo bawaan di public/.
     */
    public static function webLogo(string $fallback = 'images/logo.png'): string
    {
        $c = self::current();

        return $c['logo_path']
            ? url('/api/app/logo') . '?v=' . $c['version']
            : asset($fallback);
    }

    public static function hasCustomLogo(): bool
    {
        return self::current()['logo_path'] !== null;
    }

    public static function store(UploadedFile $file): void
    {
        $current = self::current();

        $path = $file->store(self::DIR, self::DISK);

        if ($current['logo_path']) {
            Storage::disk(self::DISK)->delete($current['logo_path']);
        }

        AppSetting::setValue(self::KEY, [
            'logo_path' => $path,
            'version' => $current['version'] + 1,
        ]);

        self::$cache = null;
    }

    /** Kembali ke logo bawaan aplikasi. */
    public static function reset(): void
    {
        $current = self::current();

        if ($current['logo_path']) {
            Storage::disk(self::DISK)->delete($current['logo_path']);
        }

        AppSetting::setValue(self::KEY, [
            'logo_path' => null,
            'version' => $current['version'] + 1,
        ]);

        self::$cache = null;
    }
}

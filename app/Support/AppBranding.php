<?php

namespace App\Support;

use App\Models\AppSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

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

    /** Ukuran ikon launcher Android per folder mipmap (piksel). */
    public const ICON_SIZES = [
        'mipmap-mdpi' => 48,
        'mipmap-hdpi' => 72,
        'mipmap-xhdpi' => 96,
        'mipmap-xxhdpi' => 144,
        'mipmap-xxxhdpi' => 192,
    ];

    public const ICONS_KEY = 'app.icons';

    public static function iconPackSupported(): bool
    {
        return extension_loaded('gd') && class_exists(ZipArchive::class);
    }

    public static function isDensity(?string $d): bool
    {
        return $d !== null && array_key_exists($d, self::ICON_SIZES);
    }

    /** @return array{files: array<string,string>, version: int} */
    private static function iconSetting(): array
    {
        $v = AppSetting::getValue(self::ICONS_KEY);

        return [
            'files' => is_array($v) && is_array($v['files'] ?? null) ? $v['files'] : [],
            'version' => is_array($v) ? max(1, (int) ($v['version'] ?? 1)) : 1,
        ];
    }

    public static function iconVersion(): int
    {
        return self::iconSetting()['version'];
    }

    /** Ikon yang diunggah superadmin per kepadatan layar, mis. ['mipmap-hdpi' => 'branding/icons/mipmap-hdpi.png']. */
    public static function customIcons(): array
    {
        $out = [];
        foreach (self::iconSetting()['files'] as $folder => $path) {
            if (self::isDensity($folder) && Storage::disk(self::DISK)->exists($path)) {
                $out[$folder] = $path;
            }
        }

        return $out;
    }

    /** Simpan ikon kustom satu kepadatan: dipotong persegi dan di-resize persis ke ukurannya. */
    public static function storeIcon(string $folder, UploadedFile $file): void
    {
        if (! self::isDensity($folder)) {
            throw new RuntimeException('Jenis ikon tidak dikenal.');
        }
        if (! self::iconPackSupported()) {
            throw new RuntimeException('Ekstensi PHP gd dan zip belum aktif di server.');
        }

        $src = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $src) {
            throw new RuntimeException('File gambar tidak bisa dibaca.');
        }
        $png = self::resizeSquarePng($src, self::ICON_SIZES[$folder]);
        imagedestroy($src);

        $path = self::DIR . '/icons/' . $folder . '.png';
        Storage::disk(self::DISK)->put($path, $png);

        $cur = self::iconSetting();
        $cur['files'][$folder] = $path;
        AppSetting::setValue(self::ICONS_KEY, ['files' => $cur['files'], 'version' => $cur['version'] + 1]);
    }

    /** Hapus ikon kustom satu kepadatan, atau semuanya kalau $folder null. */
    public static function resetIcon(?string $folder = null): void
    {
        $cur = self::iconSetting();
        foreach ($cur['files'] as $f => $path) {
            if ($folder === null || $folder === $f) {
                Storage::disk(self::DISK)->delete($path);
                unset($cur['files'][$f]);
            }
        }
        AppSetting::setValue(self::ICONS_KEY, ['files' => $cur['files'], 'version' => $cur['version'] + 1]);
    }

    /** Byte PNG satu ikon: kustom kalau ada, kalau tidak dibuat otomatis dari logo. Null kalau keduanya tidak ada. */
    public static function iconPng(string $folder): ?string
    {
        if (! self::isDensity($folder)) {
            return null;
        }
        $custom = self::customIcons()[$folder] ?? null;
        if ($custom) {
            return Storage::disk(self::DISK)->get($custom);
        }
        $logo = self::logoPath();
        if (! $logo || ! self::iconPackSupported()) {
            return null;
        }
        $src = @imagecreatefromstring(Storage::disk(self::DISK)->get($logo));
        if (! $src) {
            return null;
        }
        $png = self::resizeSquarePng($src, self::ICON_SIZES[$folder]);
        imagedestroy($src);

        return $png;
    }

    /**
     * Buat ZIP ikon launcher Android. Per kepadatan memakai ikon kustom bila ada,
     * kalau tidak dibuat dari logo. Mengembalikan path file ZIP sementara.
     */
    public static function buildIconPack(): string
    {
        if (! self::iconPackSupported()) {
            throw new RuntimeException('Ekstensi PHP gd dan zip belum aktif di server.');
        }
        if (! self::logoPath() && ! self::customIcons()) {
            throw new RuntimeException('Belum ada logo atau ikon. Unggah logo atau ikon terlebih dahulu.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'ospay_icons_');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Gagal membuat file ZIP.');
        }

        foreach (array_keys(self::ICON_SIZES) as $folder) {
            $png = self::iconPng($folder);
            if ($png === null) {
                continue;
            }
            $zip->addFromString("res/{$folder}/ic_launcher.png", $png);
            $zip->addFromString("res/{$folder}/ic_launcher_round.png", $png);
        }

        if ($logo = self::logoPath()) {
            $src = @imagecreatefromstring(Storage::disk(self::DISK)->get($logo));
            if ($src) {
                $zip->addFromString('playstore/ic_launcher_512.png', self::resizeSquarePng($src, 512));
                imagedestroy($src);
            }
        }

        $zip->addFromString('BACA-DULU.txt',
            "Salin isi folder res/ ke android/app/src/main/res (atau src/dev/res dan src/prod/res kalau memakai flavor),\n"
            . "timpa file yang sama, lalu build ulang aplikasi.\n"
            . "ic_launcher_round.png hanya dipakai kalau AndroidManifest memakai android:roundIcon.\n");
        $zip->close();

        return $tmp;
    }

    /** @param \GdImage $src */
    private static function resizeSquarePng($src, int $px): string
    {
        $w = imagesx($src);
        $h = imagesy($src);
        $side = min($w, $h);
        $sx = intdiv($w - $side, 2);
        $sy = intdiv($h - $side, 2);

        $dst = imagecreatetruecolor($px, $px);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $px, $px, $side, $side);

        ob_start();
        imagepng($dst, null, 9);
        $png = ob_get_clean();
        imagedestroy($dst);

        return $png;
    }
}

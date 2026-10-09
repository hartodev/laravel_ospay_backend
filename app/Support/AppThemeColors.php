<?php

namespace App\Support;

use App\Models\AppSetting;

/**
 * Warna aplikasi mobile yang diatur superadmin.
 *
 * Disimpan di tabel app_settings dengan key "app.theme" (tidak perlu migrasi
 * baru). Admin hanya memilih warna utama & warna kedua; warna turunan (gelap,
 * pucat, teks di atas warna) dihitung otomatis di sini.
 *
 * Bentuk datanya sama persis dengan yang dibaca Flutter dari GET /api/app/theme.
 */
class AppThemeColors
{
    public const KEY = 'app.theme';

    /** Sama dengan tampilan awal aplikasi sebelum fitur ini ada. */
    public static function defaults(): array
    {
        return [
            'primary' => '#2563EB',
            'primary_dark' => '#1D4ED8',
            'primary_light' => '#EFF4FF',
            'on_primary' => '#FFFFFF',
            'secondary' => '#0D9488',
            'on_secondary' => '#FFFFFF',
            'version' => 1,
        ];
    }

    /** Tema yang sedang aktif (default kalau belum pernah disimpan). */
    public static function current(): array
    {
        $theme = self::defaults();
        $saved = AppSetting::getValue(self::KEY);

        if (! is_array($saved)) {
            return $theme;
        }

        foreach (array_keys($theme) as $key) {
            if (! isset($saved[$key])) {
                continue;
            }

            if ($key === 'version') {
                $theme[$key] = max(1, (int) $saved[$key]);
            } elseif (self::isHex($saved[$key])) {
                $theme[$key] = strtoupper($saved[$key]);
            }
        }

        return $theme;
    }

    /** Susun tema lengkap dari dua warna pilihan admin. */
    public static function make(string $primary, string $secondary, int $version): array
    {
        $primary = strtoupper($primary);
        $secondary = strtoupper($secondary);

        return [
            'primary' => $primary,
            'primary_dark' => self::shiftLightness($primary, -0.07),
            'primary_light' => self::tint($primary, 0.92),
            'on_primary' => self::readableOn($primary),
            'secondary' => $secondary,
            'on_secondary' => self::readableOn($secondary),
            'version' => max(1, $version),
        ];
    }

    public static function isHex(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1;
    }

    /** Rasio kontras warna terhadap putih (1 - 21). Di bawah 3 = teks putih sulit dibaca. */
    public static function contrastWithWhite(string $hex): float
    {
        return 1.05 / (self::luminance($hex) + 0.05);
    }

    // ------------------------------------------------------------ helper warna

    /** Teks di atas warna ini: putih kalau cukup kontras, selain itu gelap. */
    private static function readableOn(string $hex): string
    {
        return self::contrastWithWhite($hex) >= 3.0 ? '#FFFFFF' : '#0F172A';
    }

    private static function luminance(string $hex): float
    {
        [$r, $g, $b] = array_map(function (int $v) {
            $c = $v / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, self::toRgb($hex));

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /** Campur dengan putih: $amount 0 = warna asli, 1 = putih. */
    private static function tint(string $hex, float $amount): string
    {
        [$r, $g, $b] = self::toRgb($hex);

        return self::fromRgb([
            $r + (255 - $r) * $amount,
            $g + (255 - $g) * $amount,
            $b + (255 - $b) * $amount,
        ]);
    }

    private static function shiftLightness(string $hex, float $delta): string
    {
        [$h, $s, $l] = self::toHsl(self::toRgb($hex));

        return self::fromRgb(self::fromHsl($h, $s, max(0.0, min(1.0, $l + $delta))));
    }

    /** @return array{0:int,1:int,2:int} */
    private static function toRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function fromRgb(array $rgb): string
    {
        return sprintf('#%02X%02X%02X', ...array_map(
            fn ($v) => max(0, min(255, (int) round($v))),
            $rgb
        ));
    }

    /** @return array{0:float,1:float,2:float} h 0-360, s 0-1, l 0-1 */
    private static function toHsl(array $rgb): array
    {
        [$r, $g, $b] = array_map(fn ($v) => $v / 255, $rgb);
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0.0) {
            return [0.0, 0.0, $l];
        }

        $s = $d / (1 - abs(2 * $l - 1));

        if ($max === $r) {
            $h = 60 * fmod((($g - $b) / $d), 6);
        } elseif ($max === $g) {
            $h = 60 * ((($b - $r) / $d) + 2);
        } else {
            $h = 60 * ((($r - $g) / $d) + 4);
        }

        return [$h < 0 ? $h + 360 : $h, $s, $l];
    }

    /** @return array{0:float,1:float,2:float} nilai 0-255 */
    private static function fromHsl(float $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255];
    }
}

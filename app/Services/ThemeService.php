<?php

namespace App\Services;

use App\Models\AppSetting;
use Throwable;

class ThemeService
{
    public const DEFAULT_PRIMARY   = '#1E5BFF';
    public const DEFAULT_SECONDARY = '#0B2A8A';

    /** Tema aktif dari database (fallback ke default kalau tabel belum ada). */
    public static function current(): array
    {
        try {
            return self::palette(
                AppSetting::get('theme.primary'),
                AppSetting::get('theme.secondary')
            ) + ['version' => (int) AppSetting::get('theme.version', 1)];
        } catch (Throwable $e) {
            return self::palette() + ['version' => 0];
        }
    }

    public static function palette(?string $primary = null, ?string $secondary = null): array
    {
        $p = self::valid($primary) ? strtoupper($primary) : self::DEFAULT_PRIMARY;
        $s = self::valid($secondary) ? strtoupper($secondary) : self::DEFAULT_SECONDARY;

        return [
            'primary'       => $p,
            'primary_dark'  => self::mix($p, '#000000', 0.15),
            'primary_light' => self::mix($p, '#FFFFFF', 0.90),
            'on_primary'    => self::onColor($p),
            'secondary'     => $s,
            'on_secondary'  => self::onColor($s),
        ];
    }

    public static function valid(?string $hex): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $hex);
    }

    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function mix(string $a, string $b, float $w): string
    {
        [$r1, $g1, $b1] = self::rgb($a);
        [$r2, $g2, $b2] = self::rgb($b);
        $m = fn ($x, $y) => (int) round($x * (1 - $w) + $y * $w);

        return sprintf('#%02X%02X%02X', $m($r1, $r2), $m($g1, $g2), $m($b1, $b2));
    }

    private static function onColor(string $hex): string
    {
        [$r, $g, $b] = self::rgb($hex);
        $luma = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luma > 0.6 ? '#111827' : '#FFFFFF';
    }
}

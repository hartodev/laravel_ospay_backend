<?php

namespace App\Support;

use App\Models\AppTheme;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Membuat CSS override untuk kelas Tailwind `brand-*` dan `ink-900`
 * dari tema yang diatur superadmin, supaya dashboard web ikut berubah warna.
 */
class ThemeCss
{
    /** Opacity yang didukung untuk kelas seperti bg-brand-500/30. */
    private const OPACITIES = [5, 10, 20, 30, 40, 50, 60, 70, 80, 90];

    public static function render(): string
    {
        try {
            $theme = AppTheme::current();
        } catch (Throwable $e) {
            // Tabel belum dimigrasi: pakai warna bawaan dari file CSS.
            return '';
        }

        try {
            return Cache::rememberForever(
                'theme.css.v1.' . $theme->id . '.' . $theme->version,
                fn () => self::build($theme)
            );
        } catch (Throwable $e) {
            return self::build($theme);
        }
    }

    private static function build(AppTheme $t): string
    {
        $primary = self::rgb($t->primary);
        $dark = self::rgb($t->primary_dark);
        $light = self::rgb($t->primary_light);
        $white = [255, 255, 255];
        $black = [0, 0, 0];

        $shades = [
            50 => $light,
            100 => self::mix($primary, $white, 0.88),
            200 => self::mix($primary, $white, 0.75),
            300 => self::mix($primary, $white, 0.55),
            400 => self::mix($primary, $white, 0.30),
            500 => $primary,
            600 => $dark,
            700 => self::mix($dark, $black, 0.20),
            800 => self::mix($dark, $black, 0.40),
            900 => self::mix($dark, $black, 0.60),
        ];
        $ink = self::mix($dark, $black, 0.55);

        // Variabel untuk Tailwind v4 (diabaikan oleh v3).
        $css = ':root{';
        foreach ($shades as $n => $c) {
            $css .= '--color-brand-' . $n . ':' . self::hex($c) . ';';
        }
        $css .= '--color-ink-900:' . self::hex($ink) . ';}';

        foreach ($shades as $n => $c) {
            $css .= self::utilities('brand-' . $n, $c, true);
        }
        $css .= self::utilities('ink-900', $ink, false);

        return $css;
    }

    /** Semua kelas utilitas untuk satu warna. */
    private static function utilities(string $name, array $rgb, bool $withOpacity): string
    {
        $hex = self::hex($rgb);

        $props = [
            'bg' => 'background-color:' . $hex,
            'text' => 'color:' . $hex,
            'border' => 'border-color:' . $hex,
            'ring' => '--tw-ring-color:' . $hex,
            'accent' => 'accent-color:' . $hex,
            'from' => '--tw-gradient-from:' . $hex . ';--tw-gradient-stops:var(--tw-gradient-from),var(--tw-gradient-to,rgba(255,255,255,0))',
            'via' => '--tw-gradient-stops:var(--tw-gradient-from),' . $hex . ',var(--tw-gradient-to,rgba(255,255,255,0))',
            'to' => '--tw-gradient-to:' . $hex,
            'shadow' => '--tw-shadow-color:' . $hex . ';--tw-shadow:var(--tw-shadow-colored)',
        ];

        $css = '';
        foreach ($props as $prefix => $decl) {
            $cls = $prefix . '-' . $name;
            $imp = self::important($decl);
            $css .= '.' . $cls . '{' . $imp . '}';
            $css .= '.hover\\:' . $cls . ':hover{' . $imp . '}';
            $css .= '.focus\\:' . $cls . ':focus{' . $imp . '}';
            $css .= '.group:hover .group-hover\\:' . $cls . '{' . $imp . '}';
        }

        if ($withOpacity) {
            $opacityProps = [
                'bg' => 'background-color',
                'text' => 'color',
                'border' => 'border-color',
                'ring' => '--tw-ring-color',
            ];
            foreach (self::OPACITIES as $o) {
                $rgba = 'rgba(' . $rgb[0] . ',' . $rgb[1] . ',' . $rgb[2] . ',' . ($o / 100) . ')';
                foreach ($opacityProps as $prefix => $cssProp) {
                    $css .= '.' . $prefix . '-' . $name . '\\/' . $o . '{' . $cssProp . ':' . $rgba . ' !important}';
                }
                $css .= '.shadow-' . $name . '\\/' . $o . '{--tw-shadow-color:' . $rgba . ' !important;--tw-shadow:var(--tw-shadow-colored) !important}';
            }
        }

        return $css;
    }

    private static function important(string $decl): string
    {
        return str_replace(';', ' !important;', $decl) . ' !important';
    }

    private static function rgb(string $hex): array
    {
        if (!preg_match('/^#?([0-9a-fA-F]{6})$/', $hex, $m)) {
            return [37, 99, 235];
        }

        return [
            hexdec(substr($m[1], 0, 2)),
            hexdec(substr($m[1], 2, 2)),
            hexdec(substr($m[1], 4, 2)),
        ];
    }

    private static function hex(array $c): string
    {
        return sprintf('#%02X%02X%02X', (int) round($c[0]), (int) round($c[1]), (int) round($c[2]));
    }

    private static function mix(array $a, array $b, float $t): array
    {
        return [
            (int) round($a[0] * (1 - $t) + $b[0] * $t),
            (int) round($a[1] * (1 - $t) + $b[1] * $t),
            (int) round($a[2] * (1 - $t) + $b[2] * $t),
        ];
    }
}

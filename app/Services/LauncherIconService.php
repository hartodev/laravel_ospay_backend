<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

/**
 * Membuat paket ikon launcher Android (mipmap) dari logo yang diunggah superadmin.
 *
 * Ikon di layar utama HP tertanam di dalam APK, jadi TIDAK bisa diganti dari
 * server setelah aplikasi terpasang. Yang bisa dilakukan server: menyiapkan
 * file ikon dengan ukuran yang benar, lalu developer menaruhnya di
 * android/app/src/<main|dev|prod>/res/ dan build ulang aplikasi.
 *
 * Isi ZIP (struktur folder sama dengan folder res Android):
 *   res/mipmap-mdpi/ic_launcher.png     48 x 48
 *   res/mipmap-hdpi/ic_launcher.png     72 x 72
 *   res/mipmap-xhdpi/ic_launcher.png    96 x 96
 *   res/mipmap-xxhdpi/ic_launcher.png   144 x 144
 *   res/mipmap-xxxhdpi/ic_launcher.png  192 x 192
 *   ic_launcher-playstore.png           512 x 512 (untuk Play Console)
 *   BACA-SAYA.txt
 *
 * Logo yang tidak persegi dimuat utuh di tengah kanvas persegi transparan
 * (tidak dipotong).
 */
class LauncherIconService
{
    /** @var array<string,int> folder res => ukuran piksel */
    public const DENSITIES = [
        'mipmap-mdpi' => 48,
        'mipmap-hdpi' => 72,
        'mipmap-xhdpi' => 96,
        'mipmap-xxhdpi' => 144,
        'mipmap-xxxhdpi' => 192,
    ];

    public const PLAYSTORE_SIZE = 512;

    public static function isSupported(): bool
    {
        return extension_loaded('gd') && extension_loaded('zip');
    }

    /**
     * @param  string  $imageBinary  isi file logo (PNG/JPG/WEBP)
     * @return string path file ZIP sementara; hapus setelah dikirim
     */
    public function buildZip(string $imageBinary): string
    {
        if (! self::isSupported()) {
            throw new RuntimeException('Ekstensi PHP gd dan zip harus aktif di server.');
        }

        $source = @imagecreatefromstring($imageBinary);

        if ($source === false) {
            throw new RuntimeException('File logo tidak bisa dibaca sebagai gambar.');
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'ospay_icons_');
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            imagedestroy($source);
            throw new RuntimeException('Gagal membuat file ZIP.');
        }

        try {
            foreach (self::DENSITIES as $folder => $size) {
                $zip->addFromString("res/{$folder}/ic_launcher.png", $this->renderSquare($source, $size));
            }

            $zip->addFromString('ic_launcher-playstore.png', $this->renderSquare($source, self::PLAYSTORE_SIZE));
            $zip->addFromString('BACA-SAYA.txt', $this->readme());
        } finally {
            imagedestroy($source);
            $zip->close();
        }

        return $zipPath;
    }

    /** Skala logo agar muat di kanvas persegi transparan $size x $size. */
    private function renderSquare(\GdImage $source, int $size): string
    {
        $w = imagesx($source);
        $h = imagesy($source);

        $canvas = imagecreatetruecolor($size, $size);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagealphablending($canvas, true);

        $scale = min($size / $w, $size / $h);
        $dw = max(1, (int) round($w * $scale));
        $dh = max(1, (int) round($h * $scale));
        $dx = (int) floor(($size - $dw) / 2);
        $dy = (int) floor(($size - $dh) / 2);

        imagecopyresampled($canvas, $source, $dx, $dy, 0, 0, $dw, $dh, $w, $h);

        ob_start();
        imagepng($canvas, null, 9);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        return $png;
    }

    private function readme(): string
    {
        return <<<TXT
Paket ikon launcher Android - OSPay
====================================

Cara memakai:
1. Ekstrak ZIP ini.
2. Salin isi folder "res" ke folder res Android project Flutter:
     android/app/src/main/res/
   Kalau ikon aplikasi diatur per flavor, salin ke folder res flavor itu
   (android/app/src/dev/res/ atau android/app/src/prod/res/). Flavor yang
   punya folder mipmap sendiri mengalahkan folder main.
3. Timpa file ic_launcher.png yang lama (mipmap-xhdpi, xxhdpi, xxxhdpi
   wajib; mdpi dan hdpi ikut disertakan supaya HP lama tidak menampilkan
   ikon yang berbeda).
4. Jalankan: flutter clean && flutter run   (atau build ulang APK/AAB).
5. ic_launcher-playstore.png (512 x 512) dipakai di Google Play Console.

Catatan:
- Ikon di layar utama HP tertanam di dalam APK. Mengubahnya selalu butuh
  build ulang dan pembaruan aplikasi di HP pengguna.
- Logo di dalam aplikasi (layar pembuka, login, beranda) diambil dari server
  dan berubah tanpa update aplikasi.
TXT;
    }
}

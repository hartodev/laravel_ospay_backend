<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\ContentPage;
use App\Models\Faq;
use Illuminate\Database\Seeder;

/**
 * Isi awal konten halaman Akun. Aman dijalankan ulang: data yang sudah ada
 * (dan mungkin sudah kamu edit) tidak ditimpa.
 *
 *     php artisan db:seed --class=AppContentSeeder
 */
class AppContentSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(<<<'JSON'
{
  "pages": [
    {
      "slug": "privacy-policy",
      "title": "Kebijakan Privasi",
      "intro": "Kami menghargai privasimu. Halaman ini menjelaskan data apa yang kami kumpulkan dan bagaimana data itu digunakan.",
      "sections": [
        {
          "title": "Informasi yang Kami Kumpulkan",
          "paragraphs": [
            "Data akun: nama, alamat email, dan nomor telepon yang kamu isi saat mendaftar atau mengubah profil.",
            "Data transaksi: produk yang dibeli, nomor tujuan atau nomor pelanggan, nominal, status, dan waktu transaksi.",
            "Data teknis dasar perangkat yang diperlukan untuk menjaga keamanan dan memperbaiki aplikasi."
          ]
        },
        {
          "title": "Cara Kami Menggunakan Informasi",
          "paragraphs": [
            "Memproses pembelian dan topup saldo, serta menampilkan riwayatnya.",
            "Memverifikasi akun dan mencegah penyalahgunaan atau penipuan.",
            "Memberi tahu status transaksi dan membantu kamu lewat Pusat Bantuan.",
            "Meningkatkan kualitas dan kenyamanan layanan."
          ]
        },
        {
          "title": "Berbagi Informasi",
          "paragraphs": [
            "Kami tidak menjual data pribadimu.",
            "Data hanya diteruskan seperlunya kepada penyedia produk dan mitra pembayaran agar transaksi dapat diproses, atau bila diwajibkan oleh hukum."
          ]
        },
        {
          "title": "Keamanan Data",
          "paragraphs": [
            "Kami menerapkan langkah keamanan yang wajar untuk melindungi datamu. Meski begitu, tidak ada sistem yang sepenuhnya bebas risiko.",
            "Jaga kerahasiaan password dan jangan bagikan kode verifikasi kepada siapa pun."
          ]
        },
        {
          "title": "Penyimpanan Data",
          "paragraphs": [
            "Data disimpan selama akunmu aktif dan selama diperlukan untuk menjalankan layanan serta memenuhi kewajiban hukum."
          ]
        },
        {
          "title": "Hak Pengguna",
          "paragraphs": [
            "Kamu dapat memperbarui data profil kapan saja lewat menu Edit Profil.",
            "Untuk pertanyaan atau permintaan terkait datamu, hubungi kami lewat Pusat Bantuan."
          ]
        },
        {
          "title": "Perubahan Kebijakan",
          "paragraphs": [
            "Kebijakan ini dapat diperbarui sewaktu-waktu. Dengan terus memakai aplikasi setelah perubahan, kamu dianggap menyetujui kebijakan yang baru."
          ]
        }
      ]
    },
    {
      "slug": "terms",
      "title": "Syarat & Ketentuan",
      "intro": "Dengan memakai aplikasi ini, kamu menyetujui syarat dan ketentuan berikut. Mohon dibaca dengan saksama.",
      "sections": [
        {
          "title": "Penerimaan Ketentuan",
          "paragraphs": [
            "Mendaftar dan menggunakan aplikasi berarti kamu menyetujui seluruh ketentuan di halaman ini."
          ]
        },
        {
          "title": "Akun dan Keamanan",
          "paragraphs": [
            "Data yang kamu berikan harus benar dan terbaru.",
            "Kamu bertanggung jawab menjaga kerahasiaan password serta seluruh aktivitas yang terjadi di akunmu."
          ]
        },
        {
          "title": "Jenis Pengguna",
          "paragraphs": [
            "Pengguna umum dapat langsung membeli produk yang tersedia.",
            "Pendaftaran sebagai Agen memerlukan persetujuan admin sebelum akun dapat dipakai sepenuhnya."
          ]
        },
        {
          "title": "Transaksi dan Pembayaran",
          "paragraphs": [
            "Pastikan nomor tujuan atau nomor pelanggan sudah benar sebelum membayar. Kesalahan input menjadi tanggung jawab pengguna.",
            "Transaksi yang sudah berhasil tidak dapat dibatalkan.",
            "Harga dan ketersediaan produk dapat berubah sewaktu-waktu."
          ]
        },
        {
          "title": "Transaksi Gagal atau Tertunda",
          "paragraphs": [
            "Status Diproses berarti transaksi masih dalam penanganan penyedia. Mohon tunggu hingga status berubah.",
            "Jika transaksi berstatus Gagal, dana dikembalikan sesuai ketentuan yang berlaku. Bila ada kendala, hubungi kami dengan menyertakan Ref ID transaksi."
          ]
        },
        {
          "title": "Saldo dan Topup (Agen)",
          "paragraphs": [
            "Saldo hanya dapat dipakai untuk bertransaksi di aplikasi ini. Topup diproses setelah pembayaran terkonfirmasi."
          ]
        },
        {
          "title": "Penggunaan yang Dilarang",
          "paragraphs": [
            "Dilarang menggunakan aplikasi untuk kegiatan melanggar hukum, penipuan, atau tindakan yang mengganggu sistem.",
            "Kami berhak menangguhkan akun yang melanggar ketentuan."
          ]
        },
        {
          "title": "Batasan Tanggung Jawab",
          "paragraphs": [
            "Kami tidak bertanggung jawab atas keterlambatan atau kegagalan yang disebabkan gangguan jaringan, penyedia produk, atau keadaan di luar kendali kami."
          ]
        },
        {
          "title": "Perubahan Ketentuan",
          "paragraphs": [
            "Ketentuan ini dapat diperbarui sewaktu-waktu. Penggunaan aplikasi setelah perubahan berarti kamu menyetujuinya."
          ]
        }
      ]
    }
  ],
  "faqs": [
    {
      "question": "Bagaimana cara membeli produk?",
      "answer": "Pilih layanan di Beranda atau tab Produk, pilih produk yang diinginkan, isi nomor tujuan atau nomor pelanggan, lalu lanjutkan ke pembayaran."
    },
    {
      "question": "Bagaimana cara topup saldo?",
      "answer": "Fitur saldo untuk akun Agen. Buka Beranda lalu pilih Topup, isi nominal, dan ikuti petunjuk pembayaran yang tampil. Saldo bertambah setelah pembayaran terkonfirmasi."
    },
    {
      "question": "Transaksi saya berstatus Diproses. Apa yang harus dilakukan?",
      "answer": "Status Diproses berarti transaksi masih ditangani penyedia produk. Tunggu beberapa saat, lalu cek lagi di tab Riwayat. Status akan berubah menjadi Berhasil atau Gagal."
    },
    {
      "question": "Transaksi gagal, bagaimana dengan dana saya?",
      "answer": "Dana dikembalikan sesuai ketentuan yang berlaku. Jika belum kembali, hubungi kami lewat WhatsApp atau email dengan menyertakan Ref ID dari halaman Detail Transaksi."
    },
    {
      "question": "Di mana saya bisa melihat riwayat transaksi?",
      "answer": "Buka tab Riwayat. Ketuk salah satu transaksi untuk melihat detail, termasuk Ref ID dan Serial Number yang bisa disalin."
    },
    {
      "question": "Bagaimana cara mengganti password?",
      "answer": "Buka tab Akun lalu pilih Ganti Password dan ikuti langkahnya."
    },
    {
      "question": "Bagaimana cara menjadi Agen?",
      "answer": "Pilih Daftar sebagai Agen di halaman masuk. Setelah mendaftar, akunmu menunggu persetujuan admin sebelum bisa dipakai penuh."
    }
  ],
  "app_info": {
    "description": "Aplikasi pembayaran digital untuk kebutuhan sehari-hari: isi pulsa, bayar listrik, beli voucher dan game, semuanya dalam satu tempat.",
    "latest_version": "1.0.0",
    "support": {
      "whatsapp": "0812-3456-7890",
      "email": "support@ospay.id",
      "hours": "Setiap hari, 08.00 - 21.00 WIB"
    },
    "highlights": [
      {
        "icon": "bolt",
        "title": "Cepat dan praktis",
        "description": "Beli pulsa, paket data, token listrik, dan lainnya dalam beberapa ketukan."
      },
      {
        "icon": "apps",
        "title": "Banyak pilihan layanan",
        "description": "Pulsa, data, PLN, game, voucher, streaming, hingga eSIM internasional."
      },
      {
        "icon": "receipt",
        "title": "Riwayat lengkap",
        "description": "Semua transaksi dan topup tercatat rapi dan mudah dicari."
      },
      {
        "icon": "wallet",
        "title": "Saldo untuk agen",
        "description": "Agen dapat topup saldo dan memantau mutasi dompet."
      }
    ]
  }
}
JSON, true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['pages'] as $page) {
            ContentPage::query()->firstOrCreate(['slug' => $page['slug']], $page);
        }

        if (Faq::query()->count() === 0) {
            foreach ($data['faqs'] as $i => $faq) {
                Faq::query()->create($faq + ['sort_order' => $i + 1]);
            }
        }

        if (AppSetting::query()->where('key', 'app.info')->doesntExist()) {
            AppSetting::setValue('app.info', $data['app_info']);
        }
    }
}

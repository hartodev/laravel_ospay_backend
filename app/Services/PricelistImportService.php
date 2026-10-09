<?php

namespace App\Services;

use App\Models\Products;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Import price list dari file xlsx IAK (satu sheet per tipe produk).
 *
 * Aturan sama dengan ProductSyncService (sync API):
 *
 * - Produk baru      : insert lengkap, price_user = price_agent = base_price
 *                      (margin 0, WAJIB di-review admin sebelum dijual).
 * - Produk yang ada  : HANYA base_price, status, raw_response yang diupdate.
 *                      price_user / price_agent / name / brand tidak disentuh.
 *
 * Sheet tanpa kolom "Product Code" + "Price" (mis. "Pasca Pricelist",
 * yang isinya fee & komisi, bukan harga) dilewati dan dilaporkan.
 */
class PricelistImportService
{
    /**
     * @return array{created:int, updated:int, sheets:int, skipped_sheets:array<int,string>}
     */
    public function import(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException('File pricelist tidak ditemukan.');
        }

        @set_time_limit(300);

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);

        $existing = Products::pluck('id', 'product_code')->all();

        $created = 0;
        $updated = 0;
        $sheets = 0;
        $skipped = [];

        DB::transaction(function () use ($spreadsheet, &$existing, &$created, &$updated, &$sheets, &$skipped) {
            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {
                $rows = $sheet->toArray(null, false, false, false);
                $header = array_map(fn ($h) => trim((string) $h), $rows[0] ?? []);

                $iCode = array_search('Product Code', $header, true);
                $iPrice = array_search('Price', $header, true);

                if ($iCode === false || $iPrice === false) {
                    $skipped[] = $sheet->getTitle();

                    continue;
                }

                $iOperator = array_search('Operator', $header, true);
                $iName = array_search('Product Name', $header, true);
                $iDesc = array_search('Description', $header, true);
                $iStatus = array_search('Status', $header, true);

                // "Pulsa Pricelist" -> "pulsa", "Prepaid international Pricelist" -> "prepaid-international"
                $type = str_replace(' ', '-', strtolower(trim(preg_replace('/\s*Pricelist$/i', '', $sheet->getTitle()))));

                $sheets++;
                $inserts = [];

                foreach (array_slice($rows, 1) as $row) {
                    $code = trim((string) ($row[$iCode] ?? ''));

                    if ($code === '') {
                        continue;
                    }

                    // "Rp 1.400" -> 1400
                    $price = (int) preg_replace('/\D/', '', (string) ($row[$iPrice] ?? '0'));
                    $status = strtolower(trim((string) ($row[$iStatus] ?? ''))) === 'active'
                        ? 'active'
                        : 'inactive';

                    $raw = json_encode(array_combine($header, array_pad(
                        array_slice($row, 0, count($header)),
                        count($header),
                        null
                    )));

                    if (isset($existing[$code])) {
                        DB::table('products')->where('id', $existing[$code])->update([
                            'base_price' => $price,
                            'status' => $status,
                            'raw_response' => $raw,
                            'updated_at' => now(),
                        ]);
                        $updated++;

                        continue;
                    }

                    $desc = trim((string) ($row[$iDesc] ?? ''));

                    $inserts[] = [
                        'category' => 'prepaid',
                        'product_code' => $code,
                        'brand' => $iOperator !== false ? trim((string) $row[$iOperator]) : null,
                        'type' => $type,
                        'name' => trim((string) ($row[$iName] ?? '')) ?: $code,
                        'description' => ($desc === '' || $desc === '-') ? null : $desc,
                        'base_price' => $price,
                        'price_user' => $price,
                        'price_agent' => $price,
                        'status' => $status,
                        'raw_response' => $raw,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $existing[$code] = true;
                    $created++;
                }

                foreach (array_chunk($inserts, 500) as $chunk) {
                    DB::table('products')->insert($chunk);
                }
            }
        });

        $spreadsheet->disconnectWorksheets();

        return [
            'created' => $created,
            'updated' => $updated,
            'sheets' => $sheets,
            'skipped_sheets' => $skipped,
        ];
    }
}

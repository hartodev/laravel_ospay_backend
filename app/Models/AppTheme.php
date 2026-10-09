<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $primary
 * @property string $primary_dark
 * @property string $primary_light
 * @property string $on_primary
 * @property string $secondary
 * @property string $on_secondary
 * @property int $version
 */
class AppTheme extends Model
{
    protected $fillable = [
        'primary',
        'primary_dark',
        'primary_light',
        'on_primary',
        'secondary',
        'on_secondary',
        'version',
    ];

    protected $casts = [
        'version' => 'integer',
    ];

    /** Kolom warna yang boleh diatur superadmin. */
    public const COLOR_FIELDS = [
        'primary',
        'primary_dark',
        'primary_light',
        'on_primary',
        'secondary',
        'on_secondary',
    ];

    /** Tema aktif (satu baris saja). Dibuat otomatis dengan warna bawaan. */
    public static function current(): self
    {
        return static::query()->first() ?? static::query()->create([]);
    }

    /** Bentuk JSON yang dibaca aplikasi Flutter. */
    public function toApi(): array
    {
        return [
            'primary' => $this->primary,
            'primary_dark' => $this->primary_dark,
            'primary_light' => $this->primary_light,
            'on_primary' => $this->on_primary,
            'secondary' => $this->secondary,
            'on_secondary' => $this->on_secondary,
            'version' => (int) $this->version,
        ];
    }
}

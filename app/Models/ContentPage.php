<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperContentPage
 */
class ContentPage extends Model
{
    protected $fillable = ['slug', 'title', 'intro', 'sections', 'is_active'];

    protected $casts = [
        'sections' => 'array',
        'is_active' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SliderSetting extends Model
{
    protected $fillable = [
        'slide_index',
        'title',
        'description',
        'image_path',
        'is_visible'
    ];

    protected $casts = [
        'slide_index' => 'integer'
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'category',
        'description',
        'description_en',
        'hastaliklar',
        'hastaliklar_en',
        'lat',
        'lng',
        'ai_context',
        'popular_score',
        'panorama_360',
        'image_gallery',
        'image_main',
        'district_id',
        'is_active'
    ];

    protected $casts = [
        'image_gallery' => 'array',
        'popular_score' => 'integer',
        'lat' => 'double',
        'lng' => 'double',
        'is_active' => 'boolean',
    ];
}

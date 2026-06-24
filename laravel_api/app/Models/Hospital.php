<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;

    protected $table = 'hospitals';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'name_en',
        'district_id',
        'description',
        'description_en',
        'address',
        'phone',
        'lat',
        'lng',
        'image_main',
        'panorama_360',
        'is_active',
        'created_at'
    ];
}

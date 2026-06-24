<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $table = 'districts';
    public $timestamps = false; // Original table doesn't have timestamps

    protected $fillable = [
        'name',
        'slug',
        'image',
        'lat',
        'lng',
        'is_active',
        'created_at'
    ];
}

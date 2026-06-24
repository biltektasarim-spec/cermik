<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'product_name',
        'description',
        'price',
        'image',
        'is_active',
        'sort_order',
        'created_at'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}

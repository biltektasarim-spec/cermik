<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $table = 'businesses';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'category',
        'district_id',
        'address',
        'phone',
        'email',
        'website',
        'description',
        'image',
        'lat',
        'lng',
        'working_hours',
        'has_order',
        'is_active',
        'is_featured',
        'created_at'
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}

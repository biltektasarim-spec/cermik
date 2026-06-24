<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $table = 'services';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'title_en',
        'description',
        'description_en',
        'image',
        'district_id',
        'status',
        'progress',
        'created_at'
    ];

    protected $casts = [
        'status' => 'integer',
        'progress' => 'integer',
        'district_id' => 'integer',
    ];
}

/* --- MunicipalGuide Model --- */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class MunicipalGuide extends Model {
    protected $table = 'municipal_guide';
    public $timestamps = false;
    protected $fillable = ['name', 'phone', 'category', 'district_id', 'is_active'];
}

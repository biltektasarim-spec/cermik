<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    use HasFactory;

    protected $table = 'pharmacies';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'district_id',
        'address',
        'phone',
        'lat',
        'lng',
        'is_duty',
        'created_at'
    ];
}

/* --- Announcement Model --- */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Announcement extends Model {
    protected $table = 'announcements';
    public $timestamps = false;
    protected $fillable = ['title', 'content', 'image', 'district_id', 'is_active', 'created_at'];
}

/* --- Event Model --- */
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Event extends Model {
    protected $table = 'events';
    public $timestamps = false;
    protected $fillable = ['title', 'description', 'image', 'event_date', 'district_id', 'is_active', 'created_at'];
}

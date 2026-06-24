<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'image',
        'event_date',
        'district_id',
        'is_active',
        'created_at'
    ];
}

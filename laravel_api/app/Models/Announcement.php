<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'content',
        'image',
        'district_id',
        'is_active',
        'created_at'
    ];
}

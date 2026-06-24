<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CekGonder extends Model
{
    use HasFactory;

    protected $table = 'cek_gonder_forms';
    public $timestamps = false;

    protected $fillable = [
        'district_id',
        'user_id',
        'fcm_token',
        'basvuru_turu',
        'ad_soyad',
        'tc_no',
        'email',
        'tel_no',
        'aciklama',
        'foto1',
        'foto2',
        'foto3',
        'process_status',
        'created_at'
    ];

    // process_status eklendiyse veritabanı seviyesinde default olmalı
    // veya Controller seviyesinde eklenmeli ki migration yapılmamışsa hata vermesin.
}

<?php

namespace App\Models\CoreKur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatKurCore extends Model
{
    use HasFactory;
    protected $connection = 'core_kur';
    protected $table = 'sertifikat_kur';
    protected $fillable = ['*'];
    public $timestamps = false;
}

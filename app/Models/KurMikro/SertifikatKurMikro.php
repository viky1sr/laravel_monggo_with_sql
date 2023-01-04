<?php

namespace App\Models\KurMikro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SertifikatKurMikro extends Model
{
    use HasFactory;
    protected $table = 'sertifikat_kur';
    protected $fillable = ['*'];
    public $timestamps = false;
}

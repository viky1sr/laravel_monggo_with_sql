<?php

namespace App\Models\KurMikro;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sp2KurMikro extends Model
{
    use HasFactory;
    protected $table = 'sp2_kur';
    protected $fillable = [
        'cif','no_pk','tanggal_pk','tanggal_rekam','tanggal_awal','tanggal_akhir','nomor_aplikasi','no_rekening','id_dd_bank','created_by',
        'status_data','date_created','id_calon_debitur_kur'
    ];
    public $timestamps = false;
}

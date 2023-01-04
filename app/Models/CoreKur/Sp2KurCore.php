<?php

namespace App\Models\CoreKur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sp2KurCore extends Model
{
    use HasFactory;
    protected $connection = 'core_kur';
    protected $table = 'sp2_kur';
    protected $fillable = [
        'cif','no_pk','tanggal_pk','tanggal_rekam','tanggal_awal','tanggal_akhir','nomor_aplikasi','no_rekening','id_dd_bank','created_by',
        'status_data','date_created'
    ];
    public $timestamps = false;
}

<?php

namespace App\Models\KurMikro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DdBankCabang extends Model
{
    use HasFactory;
    protected $table = "dd_bank_cabang";

    public function is_kowil()
    {
        return $this->hasOne(DcWilayahKerja::class,'id_dc_wilayah_kerja','id_dc_wilayah_kerja')
            ->select('id_dc_wilayah_kerja','ko_wil');
    }
}

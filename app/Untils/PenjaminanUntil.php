<?php

namespace App\Untils;

use App\Models\KurMikro\CalonDebiturKurMikro;
use App\Models\KurMikro\DcSektorLbu;
use App\Models\KurMikro\DdBankCabang;
use Illuminate\Support\Facades\DB;

class PenjaminanUntil {

    public function checkCalonDebiturKur(Array $data){
        return CalonDebiturKurMikro::where(['nomor_aplikasi' => $data['nomor_aplikasi'], 'id_dd_bank' => $data['id_dd_bank']])->count();
    }

    public function checkNoRekening(Array $data){
        return DB::table('sp2_kur as a')
            ->select('b.no_sertifikat','a.no_rekening','a.nomor_aplikasi')
            ->join('sertifikat_kur as b','b.no_rekening','=','a.no_rekening','inner')
            ->where(['a.id_dd_bank' => $data['id_dd_bank'], 'a.no_rekening' => $data['no_rekening']])
            ->orWhere(['a.id_dd_bank' => $data['id_dd_bank'], 'a.nomor_aplikasi' => $data['nomor_aplikasi']])
            ->first();
    }

    public function checkKodeUker(Array $data){
        return DdBankCabang::where(['kode_uker' => $data['kode_uker'], 'id_dd_bank' => $data['id_dd_bank']])->count();
    }

    public function checkKodeSektor(Array $data) {
        return DcSektorLbu::where('lbu_kode','=',$data['kode_sektor'])->count();
    }
}

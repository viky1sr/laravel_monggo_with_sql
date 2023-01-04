<?php

namespace App\Validations;

use App\Traits\ResponseTrait;
use App\Untils\PenjaminanUntil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class PenjaminanValidation
{
    use ResponseTrait;

    protected $jenisKur;
    protected $jenisKredit;
    protected $limitPlafonArr;
    protected $penjaminanUntil;

    public function __construct(PenjaminanUntil $penjaminanUntil){
        $this->jenisKur = [
            'mikro' => 1,
            'kecil' => 2,
            'tki' => 4,
            'khusus' => 5,
            'super_mikro' => 6,
        ];

        $this->jenisKredit = [
            'KMK' => 1,
            'KI' => 2,
        ];
        $this->limitPlafonArr = [
            '10_juta' => 10000000,
            '50_juta' => 50000000,
            '25_juta' => 25000000,
            '100_juta' => 100000000,
            '500_juta' => 500000000,
        ];

        $this->penjaminanUntil = $penjaminanUntil;
    }

    public function validation(Array $request){
        $validated  = Validator::make($request,[
            'jenis_kelamin' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2,9])) {
                        $fail('The '.$attribute.' is invalid. [1=Laki-laki, 2=Perempuan, 9=Badan Usaha]');
                    }
                },
            ],
            'jenis_kur' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2,4,5,6])) {
                        $fail('The '.$attribute.' is invalid. [1=Mikro; 2=Kecil; 4=TKI; 5=KUR Khusus; 6=Super Mikro]');
                    }
                },
            ],
            'jenis_identitas' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2,9])) {
                        $fail('The '.$attribute.' is invalid. [1=KTP, 2=SIM , 9=Lainnya ]');
                    }
                },
            ],
            'jenis_linkage' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use($request) {
                    if(!in_array($value,["N", "C", "E"])) {
                        $fail('The '.$attribute.' is invalid. [ E = Executing; C = Channeling; N = Non linkage ]');
                    }
                    if($value === "E" && $request['lembaga_linkage'] === null) {
                        $fail('Lembaga Linkage is required if '.$attribute.' Type is '.$value);
                    }
                },
            ],
            'no_identitas' => [
                'required',
                function ($attribute, $value, $fail) use($request) {
                    if(strlen($value) != 16 && (isset($request['jenis_identitas']) ? $request['jenis_identitas'] : null) == 1 ) {
                        $fail('The no identitas must have at least 16 digits.');
                    }
                },
            ],
            'status_kolektibilitas' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[0,1,2,3,4,5])) {
                        $fail('The '.$attribute.' is invalid. [ 0=Baru, 1=Lancar, 2=Dalam Perhatian Khusus, 3=Kurang Lancar, 4=Diragukan, 5=Macet ]');
                    }
                },
            ],
            'jenis_kredit' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2])) {
                        $fail('The '.$attribute.' is invalid. [ 1=KMK; 2=KI; ]');
                    }
                },
            ],
            'marital_status' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[0,1,2,9])) {
                        $fail('The '.$attribute.' is invalid. [ 0: Not Married, 1: Married, 2: Widow/Widower, 9: Business Entity ]');
                    }
                },
            ],
            'pendidikan' => [
                'required',
                'max_digits:1',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2,3,4,5,6,9])) {
                        $fail('The '.$attribute.' is invalid. [ 1: SD, 2: SMP, 3: SMA, 4: Diploma, 5: Bachelor, 6: Others, 9: Business Entity ]');
                    }
                },
            ],
            'jangka_waktu' => [
                'required',
                'max_digits:3',
                function ($attribute, $value, $fail) use($request) {
                    $jenisKur = isset($request['jenis_kur']) ? $request['jenis_kur'] : 0;
                    $jenisKredit = isset($request['jenis_kredit']) ? $request['jenis_kredit'] : 0;
                    $typeKur = array_search($jenisKur,$this->jenisKur);
                    $typeCredit = array_search($jenisKredit,$this->jenisKredit);
                    switch ($value){
                        case(in_array($jenisKredit,[1,2]) && $jenisKur == 4 && $value > 36):
                        case(in_array($jenisKur,[2,5,6]) && $jenisKredit == 2 && $value > 60):
                        case(in_array($jenisKur,[2,5]) && $jenisKredit == 1 && $value > 48):
                        case(in_array($jenisKur,[1,6]) && $jenisKredit == 1 && $value > 36):
                            return $fail('Jangka Waktu '.$value.' not in accordance with the type of credit '.$typeCredit.' and Jenis Kur '.$typeKur);
                        default :
                            return false;
                    }

                },
            ],
            'tanggal_awal' => 'required|date_format:Y-m-d',
            'tanggal_akhir' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) use($request) {
                    $dateFirst = Carbon::parse((isset($request['tanggal_awal']) ? $request['tanggal_awal'] : null));
                    $diff = $dateFirst->diffInMonths($value);
                    if($diff != (isset($request['jangka_waktu']) ? $request['jangka_waktu'] : 0) ){
                        $fail('Error, The jangka_waktu does not match the total month of the tanggal_awal - tanggal_akhir');
                    }
                },
            ],
            'tanggal_pk' => [
                'required',
                'date_format:Y-m-d',
                function ($attribute, $value, $fail) {
                    if($value < Carbon::now()->addYear(-1) ){
                        $fail('The tanggal pk must be more than date '. Carbon::now()->addYear(-1)->format('Y-m-d'));
                    }
                },
            ],
            'kode_pekerjaan' => [
                'required',
                'max_digits:2',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,[1,2,3,4,5,6,7,8,9,99])){
                         $fail('The '.$attribute.' is invalid. [ 1=PNS, 2=TNI/POLRI, 3=PENSIUNAN/PURNAWIRAWAN 4=PROFESIONAL, 5=KARYAWAN SWASTA, 6=WIRASWASTA, 7=PETANI, 8=PEDAGANG 9=NELAYAN, 99=LAIN-LAIN/Badan Usaha ]');
                    }
                },
            ],
            'status_lunas' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if(!in_array($value,["L", "B", "T"])){
                        $fail('The '.$attribute.' is invalid. [ L=Lunas, B=Belum, T=Tidak Memiliki Kredit Program selain KUR ]');
                    }
                },
            ],
            'npwp' => 'required|min_digits:15|max_digits:15',
            'alamat_debitur' => 'required',
            'coverage' => 'required',
            'cif' => 'required',
            'jml_t_kerja' => 'required',
            'kode_bank' => 'required',
            'kode_pos' => 'required',
            'kode_sektor' => 'required',
            'kode_uker' => 'required',
            'nama_debitur' => 'required',
            'no_ijin_usaha' => 'required',
            'no_pk' => 'required',
            'no_rekening' => 'required',
            'nomor_aplikasi' => 'required',
            'omset_usaha' => 'required',
            'plafon_kredit' => 'required',
            'suku_bunga' => 'required',
            'tanggal_lahir' => 'required|date_format:Y-m-d',
            'tanggal_rekam' => 'required|date_format:Y-m-d',
            'cabang_rekanan' => 'required',
        ]);

        if($validated->fails()) {
            return $this->failure($validated->errors()->first(),422);
        }

        if($errCheckingData = $this->checkingData($request)){
            return $this->failure($errCheckingData,400);
        }

        if($errJangkaWaktu = $this->jangkaWaktu($request)){
            return $this->failure($errJangkaWaktu,400);
        }

        if($errPlafonKRedit = $this->plafonKredit($request)){
            return $this->failure($errPlafonKRedit,400);
        }

        return false;
    }

    protected function checkingData(Array $data){
        if($this->penjaminanUntil->checkCalonDebiturKur($data)){
            return "Calon Debitur Kur dengan nomor aplikasi : ".$data['nomor_aplikasi'].' sudah pernah melakukan Penjaminan';
        } else if($dataSer = $this->penjaminanUntil->checkNoRekening($data)) {
            return "Calon Debitur Kur dengan nomor rekening : ".$data['no_rekening'].' atau nomor aplikasi : '.$data['nomor_aplikasi'].
                ' sudah pernah melakukan Penjaminan dengan nomor sertifikat : '.$dataSer->no_sertifikat;
        } else if($this->penjaminanUntil->checkKodeUker($data) <= 0){
            return "Maaf, Kode Unit Kerja : ".$data['kode_uker']." belum dikenal Sistem. Mohon untuk menghubungi Pihak Jamkrindo untuk melakukan pendaftaran unit kerja";
        } else if($this->penjaminanUntil->checkKodeSektor($data) <= 0){
            return  "Maaf, Kode Sektor : ".$data['kode_sektor']." tidak di kenal";
        } else {
            return false;
        }
    }

    protected function jangkaWaktu(Array $data){
        $jangkaWaktu = [
            ($data['jangka_waktu'] > 36 ? "ok_mikro_1" : "fail_mikro_1") => 'mikro_1',
            ($data['jangka_waktu'] > 36 ? "ok_super_mikro_1" : "fail_super_mikro_1") => 'super_mikro_1',
            ($data['jangka_waktu'] > 36 ? "ok_tki_1" : "fail_tki_1") => 'tki_1',
            ($data['jangka_waktu'] > 36 ? "ok_tki_2" : "fail_tki_2") => 'tki_2',
            ($data['jangka_waktu'] > 48 ? "ok_kecil_1" : "fail_kecil_1") => 'kecil_1',
            ($data['jangka_waktu'] > 48 ? "ok_khusus_1" : "fail_khusus_1") => 'khusus_1',
            ($data['jangka_waktu'] > 60 ? "ok_mikro_2" : "fail_mikro_2") => 'mikro_2',
            ($data['jangka_waktu'] > 60 ? "ok_super_mikro_2" : "fail_super_mikro_2") => 'super_mikro_2',
            ($data['jangka_waktu'] > 60 ? "ok_kecil_2" : "fail_kecil_2") => 'kecil_2',
            ($data['jangka_waktu'] > 60 ? "ok_khusus_2" : "fail_khusus_2") => 'khusus_2',
        ];
        $value = array_search($data['jenis_kur'],$this->jenisKur)."_".$data['jenis_kredit'];
        $result = array_search($value,$jangkaWaktu);
        if(str_replace("_".$value,"",$result) === "ok"){
            return "Error, Credit Term " . ($data['jangka_waktu']) . " not in accordance with the type of credit " .
                ($data['jenis_kur'] == 1 ? "KMK" : "KI")." and Jenis KUR ".array_search($data['jenis_kur'],$this->jenisKur);
        }
        return false;
    }

    protected function plafonKredit(Array $data){
        $jenis_kur = $data['jenis_kur'];
        $plafon_kredit = $data['plafon_kredit'];
        $tanggal_pk = $data['tanggal_pk'];
        $limitPlafonArr = $this->limitPlafonArr;
        $jenisKurArr = $this->jenisKur;

        if ($tanggal_pk < '2020-01-02')
        {
            $message = [
                'Type of Kur Kecil limit only IDR 25,000,001 up to IDR 500,000,000' => 2,
                'Type of Kur Kecil limit only IDR 25,000,001 up to IDR 500,000,000' => 6,
                'Type of Kur Mikro limit only up to IDR 25,000,000' => 1,
                'Type of Kur Mikro limit only up to IDR 25,000,000' => 4
            ];
            switch ($jenis_kur) {
                case (($jenis_kur == 2 || $jenis_kur == 5) && ($plafon_kredit > $limitPlafonArr['500_juta'] ||
                        $plafon_kredit <= $limitPlafonArr['25_juta']) ):
                case (($jenis_kur == 1 || $jenis_kur == 4) && ($plafon_kredit > $limitPlafonArr['25_juta'])) :
                    return array_search($jenis_kur,$message);
                default:
                    return false;
            }
        } else {
            $message = [
                'Type of Kur Mikro limit only IDR 10,000,001 up to IDR 100,000,000' => 1,
                'Type of Kur Kecil limit only IDR 100,000,001 up to IDR 500,000,000' => 2,
                'Type of Kur Khusus limit only up to IDR 500,000,000' => 5,
                'Type of Kur Pekerja Migran Indoneisa limit only up to IDR 100,000,000' => 4,
                'Type of Kur Super Mikro limit only up to IDR 10,000,000' => 6
            ];
            switch($jenis_kur){
                case ( $jenis_kur == $jenisKurArr['mikro'] &&  (
                        $plafon_kredit > $limitPlafonArr['100_juta'] ||
                        $plafon_kredit <= $limitPlafonArr['10_juta']
                    )
                ) :
                case ( ($jenis_kur == $jenisKurArr['kecil'] || $jenis_kur == $jenisKurArr['khusus']) &&  (
                        $plafon_kredit > $limitPlafonArr['500_juta'] ||
                        ($jenis_kur == $jenisKurArr['kecil'] ? $plafon_kredit <= $limitPlafonArr['100_juta'] : false )
                    )
                ) :
                case ( $jenis_kur == $jenisKurArr['tki'] &&  (
                        $plafon_kredit > $limitPlafonArr['100_juta']
                    )
                ) :
                case ( $jenis_kur == $jenisKurArr['super_mikro'] &&  (
                        $plafon_kredit > $limitPlafonArr['10_juta']
                    )
                ) :
                    return array_search($jenis_kur,$message);
                default:
                    return false;
            }
        }
    }

}

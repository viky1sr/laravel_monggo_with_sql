<?php

namespace App\Validations;

use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use PhpParser\Node\Expr\Array_;

class PenjaminanValidation
{
    use ResponseTrait;

    protected $jenisKur;
    protected $jenisKredit;

    public function __construct(){
        $this->jenisKur = [
          'Mikro' => 1,
          'Kecil' => 2,
          'TKI' => 4,
          'Khusus' => 5,
          'Super Mikro' => 6
        ];

        $this->jenisKredit = [
            'KMK' => 1,
            'KI' => 2,
        ];
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
                    if(strlen($value) != 16 && $request['jenis_identitas'] == 1 ) {
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
                'max_digits:2',
                function ($attribute, $value, $fail) use($request) {
                    $typeKur = array_search($request['jenis_kur'],$this->jenisKur);
                    $typeCredit = array_search($request['jenis_kredit'],$this->jenisKredit);
                    switch ($value){
                        case(in_array($request['jenis_kredit'],[1,2]) && $request['jenis_kur'] == 4 && $value > 36):
                        case(in_array($request['jenis_kur'],[2,5,6]) && $request['jenis_kredit'] == 2 && $value > 60):
                        case(in_array($request['jenis_kur'],[2,5]) && $request['jenis_kredit'] == 1 && $value > 48):
                        case(in_array($request['jenis_kur'],[1,6]) && $request['jenis_kredit'] == 1 && $value > 36):
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
                    $dateFirst = Carbon::parse($request['tanggal_awal']);
                    $diff = $dateFirst->diffInMonths($value);
                    if($diff != $request['jangka_waktu']){
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

        if($errPlafonKRedit = $this->plafonKredit($request)){
            return $this->failure($errPlafonKRedit,400);
        }

        return false;
    }

    protected function checkingData(Array $data){

    }

    protected function plafonKredit(Array $data){

    }

}

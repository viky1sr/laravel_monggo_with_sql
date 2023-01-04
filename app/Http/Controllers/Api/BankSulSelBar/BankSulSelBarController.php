<?php

namespace App\Http\Controllers\api\BankSulSelBar;

use App\Http\Controllers\Controller;
use App\Models\KurMikro\DdBankCabang;
use App\Services\PenjaminanBaru\PenjaminanBaruService;
use App\Traits\ResponseTrait;
use App\Validations\PenjaminanValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class BankSulSelBarController extends Controller
{
    use ResponseTrait;

    protected $valPenjaminan;
    protected $servicePenjaminanBaru;

    public function __construct(
        PenjaminanValidation $valPenjaminan,
        PenjaminanBaruService $servicePenjaminanBaru
    )
    {
        $this->middleware('is_jwt');
        $this->valPenjaminan = $valPenjaminan;
        $this->servicePenjaminanBaru = $servicePenjaminanBaru;
    }

    public function storePenjaminan(Request $request){
        $request->merge(['id_dd_bank' => Auth::user()->id_dd_bank]);
        $data = $request->all();

        DB::beginTransaction();
        DB::connection('core_kur')->beginTransaction();
        try {
            if($err = $this->valPenjaminan->validation($data)){
                return $err;
            }
            $insert = $this->servicePenjaminanBaru->createPenjaminanBaru($data);
            DB::commit();
            DB::connection('core_kur')->commit();
            return $this->success("Success create Penjaminan Baru",$insert,201);
        }catch (\Exception $e){
            DB::rollBack();
            DB::connection('core_kur')->rollBack();
            return $this->failure($e->getMessage(),500);
        }
    }

}

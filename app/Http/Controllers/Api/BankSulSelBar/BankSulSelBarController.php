<?php

namespace App\Http\Controllers\api\BankSulSelBar;

use App\Http\Controllers\Controller;
use App\Validations\PenjaminanValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BankSulSelBarController extends Controller
{
    protected $valPenjaminan;

    public function __construct(
        PenjaminanValidation $valPenjaminan
    )
    {
        $this->valPenjaminan = $valPenjaminan;
//        $this->middleware('is_jwt');
    }

    public function storePenjaminan(Request $request){
        $data = $request->all();
        $getVal = Storage::get('json/plafonkredit.json');
        return $this->valPenjaminan->validation($data);
        if($this->valPenjaminan->validation($data)){

        }
    }

}

<?php

namespace App\Http\Controllers\Api\BankSulSelBar;

use App\Http\Controllers\Controller;
use App\Validations\PenjaminanValidation;
use Illuminate\Http\Request;

class PenjaminanController extends Controller
{
    protected $valPenjaminan;

    public function __construct(
        PenjaminanValidation $valPenjaminan
    )
    {
        $this->valPenjaminan = $valPenjaminan;
        $this->middleware('is_jwt');
    }

    public function store(Request $request){
        $data = $request->all();
        if($this->valPenjaminan->validation($data)){

        }
    }



}

<?php

namespace App\Validations;

use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;

class PenjaminanSprValidation
{
    use ResponseTrait;

    public function validation(Array $request){
        $validated  = Validator::make($request,[
            'username' => 'required',
            'password' => 'required'
        ]);

        if($validated->fails()) {
            return $this->failure($validated->errors()->first(),422);
        }

        if($errJw = $this->jangkaWaktu($request)){
            return $this->failure($errJw,400);
        }

        if($errPlafonNonCovid = $this->plafonKreditNonCovid($request)){
            return $this->failure($errPlafonNonCovid,400);
        }

        if($errPlafonCovid = $this->plafonKreditCovid($request)){
            return $this->failure($errPlafonCovid,400);
        }

        return false;
    }

    protected function jangkaWaktu(Array $data){

    }

    protected function plafonKreditNonCovid(Array $data){

    }

    protected function plafonKreditCovid(Array $data){

    }
}

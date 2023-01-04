<?php

namespace App\Validations;

use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\Validator;

class AuthValidation
{
    use ResponseTrait;

    public function loginValidtion(Array $request){
        $validated  = Validator::make($request,[
          'username' => 'required',
          'password' => 'required'
        ]);

        if($validated->fails()) {
            return $this->failure($validated->errors()->first(),422);
        }

        return false;
    }
}

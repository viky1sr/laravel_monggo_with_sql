<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\KurMikro\DdUser;
use App\Models\User;
use App\Services\AuthService;
use App\Validations\AuthValidation;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController extends Controller
{
    protected $authService;
    protected $authVal;

    public function __construct(
        AuthService $authService,
        AuthValidation $authVal
    ){
        $this->authService = $authService;
        $this->authVal = $authVal;
    }

    public function login(Request $request)
    {
        if($error = $this->authVal->loginValidtion($request->all())){
            return $error;
        }
        return $this->authService->login($request->only('username','password'));
    }
}

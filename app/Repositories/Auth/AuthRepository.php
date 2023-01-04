<?php

namespace App\Repositories\Auth;

use App\Exceptions\AuthErrorException;
use App\Traits\ResponseTrait;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthRepository implements AuthInterface
{
    use ResponseTrait;

    protected $carbon;

    public function __construct(Carbon $carbon){
        $this->carbon = $carbon;
    }

    public function login(array $params)
    {
        try {
            if (!$access_token = JWTAuth::attempt($params)) {
                report('Error, Password dan email tidak sama.');
                throw new AuthErrorException('Error, Password dan email tidak sama.');
            } else {
                $result = [
                    'access_token' => $access_token,
                    'exp_token' => date('Y-m-d h:i:s',JWTAuth::setToken($access_token)->getPayload()->get('exp')),
                    'result' => [
                        'UserName' => Auth::user()->username,
                        'LoginDatetime' => $this->carbon->now()->format('Y-m-d h:i:s')
                    ]
                ];
                return $this->success('Success login.',$result,200);
            }
        }catch (JWTException $e){
            report($e->getMessage());
            throw new AuthErrorException('Error, could_not_create_token, '.$e->getMessage());
        }
    }

    public function logout(array $params)
    {
        // TODO: Implement logout() method.
    }

    public function register(array $params)
    {
        // TODO: Implement logout() method.
    }
}

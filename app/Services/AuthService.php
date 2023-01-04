<?php

namespace App\Services;

use App\Repositories\Auth\AuthInterface;
use App\Repositories\Auth\AuthRepository;

class AuthService implements AuthInterface
{
    protected $repoAuth;

    public function  __construct(AuthRepository $repoAuth){
        $this->repoAuth = $repoAuth;
    }

    public function login(array $params)
    {
        $credential = [
            'username' => $params['username'],
            'password' => md5($params['password'])
        ];
        return $this->repoAuth->login($credential);
    }

    public function logout(array $params)
    {
        // TODO: Implement logout() method.
    }

    public function register(array $params)
    {
        // TODO: Implement register() method.
    }
}

<?php

namespace App\Repositories\Auth;

interface AuthInterface
{
    public function login(Array $params);
    public function logout(Array $params);
    public function register(Array $params);
}

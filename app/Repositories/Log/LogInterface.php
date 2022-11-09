<?php

namespace App\Repositories\Log;

interface LogInterface
{
    public function dataTables(Array $params);
    public function store(Array $params);
}

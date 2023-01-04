<?php

namespace App\Services\PenjaminanBaru;

interface PenjaminanBaruServiceInterface
{
    public function createPenjaminanBaru(Array $data);
    public function generateSertifikat(Array $data);
}

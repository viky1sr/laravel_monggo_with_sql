<?php

namespace App\Repositories\PenjaminanBaru;

use App\Models\KurMikro\CalonDebiturKurMikro;

interface PenjaminanBaruInterface
{
    public function storeCalonDebiturKur(Array $request) : array;
    public function storeSp2Kur(Array $request) : array;
    public function storeSertifikat(Array $request) : array;
    public function generateSertifikat(Array $data);
    public function getKowil(Array $data) : array;
}

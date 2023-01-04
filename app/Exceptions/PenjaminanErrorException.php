<?php

namespace App\Exceptions;

use App\Traits\ResponseTrait;

class PenjaminanErrorException extends \Exception
{
    use ResponseTrait;

    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        \Log::debug('Penjaminan Baru Error');
    }

    public function render($request){
        return $this->failure($this->getMessage(),500);
    }
}

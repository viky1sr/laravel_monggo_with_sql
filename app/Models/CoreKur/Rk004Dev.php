<?php

namespace App\Models\CoreKur;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rk004Dev extends Model
{
    use HasFactory;
    protected $connection = 'core_kur';
    protected $table = 'rk004_dev';
}

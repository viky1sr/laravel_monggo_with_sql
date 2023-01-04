<?php

namespace App\Models\KurMikro;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdUser extends Model
{
    use HasFactory;

    protected $table = 'dd_user';

    public function is_user(){
        return $this->hasOne(User::class,'id_dd_user','id_dd_user');
    }

}

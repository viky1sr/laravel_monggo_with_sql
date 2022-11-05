<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $dates = ['deleted_at'];
    protected $fillable = ['bank','response','request'];
    protected $casts = [
        'response' => 'array',
        'request' => 'array',
    ];

}

<?php

namespace App\Repositories\Log;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class LogRepository implements LogInterface
{
    protected $model;
    protected $redis;
    protected $carbon;

    public function __construct(
        Log $model,
        Redis $redis,
        Carbon $carbon
    ){
        $this->model = $model;
        $this->redis = $redis;
        $this->carbon = $carbon;
    }

    public function dataTables(array $params)
    {
        $data = $this->redis->get('logs');
        return collect(json_decode($data))->map(function ($item) use($params){
            if(empty($params)){
                return searchDataLog($item,['date' => Carbon::now()->format('Y-m-d')]);
            } else {
                return searchDataLog($item,$params);
            }
        })->reject(function ($item) {
            return empty($item);
        });
    }

    public function store(array $params)
    {
        // TODO: Implement store() method.
    }
}

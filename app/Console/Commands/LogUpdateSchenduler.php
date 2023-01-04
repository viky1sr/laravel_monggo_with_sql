<?php

namespace App\Console\Commands;

use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class LogUpdateSchenduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Log to REDIS when every day at midnight';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $get = Redis::get('logs');
        if(count(json_decode($get)) != Log::count()){
            Redis::del('logs');
            Redis::set('logs',Log::all());
        }
        return $this->info('Success updated log '.Carbon::now());
    }
}

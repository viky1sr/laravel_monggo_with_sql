<?php

namespace App\Console\Commands;

use App\Models\KurMikro\DdUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateUserSchenduller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user every 1 hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Schenduller update password from dd_user to user with bycrpt laravel
        $dd_users = DdUser::with('is_user')->get()->map( function ($item) {
            if($item->is_user){
                if(!\Hash::check($item->password, $item->is_user->password)){
                    User::where('id_dd_user',$item->id_dd_user)
                        ->where('username',$item->username)
                        ->update([
                            'password' => \Hash::make($item->password)
                        ]);
                }
            } else{
                User::firstOrCreate([
                    'username' => $item->username,
                    'id_dd_user' => $item->id_dd_user,
                    'password' => \Hash::make($item->password)
                ]);
            }
        });

        return $this->info('Success updated user from dd_user '.Carbon::now());
    }
}

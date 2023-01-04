<?php

namespace App\Providers;

use App\Repositories\PenjaminanBaru\PenjaminanBaruInterface;
use App\Repositories\PenjaminanBaru\PenjaminanBaruRepository;
use App\Services\PenjaminanBaru\PenjaminanBaruService;
use App\Services\PenjaminanBaru\PenjaminanBaruServiceInterface;
use Illuminate\Support\ServiceProvider;

class PenjaminanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PenjaminanBaruInterface::class,PenjaminanBaruRepository::class);
//        $this->app->bind(PenjaminanBaruServiceInterface::class,PenjaminanBaruService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class TestingController extends Controller
{
    public function index(Request $request){
//        dd($id);
//        $data =  Log::all();
//        $set = Redis::set('logs',Log::all());
        $get = Redis::get('logs');

        dd(count(json_decode($get)) != Log::count());
//        dd($get);

//        $tes = Log::query()->chunk(10, function ($logs){
//            Redis::set('logs',$logs);
//        });

        $data = collect(json_decode($get))->map(function ($item) use($request){
            if(empty($request->all())){
                return searchDataLog($item,['date' => Carbon::now()->format('Y-m-d')]);
            } else {
                return searchDataLog($item,$request->all());
            }
        })->reject(function ($item) {
            return empty($item);
        });
        return DataTables::of($data)->toJson();
//        return DataTables::of(Log::query())->toJson();
    }
}

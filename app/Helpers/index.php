<?php

use Carbon\Carbon;

function searchDataLog(Object $data, Array $req){
    if(isset($req['nomor_aplikasi']) && isset($req['nomor_rekening']) && isset($req['bank']) && isset($req['date']) ){
        if(stristr($data->bank , $data['bank'])
            && stristr($data->request->nomor_aplikasi , $req['nomor_aplikasi'])
            && stristr($data->request->nomor_rekening , $req['nomor_rekening'])
            && Carbon::parse($data->created_at)->format('Y-m-d') == Carbon::parse($req['date'])->format('Y-m-d') ){
            return $data;
        }
    }

    if( isset($req['bank']) && stristr($data->bank , $req['bank']) ){
        return $data;
    }

    if( isset($req['nomor_aplikasi']) && stristr($data->request->nomor_aplikasi , $req['nomor_aplikasi'])) {
        return $data;
    }

    if( isset($req['nomor_rekening']) && stristr($data->request->nomor_rekening , $req['nomor_rekening'])) {
        return $data;
    }

    if( isset($req['date']) && Carbon::parse($data->created_at)->format('Y-m-d') == Carbon::parse($req['date'])->format('Y-m-d')){
        return $data;
    }

    return false;
}

<?php

function rumusIjp(array $data){
    $taripIjp = $data['tarif_ijp'] / 100; // tarif ijp convert ke persen
    $ijpBayar = $data['plafon_kredit'] * $taripIjp; // get total Ijp semua tahun
    $jw = $data['jangka_waktu'] / 12; // convert ke tahun
    $coverage = 70; //coverage per %
    $tampIjp = []; // tamp value ijp bayar non flag (Besar ke Kecil)

    // begin::Membuat Ijp Bayar per tahun dari Besar ke Kecil
    for($i = 1; $i < ($jw + 1); $i++){
        if($i != $jw){ // rumus di gunakan dari index pertama dan index sebelum akhir
            $r = $ijpBayar - ($ijpBayar * ($coverage / 100));
            $ijpBayar = $ijpBayar - $r;
        } else {
            $r = $ijpBayar; // sisah index terakhir tanpa kalkulasi
        }
        $tampIjp[] = $r;
    }
    // end::Membuat Ijp Bayar per tahun dari Besar ke Kecil

    rsort($tampIjp); // Order By Value IJP DESC

    return $tampIjp; //finish
}

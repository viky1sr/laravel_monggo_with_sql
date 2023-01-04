<?php

namespace App\Http\Controllers;

use App\Exports\Rk004Export;
use App\Models\CoreKur\Rk004Dev;
use App\Models\Log;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class TestingController extends Controller
{

    public function testing(Request $request){
        $countInvoice = Invoice::count();
        $index = 0;
        $tampInvoice = [];
        $reqIndex = isset($request->index_search) ? $request->index_search : 0;
        for($i = 0; $i < (ceil($countInvoice / 250)); $i++) {
            $result = Invoice::where()->offset($index)->take(500)->cursor()->each(function ($q) {
                $tampInvoice[] = $q;
            });
        }
        // search fillter manual
        $data = $tampInvoice[$reqIndex];

        return DataTables::of($data)->toJson();

        $datas = [
            [0],
            [1],
            [2],
            [3],
        ];

    }

    public function index(Request $request){

//        dd($id);
//        $data =  Log::all();
//        $set = Redis::set('logs',Log::all());
//        $get = Redis::get('logs');

//        dd(count(json_decode($get)) != Log::count());
//        dd($get);

//        $tes = Log::query()->chunk(10, function ($logs){
//            Redis::set('logs',$logs);
//        });

//        $data = collect(json_decode($get))->map(function ($item) use($request){
//            if(empty($request->all())){
//                return searchDataLog($item,['date' => Carbon::now()->format('Y-m-d')]);
//            } else {
//                return searchDataLog($item,$request->all());
//            }
//        })->reject(function ($item) {
//            return empty($item);
//        });
//        return DataTables::of($data)->toJson();
//        return DataTables::of(Log::query())->toJson();
        $a = [
            [
                'a' => 1,
            ],
            [
                'a' => 1,
            ],
            [
                'a' => 1,
            ],
        ];
        $b = [
            [
                'a' => 1,
            ],
            [
                'a' => 1,
            ],
            [
                'a' => 1,
            ],
        ];

        $c = [];
//        foreach ($a as $i){
//            array_push($b,$i);
//        }

//        dd($b);

        $countRk = Rk004Dev::where('id_dd_bank',15)->limit(100)->get();
        $countRk = count($countRk);
//        dd($countRk);
        $selected_array = array('nawil_kerja','kode_uker', 'nama_bank','nama_bank_cabang');

        $Filename ='Level.csv';
        header('Content-Description: File Transfer');
        header('Content-Type: csv; charset=utf-8');
        header('Content-Type: application/force-download');
        header('Content-Disposition: attachment; filename='.$Filename.'');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();

//        dd(ceil(612 / 150));
        $index = 0;
        $dataRk = [];
        $output = fopen('php://output', 'w');
        fputcsv($output, $selected_array,",");

        for($i = 0; $i < (ceil($countRk / 250)); $i++){
//           $result = Rk004Dev::select(
//                'nawil_kerja',
//                'kode_uker',
//                'nama_bank',
//                'nama_bank_cabang'
//            )->where('id_dd_bank',15)->offset($index)->take(1000)->get()->map(function ($q){
//               return [
//                    'nawil_kerja' => $q->nawil_kerja,
//                    'kode_uker' => $q->kode_uker,
//                    'nama_bank' => $q->nama_bank,
//                    'nama_bank_cabang' => $q->nama_bank_cabang
//                ];
//            });

            $s =  Rk004Dev::select(
                \DB::raw("ROW_NUMBER() OVER (ORDER BY CAST(GETDATE() AS TIMESTAMP))  as id"),
                'nawil_kerja',
                'kode_uker',
                'nama_bank',
                'nama_bank_cabang'
            )->where('id_dd_bank',15)->offset($index)->take(500)->cursor()->each(function ($q) use($output,$countRk,$i){
                $result =  [
                    'nawil_kerja' => $q['nawil_kerja'],
                    'kode_uker' => $q['kode_uker'],
                    'nama_bank' => $q['nama_bank'],
                    'nama_bank_cabang' => $q['nama_bank_cabang']
                ];
                fputcsv($output, $result,",");
            });

//            dd($s->nawil_kerja);
//            return 1;

            return 1;
//           foreach ($result as $r){
//               dd($result);
//               fputcsv($output, $r,"\t");
//           }

//            $path ='/bri/'.$countRk.'/'.$index.'.log';
//            Storage::put($path,json_encode($t));
            $index += 250;
        }


//        foreach ($dataRk as $row){
//            fputcsv($output, $row,"\t");
//        }

        $skl = file_get_contents('php://output');
        dd($skl);

        fclose($output);
        return 123;


//        for($i = 0; $i < (ceil($countRk / 200)); $i++){
//            $path ='/bri/'.$countRk.'/'.$index.'.log';
//            $r = Storage::get($path);
//            array_push($dataRk,json_decode($r));
//            $index += 200;
//        }

//        Redis::set('data_rk',json_encode($dataRk));

//        $get = Redis::del('data_rk');
//        $d = json_decode($get);
//        $array = array($d);
//        dd(123);
//        return DataTables::of($dataRk)->toJson();
//
//        $headings = array('ATTR1');
//
//        $fp = fopen('file.csv', 'w');
//        fputcsv($fp,$headings,"\t");
//        foreach($dataRk as $row) {
//            fputcsv($fp,['sss'],"\t");
//        }


//        dd($Array_data);

// create a file pointer connected to the output stream


//        fputcsv($fp,$headings,"\t");
//        foreach($dataRk as $row) {
//            fputcsv($fp,['sss'],"\t");
//        }

//        fclose($output);


//        fclose($fp);
//        die;


//        return 123;

//        return  Rk004Dev::select(
//            \DB::raw("ROW_NUMBER() OVER (ORDER BY CAST(GETDATE() AS TIMESTAMP))  as id"),
//            'nawil_kerja',
//            'kode_uker',
//            'nama_bank',
//            'nama_bank_cabang'
//        )->where('id_dd_bank',15)->get();

    }


    private function queryRk(){
        $sql = "
           SELECT
      ROW_NUMBER() OVER (ORDER BY CAST(GETDATE() AS TIMESTAMP))  as id,
    rk.nawil_kerja,
   rk.kode_uker,
   rk.nama_bank,
   rk.nama_bank_cabang,
   rk.ktp_npwp,
   rk.no_rekening,
   rk.nama,
   rk.pokok_pembiayaan,
   rk.tgl_perjanjian,
   rk.jangka_waktu,
   rk.nomor_sk,
   rk.tgl_sk,
   rk.flag_satuan,
   rk.nilai_ijp_total,
   rk.ijp_tahun1,
   rk.ijp_tahun2,
   rk.ijp_tahun3,
   rk.ijp_tahun4,
   rk.ijp_tahun5,
   rk.ijp_tahun6,
   rk.ijp_tahun7,
   rk.ijp_tahun8,
   rk.ijp_tahun9,
   rk.ijp_tahun10,
   rk.tgl_penagihan1,
   rk.tgl_penagihan2,
   rk.tgl_penagihan3,
   rk.tgl_penagihan4,
   rk.tgl_penagihan5,
   rk.tgl_penagihan6,
   rk.tgl_penagihan7,
   rk.tgl_penagihan8,
   rk.tgl_penagihan9,
   rk.tgl_penagihan10,
   rk.bayar_tahun_1,
   rk.bayar_tahun_2,
   rk.bayar_tahun_3,
   rk.bayar_tahun_4,
   rk.bayar_tahun_5,
   rk.bayar_tahun_6,
   rk.bayar_tahun_7,
   rk.bayar_tahun_8,
   rk.bayar_tahun_9,
   rk.bayar_tahun_10,
   rk.bayar_tahun_1,
   rk.bayar_tahun_2,
   rk.bayar_tahun_3,
   rk.bayar_tahun_4,
   rk.bayar_tahun_5,
   rk.bayar_tahun_6,
   rk.bayar_tahun_7,
   rk.bayar_tahun_8,
   rk.bayar_tahun_9,
   rk.bayar_tahun_10,
   rk.ijp_bayar,
   cdk.nomor_aplikasi
                FROM rk004_dev rk
                 INNER JOIN calon_debitur_kur cdk ON rk.id_calon_debitur_kur = cdk.id_calon_debitur_kur
                    WHERE  rk.tgl_perjanjian BETWEEN '2015-01-01' AND '20221122'
                       AND rk.id_dd_bank = 15   AND rk.id_dd_bank = 15 AND id_pengajuan_spr IS NULL
                       ORDER BY id OFFSET 0 ROWS FETCH NEXT 500 ROWS ONLY
        ";
    }


    public  function aa(){
        $ps = \DB::connection('core_kur')->select("
         SELECT
      ROW_NUMBER() OVER (ORDER BY CAST(GETDATE() AS TIMESTAMP))  as id,
    rk.nawil_kerja,
   rk.kode_uker,
   rk.nama_bank,
   rk.nama_bank_cabang,
   rk.ktp_npwp,
   rk.no_rekening,
   rk.nama,
   rk.pokok_pembiayaan,
   rk.tgl_perjanjian,
   rk.jangka_waktu,
   rk.nomor_sk,
   rk.tgl_sk,
   rk.flag_satuan,
   rk.nilai_ijp_total,
   rk.ijp_tahun1,
   rk.ijp_tahun2,
   rk.ijp_tahun3,
   rk.ijp_tahun4,
   rk.ijp_tahun5,
   rk.ijp_tahun6,
   rk.ijp_tahun7,
   rk.ijp_tahun8,
   rk.ijp_tahun9,
   rk.ijp_tahun10,
   rk.tgl_penagihan1,
   rk.tgl_penagihan2,
   rk.tgl_penagihan3,
   rk.tgl_penagihan4,
   rk.tgl_penagihan5,
   rk.tgl_penagihan6,
   rk.tgl_penagihan7,
   rk.tgl_penagihan8,
   rk.tgl_penagihan9,
   rk.tgl_penagihan10,
   rk.bayar_tahun_1,
   rk.bayar_tahun_2,
   rk.bayar_tahun_3,
   rk.bayar_tahun_4,
   rk.bayar_tahun_5,
   rk.bayar_tahun_6,
   rk.bayar_tahun_7,
   rk.bayar_tahun_8,
   rk.bayar_tahun_9,
   rk.bayar_tahun_10,
   rk.bayar_tahun_1,
   rk.bayar_tahun_2,
   rk.bayar_tahun_3,
   rk.bayar_tahun_4,
   rk.bayar_tahun_5,
   rk.bayar_tahun_6,
   rk.bayar_tahun_7,
   rk.bayar_tahun_8,
   rk.bayar_tahun_9,
   rk.bayar_tahun_10,
   rk.ijp_bayar,
   cdk.nomor_aplikasi
                FROM rk004_dev rk
                 INNER JOIN calon_debitur_kur cdk ON rk.id_calon_debitur_kur = cdk.id_calon_debitur_kur
                    WHERE  rk.tgl_perjanjian BETWEEN '2015-01-01' AND '20221122'
                       AND rk.id_dd_bank = 15   AND rk.id_dd_bank = 15 AND id_pengajuan_spr IS NULL
                       ORDER BY id OFFSET 0 ROWS FETCH NEXT 500 ROWS ONLY
        ");

        $temps = [];
        $months =[1,2,3,4,5,6,7,8,9,10,11,12];
        foreach ($ps as $key => $item){
//            dd($item);
            $values = [
                'id' => $item->id,
                'nawil_kerja' => $item->nawil_kerja,
                'kode_uker' => $item->kode_uker,
                'nama_bank' => $item->nama_bank,
                'nama_bank_cabang' => $item->nama_bank_cabang,
                'ktp_npwp' => $item->ktp_npwp,
                'no_rekening' => $item->no_rekening,
                'nama' => $item->nama,
                'pokok_pembiayaan' => $item->pokok_pembiayaan,
                'tgl_perjanjian' => $item->tgl_perjanjian,
                'jangka_waktu' => $item->jangka_waktu,
                'nomor_sk' => $item->nomor_sk,
                'tgl_sk' => $item->tgl_sk,
                'flag_satuan' => $item->flag_satuan,
                'nilai_ijp_total' => $item->nilai_ijp_total,
                'ijp_bayar' => $item->ijp_bayar,
                'nomor_aplikasi' => $item->nomor_aplikasi
            ];
            if($item->ijp_tahun1 > 0){
                array_merge($values,[ 'ijp_tahun1' => $item->ijp_tahun1,]);
            } else if($item->ijp_tahun2 > 0){
                array_merge($values,[ 'ijp_tahun2' => $item->ijp_tahun2,]);
            } else if($item->ijp_tahun3 > 0){
                array_merge($values,[ 'ijp_tahun3' => $item->ijp_tahun3,]);
            } else if($item->ijp_tahun4 > 0){
                array_merge($values,[ 'ijp_tahun4' => $item->ijp_tahun4,]);
            } else if($item->ijp_tahun5 > 0){
                array_merge($values,[ 'ijp_tahun5' => $item->ijp_tahun5,]);
            } else if($item->ijp_tahun6 > 0){
                array_merge($values,[ 'ijp_tahun6' => $item->ijp_tahun6,]);
            } else if($item->ijp_tahun7 > 0){
                array_merge($values,[ 'ijp_tahun7' => $item->ijp_tahun8,]);
            } else if($item->ijp_tahun9 > 0){
                array_merge($values,[ 'ijp_tahun9' => $item->ijp_tahun9,]);
            } else if($item->ijp_tahun10 > 0){
                array_merge($values,[ 'ijp_tahun10' => $item->ijp_tahun10,]);
            }

            if($item->tgl_penagihan1 != null){
                array_merge($values,[ 'tgl_penagihan1' => $item->tgl_penagihan1,]);
            } else if($item->tgl_penagihan2 != null){
                array_merge($values,[ 'tgl_penagihan2' => $item->tgl_penagihan3,]);
            } else if($item->tgl_penagihan3 != null){
                array_merge($values,[ 'tgl_penagihan3' => $item->tgl_penagihan3,]);
            } else if($item->tgl_penagihan4 != null){
                array_merge($values,[ 'tgl_penagihan4' => $item->tgl_penagihan4,]);
            } else if($item->tgl_penagihan5 != null){
                array_merge($values,[ 'tgl_penagihan5' => $item->tgl_penagihan5,]);
            } else if($item->tgl_penagihan6 != null){
                array_merge($values,[ 'tgl_penagihan6' => $item->tgl_penagihan6,]);
            } else if($item->tgl_penagihan7 != null){
                array_merge($values,[ 'tgl_penagihan7' => $item->tgl_penagihan7,]);
            } else if($item->tgl_penagihan8 != null){
                array_merge($values,[ 'tgl_penagihan8' => $item->tgl_penagihan8,]);
            } else if($item->tgl_penagihan9 != null){
                array_merge($values,[ 'tgl_penagihan9' => $item->tgl_penagihan9,]);
            } else if($item->tgl_penagihan10 != null){
                array_merge($values,[ 'tgl_penagihan10' => $item->tgl_penagihan10,]);
            }

            if($item->bayar_tahun_1 != null){
                array_merge($values,[ 'bayar_tahun_1' => $item->bayar_tahun_1,]);
            }
            elseif($item->bayar_tahun_2 != null){
                array_merge($values,[ 'bayar_tahun_2' => $item->bayar_tahun_2,]);
            }
            elseif($item->bayar_tahun_3 != null){
                array_merge($values,[ 'bayar_tahun_3' => $item->bayar_tahun_3,]);
            }
            elseif($item->bayar_tahun_4 != null){
                array_merge($values,[ 'bayar_tahun_4' => $item->bayar_tahun_4,]);
            }
            elseif($item->bayar_tahun_5 != null){
                array_merge($values,[ 'bayar_tahun_5' => $item->bayar_tahun_5,]);
            }
            elseif($item->bayar_tahun_6 != null){
                array_merge($values,[ 'bayar_tahun_6' => $item->bayar_tahun_6,]);
            }
            elseif($item->bayar_tahun_7 != null){
                array_merge($values,[ 'bayar_tahun_7' => $item->bayar_tahun_7,]);
            }
            elseif($item->bayar_tahun_8 != null){
                array_merge($values,[ 'bayar_tahun_8' => $item->bayar_tahun_8,]);
            }
            elseif($item->bayar_tahun_9 != null){
                array_merge($values,[ 'bayar_tahun_9' => $item->bayar_tahun_9,]);
            }
            elseif($item->bayar_tahun_10 != null){
                array_merge($values,[ 'bayar_tahun_10' => $item->bayar_tahun_10,]);
            }

            if(!empty($ps[$key+1])){

                if(date('M',strtotime($ps[$key+1]->tgl_perjanjian)) === date('M',strtotime($item->tgl_perjanjian)) ){
                    $temps[] = $values;
                }
            } else {
                if(date('M',strtotime(end($temps)['tgl_perjanjian'])) === date('M',strtotime($item->tgl_perjanjian)) ){
                    $temps[] = $values;
                    $path = 'bri/'.date('Y/M',strtotime($item->tgl_perjanjian)).'/'.date('Y-m-d',strtotime($item->tgl_perjanjian)).'.log';
                    Storage::put($path,json_encode($temps));
                } else {
                    dd($values);
                    $temps[] = $values;
                }
            }
        }
    }

}

<?php

namespace App\Services\PenjaminanBaru;

use App\Repositories\PenjaminanBaru\PenjaminanBaruRepository;

class PenjaminanBaruService implements PenjaminanBaruServiceInterface
{
    protected $repo;
    protected $tgl_skg;

    public function __construct(PenjaminanBaruRepository $repo){
        $this->repo = $repo;
        $this->tgl_skg = date('Y-m-d');
    }

    public function createPenjaminanBaru(array $data)
    {
        $data = array_merge($data,[
            'coverage' => 70 ,'j_create_time' => $this->tgl_skg, 'j_flag_persetujuan' => 0,
            'date_created' => $this->tgl_skg,'tipe_kredit' => 1
        ]);
        $calonDebitur = $this->repo->storeCalonDebiturKur($data);
        $sp2Kur = $this->repo->storeSp2Kur(
            collect($data)->only(
                'cif','no_pk','tanggal_pk','tanggal_rekam','tanggal_awal','tanggal_akhir','nomor_aplikasi','no_rekening','id_dd_bank','created_by',
                'status_data','date_created'
            )->merge(['id_calon_debitur_kur' => $calonDebitur['id_calon_debitur_mikro']])->toArray()
        );
        $sertifikat = $this->generateSertifikat($data);
        dd($sertifikat);
        $dataSertifikatKur = [
            'no_rekening' => $data['no_rekening'],
            'no_sertifikat' => $sertifikat['nomor_sp'],
            'tanggal_sertifikat' => $sertifikat['tgl_sp'],
            'date_created' => $this->tgl_skg,
            'id_calon_debitur_kur_sync' => $calonDebitur['id_calon_debitur_mikro'],
            'id_dd_bank' => $data['calon_debitur_kur_mikro']['id_dd_bank'],
            'kode_uker' => $data['kode_uker'],
            'flag_transfer' => 0,
            'jenis_linkage' => $data['calon_debitur_kur_mikro']['jenis_linkage'],
            'id_calon_debitur_kur' => $calonDebitur['id_calon_debitur_core'],
            'created_by' => date('Y-m-d H:i:s')
        ];
        $insertSertifikatKur = $this->repo->storeSertifikat($dataSertifikatKur);
        return [
            'no_rekening' => $data['no_rekening'],
            'nomor_aplikasi' => $data['nomor_aplikasi'],
            'no_sertifikat' => '',
            'link_url' => [
                'url_sertifikat' => '',
                'url_ijp' => '',
                'data' => $calonDebitur
            ]
        ];
    }

    public function generateSertifikat(array $data)
    {
        return $this->repo->generateSertifikat($data);
    }
}

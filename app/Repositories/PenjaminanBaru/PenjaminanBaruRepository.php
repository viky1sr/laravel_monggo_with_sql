<?php

namespace App\Repositories\PenjaminanBaru;

use App\Exceptions\PenjaminanErrorException;
use App\Models\CoreKur\CalonDebiturKurCore;
use App\Models\CoreKur\SertifikatKurCore;
use App\Models\CoreKur\Sp2KurCore;
use App\Models\KurMikro\CalonDebiturKurMikro;
use App\Models\KurMikro\DdBankCabang;
use App\Models\KurMikro\SertifikatKurMikro;
use App\Models\KurMikro\Sp2KurMikro;
use Illuminate\Support\Facades\DB;

class PenjaminanBaruRepository implements PenjaminanBaruInterface
{
    protected $modelCalonDebiturMikro;
    protected $modelCalonDebiturCore;
    protected $modelSp2KurMikro;
    protected $modelSp2KurCore;
    protected $modelSertifikatMikro;
    protected $modelSertifikatCore;
    protected $modelDdBankCabang;

    public function __construct(
        CalonDebiturKurMikro $modelCalonDebiturMikro,
        CalonDebiturKurCore $modelCalonDebiturCore,
        Sp2KurMikro $modelSp2KurMikro,
        Sp2KurCore $modelSp2KurCore,
        SertifikatKurMikro $modelSertifikatMikro,
        SertifikatKurCore $modelSertifikatCore,
        DdBankCabang $modelDdBankCabang
    ){
        $this->modelCalonDebiturMikro = $modelCalonDebiturMikro;
        $this->modelCalonDebiturCore = $modelCalonDebiturCore;
        $this->modelSp2KurMikro = $modelSp2KurMikro;
        $this->modelSp2KurCore = $modelSp2KurCore;
        $this->modelSertifikatMikro = $modelSertifikatMikro;
        $this->modelSertifikatCore = $modelSertifikatCore;
        $this->modelDdBankCabang = $modelDdBankCabang;
    }

    public function storeCalonDebiturKur(array $request) : array
    {
        $data = collect($request)->except([ 'cif','no_pk','no_rekening','tanggal_akhir','tanggal_awal','tanggal_pk'])->toArray();
        $datakowil = $this->getKowil($request);
        $mikro = $this->modelCalonDebiturMikro->create($data);
        $core = $this->modelCalonDebiturCore->create(collect($data)->merge([
            'id_dd_bank_cabang' => $datakowil['id_dd_bank_cabang'],
            'id_dc_wilayah_kerja' => $datakowil['id_dc_wilayah_kerja'],
            'id_calon_debitur_kur_sync' => isset($mikro['id_calon_debitur_kur']) ? $mikro['id_calon_debitur_kur'] : $mikro['id']
        ])->toArray());
        return [
            'id_calon_debitur_mikro' => isset($mikro['id_calon_debitur_kur']) ? $mikro['id_calon_debitur_kur'] : $mikro['id'],
            'id_calon_debitur_core' => isset($core['id_calon_debitur_kur']) ? $core['id_calon_debitur_kur'] : $core['id'],
        ];
    }

    public function storeSp2Kur(array $request): array
    {
        $mikro = $this->modelSp2KurMikro->create($request);
        $core = $this->modelSp2KurCore->create(collect($request)->except('id_calon_debitur_kur')->toArray());
        return $request;
    }

    public function storeSertifikat(array $request): array
    {
        // TODO: Implement storeSertifikat() method.
    }

    public function generateSertifikat(array $data)
    {
        $datakowil = $this->getKowil($data);
        $newData = [
            $datakowil['kode_uker'],
            $datakowil['id_dc_wilayah_kerja'],
            $data['id_dd_bank'],
            $data['jenis_linkage'] <> 'C' ? 0 : 1,
            'MINGGUAN'
        ];
        return DB::connection('core_kur')
            ->select(" exec generate_sertifikat_dki ?,?,?,?,? ",$newData);
    }

    public function getKowil(array $data): array
    {
        $data = DdBankCabang::with('is_kowil')->where(['kode_uker' => $data['kode_uker'], 'id_dd_bank' => $data['id_dd_bank']])->first();
        return [
            'id_dc_wilayah_kerja' => $data->id_dc_wilayah_kerja,
            'id_dd_bank_cabang' => $data->id_dd_bank_cabang,
            'kode_uker' => $data->kode_uker
        ];
    }
}

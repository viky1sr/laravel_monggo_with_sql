<?php
//die();
require_once("../_lib/db3.php");
loadlib("function","olah_tabel_tanpa_trans");
loadlib("function","date2str_baru");
loadlib("function","submit_uang");
loadlib("function","ijp_kur");
loadlib("function","hitunganIjpFlat");
loadlib('class', 'CSVWriter');
loadlib("class", "autoNumberCertificate");
loadlib('class', 'IJP');
loadlib("function","generate_angsuran");
$db->debug = true;

$nama_file = "generateSertifikatSchedulerSpr__v1_dpn_baru.txt";
//	if(!file_exists($nama_file)){
//		$filee=fopen($nama_file,'w');
//		fclose($filee);
//	}
//	else{
//		die("Proses Sedang berjalan");
//	}
// == [akhir 30 mei 2019] -> dimatikan dulu sementara untuk patch loan type null bri
$sqlPlus='';
//
$id_dc_produk = 30;

$sqlCode = "Select kode_sertifikat from dc_produk where id_dc_produk = {$id_dc_produk}";
$arrCode = $db->GetAll($sqlCode);

$result = true;

$tanggal3 = date('Y-m-d');
$tanggal4 = (int) date('Ymd'); //penambahan

$noSP 		= 0;
$sql = "
		select top 1000
			awal.id_calon_debitur_kur,
			a.id_calon_debitur_kur
			, a.nama_debitur as nama_debitur
			, awal.alamat as alamat_debitur
			, awal.tgl_lahir as tanggal_lahir
			, awal.ktp_npwp as no_identitas
			, a.kode_uker as kode_uker
			, a.nomor_aplikasi as nomor_aplikasi
			, a.plafon_kredit as plafon_kredit
			, a.coverage as coverage
			, a.jangka_waktu as jangka_waktu
			, awal.jumlah_tenaga_kerja as jml_t_kerja
			, awal.ket_peruntukan
			, awal.lbu_kode as kode_sektor_lama
			, a.id_dd_bank
			, awal.id_dd_bank as id_dd_bank_lama
			, a.j_flag_persetujuan_spr
			, coalesce(spr_lama.id_dc_wilayah_kerja,awal.id_dc_wilayah_kerja) as id_dc_wilayah_kerja
			, coalesce(spr_lama.ko_wil,awal.ko_wil)  as ko_wil
			, a.tanggal_awal as tanggal_rekam
			, a.jenis_kur
			, a.jenis_kredit
			, a.kode_sektor
			, coalesce(spr_lama.nomor_sk,awal.nomor_sk) as nomor_sk_lama
			, coalesce(spr_lama.tgl_sk,awal.tgl_sk) as tgl_sk_lama
			, a.flag_p
			, a.flag_s
			, a.flag_r
			, a.id_pengajuan_spr
			, awal.kode_uker as kode_uker_lama
			, a.no_pk_baru as no_pk
			, a.tgl_pk_baru as tanggal_pk
			, coalesce(spr_lama.id_dc_wilayah_kerja,awal.id_dc_wilayah_kerja) as wil_prev
			, coalesce(spr_lama.id_opmt_transaksi_penjaminan,awal.id_opmt_transaksi_penjaminan) as id_opmt_transaksi_penjaminan
			, awal.id_opmt_transaksi_penjaminan as id_opmt_transaksi_awal
			, a.tanggal_awal as tanggal_awal
			, a.tanggal_akhir as tanggal_akhir
			, coalesce(spr_lama.no_rekening_baru,awal.no_rekening) as no_rekening_lama
			, a.no_rekening_baru as no_rekening
			, coalesce(spr_lama.id_opmt_sertifikat,awal.id_opmt_sertifikat) as id_opmt_sertifikat
			, coalesce(spr_lama.id_opmt_pengendalian,awal.id_opmt_pengendalian) as id_opmt_pengendalian
			, a.no_sertifikat as no_sertifikat_baru
			, a.tgl_sertifikat as tgl_sertifikat_baru
			,spr_jml.jml_s as jml_suplesi
			,spr_jml.jml_r as jml_restruk_batch
			,spr_jml.jml_p as jml_perpanjangan
			,flag_penundaan
			,coalesce(jw_sebelumnya,coalesce(spr_lama.jw,awal.jw)) as jw_sebelumnya
			,coalesce(jw_terlewati, DATEDIFF(month,coalesce(spr_lama.tgl_awl, awal.tgl_awl), a.tanggal_awal)) as jw_terlewati
			,coalesce(sisa_jw_sebelumnya, (coalesce(jw_sebelumnya,coalesce(spr_lama.jw,awal.jw))- coalesce(jw_terlewati, DATEDIFF(month,coalesce(spr_lama.tgl_awl, awal.tgl_awl), a.tanggal_awal)) )) as sisa_jw_sebelumnya
			, coalesce(a.flag_covid,0) as flag_covid
			, coalesce(a.status_rekening,0) as status_rekening
				, (case when coalesce(a.status_rekening,1) =0 then 0 else coalesce(a.outstanding, coalesce(spr_lama.posisi_piutang_lama,spr_lama.pokok_pembiayaan_lama)) end) as outstanding
		--,coalesce(a.outstanding,case when coalesce(a.status_rekening,0) =0 then 0 else coalesce(spr_lama.posisi_piutang_lama,spr_lama.pokok_pembiayaan_lama) end) as outstanding
			from pengajuan_spr a
			left join (
				SELECT
				  ROW_NUMBER() OVER(PARTITION BY a.id_calon_debitur_kur  ORDER BY a.id_pengajuan_spr desc) AS Row,
				 a.*, b.id_opmt_transaksi_penjaminan, c.id_opmt_permohonan,c.id_dc_wilayah_kerja, d.id_opmt_sertifikat, e.ko_wil
				 			, d.nomor_sk , b.pokok_pembiayaan as pokok_pembiayaan_lama, b.jangka_waktu as jw, b.waktu_realisasi_pembayaran as tgl_awl
							, d.tgl_sk , pen.id_opmt_pengendalian, pen.posisi_piutang as posisi_piutang_lama
				FROM tbl_transaksi_spr_count a
				join opmt_transaksi_penjaminan b on a.id_opmt_transaksi_baru = b.id_opmt_transaksi_penjaminan
				join opmt_permohonan c on c.id_opmt_permohonan = b.id_opmt_permohonan
				join opmt_sertifikat d on d.id_opmt_sertifikat = c.id_opmt_sertifikat
				join dc_wilayah_kerja_new e on e.id_dc_wilayah_kerja= c.id_dc_wilayah_kerja
				left join opmt_pengendalian pen on pen.id_opmt_transaksi_penjaminan = b.id_opmt_transaksi_penjaminan and pen.flag_position = 1
			) spr_lama on spr_lama.id_calon_debitur_kur = a.id_calon_debitur_kur and Row=1

			left join (
				SELECT
				  id_calon_debitur_kur ,count(*) as jml_spr, max(no_perpanjangan) as jml_p,max(no_suplesi) as jml_s, max(no_restrukturisasi) as jml_r
				FROM tbl_transaksi_spr_count a
				group by id_calon_debitur_kur
			) spr_jml on spr_jml.id_calon_debitur_kur = a.id_calon_debitur_kur and Row=2

			left join
			(
			select d.id_opmt_transaksi_penjaminan,
			 c.alamat
			, c.tgl_lahir
			, c.ktp_npwp
			, d.jumlah_tenaga_kerja
			, d.ket_peruntukan
			, l.lbu_kode
			, wil.id_dc_wilayah_kerja
			, wil.ko_wil as ko_wil
			, f.nomor_sk
			, f.tgl_sk
			, h.kode_uker as kode_uker_lama
			, no_rekening
			, d.id_calon_debitur_kur
			,d.jangka_waktu as jw
			, d.waktu_realisasi_pembayaran as tgl_awl
			, j.id_dd_bank
			, h.kode_uker
			, pen.id_opmt_pengendalian
			, f.id_opmt_sertifikat
			from opmt_nasabah_rek b --on a.no_rekening_lama = b.no_rekening
			inner join opmt_nasabah c on c.id_opmt_nasabah = b.id_opmt_nasabah
			inner join opmt_transaksi_penjaminan d on d.id_opmt_nasabah = c.id_opmt_nasabah
			inner join opmt_permohonan e on e.id_opmt_permohonan = d.id_opmt_permohonan
			inner join opmt_sertifikat f on f.id_opmt_sertifikat = e.id_opmt_sertifikat
			inner join dd_bank_cabang h on h.id_dd_bank_cabang = e.id_dd_bank_cabang
			inner join dd_bank j on j.id_dd_bank = h.id_dd_bank
			inner join opmt_sektor k on k.id_opmt_transaksi_penjaminan = d.id_opmt_transaksi_penjaminan
			inner join dc_sektor_lbu l on l.id_dc_sektor_lbu = k.id_dc_sektor_lbu
			inner join dc_wilayah_kerja wil on wil.id_dc_wilayah_kerja = e.id_dc_wilayah_kerja
			left join opmt_pengendalian pen on pen.id_opmt_transaksi_penjaminan = d.id_opmt_transaksi_penjaminan and pen.flag_position = 1

			) awal on a.id_calon_debitur_kur = awal.id_calon_debitur_kur
			LEFT JOIN pengajuan_klaim klaim ON a.no_rekening_baru = klaim.no_rekening AND a.id_dd_bank = klaim.id_dd_bank
			where a.j_flag_persetujuan_spr = 0 and (a.cabang_rekanan is not null or a.cabang_rekanan not in (101,0)) and a.cabang_rekanan not in (0,101)
			and a.no_sertifikat is not null
			AND a.no_sertifikat IS NOT NULL
			AND klaim.no_rekening IS NULL
			--and a.id_dd_bank in (9)
			-- and a.id_pengajuan_spr  not in (858258)
			-- and a.id_pengajuan_spr  = 858258
			-- (spr 23 = BPD Bali, 41 = BPD Jateng, 52 = BPD Sumut, 62 = BTN)
			-- and a.id_dd_bank in (23,41,52,62,11,9,25)
			and a.id_dd_bank in (36)
			order by a.id_pengajuan_spr


";
print_r("<pre>".$sql);
//PRINT_R($sql);die();
$arrSP2 = $db->GetAll($sql);
//print_r($arrSP2);
$countSP = count($arrSP2);

echo "Jumlah Data SPR : " . $countSP;
//print_r($arrSP2);
/*if(!in_array($arrSP2['j_flag_persetujuan'],array(0,5,6))){
	print_r("Data Sudah Pernah Ditolak atau Diterima");
	die();
}*/
$dd_temp_error = array();
if($countSP > 0)
{
    //print_r('oke');
    for($i=0;$i<$countSP;$i++)
    {
        try
        {
            echo "<br/>===============================================================================<br/>";
            echo "iterasi ke -> ".$i." | id_pengajuan_spr : ".$arrSP2[$i]['id_pengajuan_spr']."<br/>";

            $db->BeginTrans();
            unset($sertifikat);
            $id_dd_bank = $arrSP2[$i]['id_dd_bank'];
            $id_dd_bank_lama = $arrSP2[$i]['id_dd_bank_lama'];
            $id_dc_wilayah_kerja = $arrSP2[$i]['id_dc_wilayah_kerja'];
            $id_calon_debitur_kur = $arrSP2[$i]['id_calon_debitur_kur'];
            $no_app = $arrSP2[$i]['nomor_aplikasi'];
            $nomor_aplikasi = "'$no_app'" ;
            $plafon_kredit=$arrSP2[$i]['plafon_kredit'];
            $coverage=$arrSP2[$i]['coverage'];
            $jangka_waktu=$arrSP2[$i]['jangka_waktu'];
            $jml_t_kerja = $arrSP2[$i]['jml_t_kerja'];
            $jenis_kredit = $arrSP2[$i]['jenis_kredit'];
            if($arrSP2[$i]['jenis_kredit'] == 1)
            {
                $jenis_kredit1 = 'Kredit Modal Kerja';
            }
            else if($arrSP2[$i]['jenis_kredit'] == 2)
            {
                $jenis_kredit1 = 'Kredit Investasi';
            }
            else
            {
                $jenis_kredit1 = 'Tidak Jelas';
            }
            $kode_sektor = $arrSP2[$i]['kode_sektor'];
            $sqlSktr = "Select coverage,coverage_sesuai, coverage_permenko13 from dc_sektor_lbu where lbu_kode = '{$kode_sektor}'";
            $kode_sktr = $db->GetAll($sqlSktr);

            $cov_sesuai = $kode_sktr[0]['coverage'];
            $cov = $kode_sktr[0]['coverage_sesuai'];
            $cov_permen = $kode_sktr[0]['coverage_permenko13'];
            $flag_p = $arrSP2[$i]['flag_p'];
            $flag_s = $arrSP2[$i]['flag_s'];
            $flag_r = $arrSP2[$i]['flag_r'];
            $jml_spr = $arrSP2[$i]['jml_spr'];
            $jml_perpanjangan = $arrSP2[$i]['jml_perpanjangan'];
            $jml_suplesi = $arrSP2[$i]['jml_suplesi'];
            $no_batch = $arrSP2[$i]['no_batch'];
            $tgl_sk_lama= $arrSP2[$i]['tgl_sk_lama'];
            $jml_restruk = $arrSP2[$i]['jml_restruk'];
            $date = $arrSP2[$i]['tanggal_awal'];
            $nomor_sk_lama = $arrSP2[$i]['nomor_sk_lama'];
            $id_pengajuan_spr = $arrSP2[$i]['id_pengajuan_spr'];
            $id_calon_debitur_kur = $arrSP2[$i]['id_calon_debitur_kur'];
            $kode_uker = $arrSP2[$i]['kode_uker'];
            $kode_uker_lama = $arrSP2[$i]['kode_uker_lama'];
            $id_opmt_transaksi_lama = $arrSP2[$i]['id_opmt_transaksi_penjaminan'];
            $status_rekening = $arrSP2[$i]['status_rekening'];
            $id_opmt_transaksi_awal = $arrSP2[$i]['id_opmt_transaksi_awal'];
            $id_opmt_sertifikat_lama = $arrSP2[$i]['id_opmt_sertifikat'];
            $id_opmt_pengendalian_lama = $arrSP2[$i]['id_opmt_pengendalian'];
            $no_sertifikat_baru =  $arrSP2[$i]['no_sertifikat_baru'];
            $tgl_sertifikat_baru =  $arrSP2[$i]['tgl_sertifikat_baru'];
            $jml_restruk_batch = $kode_Srtfkt[0]['jml_restruk_batch'];
            $outstanding = $arrSP2[$i]['outstanding'];
            $flag_covid = $arrSP2[$i]['flag_covid'];
            $flag_penundaan = $arrSP2[$i]['flag_penundaan'];
            $jw_sebelumnya = $arrSP2[$i]['jw_sebelumnya'];
            $jw_terlewati = $arrSP2[$i]['jw_terlewati'];
            $sisa_jw_sebelumnya = $arrSP2[$i]['sisa_jw_sebelumnya'];

            $tgl_pk = $arrSP2[$i]['tanggal_pk'];

            If($kode_uker != $kode_uker_lama){
                echo $nomor_aplikasi;
                echo  'kode uker lama tidak sama dengan sebelumnya'.$kode_uker.' kode uker lama '.$kode_uker_lama;
                $dd_temp_error[$i]['keterangan_error'] = 'kode uker lama tidak sama dengan sebelumnya'.$kode_uker.' kode uker lama '.$kode_uker_lama;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $db->RollbackTrans();
                continue;
            }

            If($id_dd_bank != $id_dd_bank_lama){
                echo $nomor_aplikasi;
                echo  'id_dd_bank lama tidak sama dengan sebelumnya '.$id_dd_bank.' id_dd_bank lama '.$id_dd_bank_lama;
                $dd_temp_error[$i]['keterangan_error'] =  'id_dd_bank lama tidak sama dengan sebelumnya '.$id_dd_bank.' id_dd_bank lama '.$id_dd_bank_lama;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $db->RollbackTrans();
                continue;
            }

            //echo "iterasi ke -> ".$i." | id_pengajuan_spr : ".$arrSP2[$i]['id_pengajuan_spr']."<br/>";

            //cek sudah berapa kali SPR
            echo "<br/>jumlah perpanjangan : ".$jml_perpanjangan." | jml_suplesi : ".$jml_suplesi."<br/>";
            if(($jml_perpanjangan == 0) && ($jml_suplesi == 0))
                $jml_spr = 1;
            else if(($jml_perpanjangan > 0) && ($jml_suplesi == 0))
                $jml_spr = $jml_perpanjangan;
            else if (($jml_perpanjangan == 0) && ($jml_suplesi > 0))
                $jml_spr = $jml_suplesi;
            else
                $jml_spr = $jml_suplesi + $jml_perpanjangan ;

            //print_r($no_sertifikat);
            //print_r($batch);
            //update calon debitur kur untul menjumlah debitur tersebut berapa kali perpanjangan, suplesi, restruk
            echo "masuk else jumlah perpanjangan dan jumlah suplesi <> 0<br/>";

            $sqlId = "Select id_opmt_sertifikat from opmt_sertifikat where nomor_sk = '$no_sertifikat_baru' and tgl_sk = '$tgl_sertifikat_baru'";
            $id = $db->GetAll($sqlId);
            $id_opmt_sertifikat = $id[0]['id_opmt_sertifikat'];
            if(empty($id_opmt_sertifikat)){
                $tglSK = date('dmY');
                $kode_id_dd_user = str_pad($usrtmp["npp"], 5, '0', STR_PAD_LEFT);
                $kode_sertifikat = $kode_id_dd_user . substr($tglSK, -2) . substr($tglSK, 5, 2) . substr($tglSK, 0, 4) . date('H') . date('i');
                $opmt_sertifikat = array();
                unset($opmt_sertifikat);
                $opmt_sertifikat['nomor_sk'] = $no_sertifikat_baru;
                $opmt_sertifikat['tgl_sk'] = $tgl_sertifikat_baru;
                $opmt_sertifikat['flag_cetak'] = 0;
                $opmt_sertifikat['flag_transfer'] = 0;
                $opmt_sertifikat['tgl_status'] = $tgl_sertifikat_baru;
                $opmt_sertifikat['flag_edit'] = 0;
                $opmt_sertifikat['flag_channeling'] = 0;
                $opmt_sertifikat['kode_sertifikat'] = $kode_sertifikat;

                $result = insert_tabel_tanpa_trans('opmt_sertifikat', $opmt_sertifikat);
                if($result != false)
                {
                    $id_opmt_sertifikat = $lastInsertID;

                }
                else {
                    //$dd_temp_error = array();
                    //unset($dd_temp_error);
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT SERTIFICAT';
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    //throw new exception("GAGAL INPUT KE OPMT SERTIFICAT");
                    //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                    $db->RollbackTrans();
                    continue;

                }
            }


            //untuk menggenerate ijp
            if($id_dd_bank == 15)	{
                echo "masuk ke step generate jadwal ijp spr<br/>";
                $sqlIjp = "Execute generate_jadwal_ijp_spr_175_7_prod '{$arrSP2[$i]['no_rekening']}', {$id_pengajuan_spr}";
                $result =  $db->Execute($sqlIjp);
                $sqlTotal = "select sum(nominal_ijp) as ijp from opmt_jadwal_ijp_spr where no_fasilitas = '{$arrSP2[$i]['no_rekening']}'";
                $ijpTot =  $db->GetAll($sqlTotal);
                echo "<br/>";
                // ini begini saja, karena takut error lagi, angsuran sama total tidak sama (Amrid)
            }else if($id_dd_bank == 11 || $id_dd_bank == 52 || ($id_dd_bank== 41 and $tgl_pk >= '2019-01-01')){
                //} else if (in_array($id_dd_bank,array(11,52)) || ($id_dd_bank== 41 and $tgl_pk >= '2019-01-01')) { /*2020-07-10 diubah bentuk array agar lebih rapih*/
                $sql_ijp = "select dbo.perhitungan_ijp_bank_bni_175_7({$jangka_waktu},{$plafon_kredit},{$id_dd_bank}, '{$arrSP2[$i]['tanggal_pk']}', '{$arrSP2[$i]['jenis_kur']}') as ijp";
                $ijpTot = $db->GetAll($sql_ijp);
            }
            else{
                $sql_ijp = "select dbo.perhitungan_ijp_non_bri_175({$jangka_waktu},{$plafon_kredit},{$id_dd_bank}, '{$arrSP2[$i]['tanggal_pk']}', '{$arrSP2[$i]['jenis_kur']}') as ijp";
                $ijpTot = $db->GetAll($sql_ijp);
            }
            //	PRINT_R($ijpTot);
            $obj = read_tabel_tanpa_trans('dd_bank_cabang', 'id_dd_bank_cabang', "WHERE kode_uker = '{$kode_uker}' and id_dd_bank = '{$id_dd_bank}' ");
            $id_dd_bank_cabang = $obj->Fields('id_dd_bank_cabang');

            if($id_dd_bank_cabang == ''){
                $bank_cabang = array();
                $bank_cabang['id_dd_bank'] 				= $id_dd_bank;
                $bank_cabang['kode_uker'] 				= $kode_uker;
                $bank_cabang['id_dc_wilayah_kerja'] 	= $id_dc_wilayah_kerja;
                $bank_cabang['flag_transfer'] 			= 0;
                $bank_cabang['tgl_status'] 				= date("Y-m-d H:i:s");
                $result = insert_tabel_tanpa_trans('dd_bank_cabang', $bank_cabang);
                if($result != false)
                {
                    $id_dd_bank_cabang = $lastInsertID;
                }else{
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $dd_temp_error[$i]['keterangan_error'] = "GAGAL INPUT KE DD BANK CABANG!";
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $db->RollbackTrans();
                    continue;
                }
            }

            //memasukkan ke opmt_permohonan
            $objIJP = new IJP();

            unset($opmt_permohonan);
            $opmt_permohonan = array();
            $opmt_permohonan['id_opmt_sertifikat'] 		= $id_opmt_sertifikat;
            $opmt_permohonan['no_permohonan'] 			= 'Pusat' . "-{$arrSP2[$i]['tanggal_rekam']}-" . str_pad(++$noSP, 4, '0', STR_PAD_LEFT) . '-' . date('YmdHis');
            $opmt_permohonan['id_dc_produk'] 			= $id_dc_produk;
            $opmt_permohonan['id_dd_bank_cabang'] 		= $id_dd_bank_cabang;
            $opmt_permohonan['id_dc_wilayah_kerja'] 	= $id_dc_wilayah_kerja;

            $obj_prop = read_tabel_tanpa_trans('dc_wilayah_kerja', 'id_dc_propinsi', "WHERE id_dc_wilayah_kerja = {$id_dc_wilayah_kerja}");
            $id_dc_propinsi = $obj_prop->Fields('id_dc_propinsi');

            $opmt_permohonan['id_dc_propinsi'] 			= $id_dc_propinsi;
            $opmt_permohonan['no_surat'] 				= $nomor_aplikasi;
            $opmt_permohonan['tanggal_surat'] 			= $arrSP2[$i]['tanggal_rekam'];
            $opmt_permohonan['nilai_coverage'] 			= $cov_sesuai;
            $opmt_permohonan['nilai_coverage_final'] 	= $cov_sesuai;
            $opmt_permohonan['flag_syariah'] 			= 0;
            $opmt_permohonan['pokok_pembiayaan'] 		= $arrSP2[$i]['plafon_kredit'];
            $opmt_permohonan['total_ijp']  				= $ijpTot[0]['ijp'];
            $opmt_permohonan['jumlah_tenaga_kerja'] 	= $jml_t_kerja; // Dihitung setelah proses perhitungan selesai.
            $opmt_permohonan['flag_proses'] 			= 1;
            $opmt_permohonan['risk_sharing'] 			= 0;
            $opmt_permohonan['flag_transfer'] 			= 0;
            $opmt_permohonan['tgl_status'] 				= date("Y-m-d H:i:s");
            $opmt_permohonan['id_pengajuan_spr'] 		= $id_pengajuan_spr;
            //	print_r($opmt_permohonan);
            //die();
            $result = insert_tabel_tanpa_trans('opmt_permohonan', $opmt_permohonan);


            /*ini dikasih pengecakna atas id pengajuan spr itu udah pernah atau belum klo uda pake id itu*/

            if($result != false)
            {
                $id_opmt_permohonan = $lastInsertID;

            }else{
                $gagal='GAGAL INPUT KE OPMT PERMOHONAN - spr!';
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $dd_temp_error[$i]['keterangan_error'] = $gagal;
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;

                $db->RollbackTrans();
                continue;
            }

            //die();
            unset($nasabah);
            $nasabah = array();
            $nasabah['alamat']							= $arrSP2[$i]['alamat_debitur'];
            $nasabah['flag_sync']						= 0;
            $nasabah['ktp_npwp']						= $arrSP2[$i]['no_identitas'];
            $nasabah['nama']							= $arrSP2[$i]['nama_debitur'];
            $nasabah['tgl_lahir']						= $arrSP2[$i]['tanggal_lahir'];
            $nasabah['id_dc_wilayah_kerja']				= $id_dc_wilayah_kerja;
            $nasabah['tgl_status']				        = date('Y-m-d');
            //print_r($nasabah);

            $result = insert_tabel_tanpa_trans('opmt_nasabah', $nasabah);

            if($result != false)
            {
                $id_opmt_nasabah = $lastInsertID;

            }ELSE{
                $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT PERMOHONAN - spr!';
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                continue;
            }

            unset($nasabah_rek);
            $nasabah_rek = array();
            $nasabah_rek['id_opmt_nasabah']				= $id_opmt_nasabah;
            //$nasabah['flag_sync']						= 0;
            $nasabah_rek['no_rekening']					= $arrSP2[$i]['no_rekening'];
            $nasabah_rek['tgl_status']					= date('Y-m-d');
            //print_r($nasabah_rek);
            //die();
            $result = insert_tabel_tanpa_trans('opmt_nasabah_rek', $nasabah_rek);
            if(!$result){

                $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT NASABAH REKENING - spr!';
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                continue;
            }

            // opmt_transaksi_penjaminan
            unset($opmt_transaksi_penjaminan);
            $opmt_transaksi_penjaminan = array();
            $opmt_transaksi_penjaminan['id_opmt_nasabah'] 				= $id_opmt_nasabah;
            $opmt_transaksi_penjaminan['id_opmt_permohonan']			= $id_opmt_permohonan;
            $opmt_transaksi_penjaminan['tgl_perjanjian'] 				= $arrSP2[$i]['tanggal_pk'];
            $opmt_transaksi_penjaminan['pokok_pembiayaan'] 				= $arrSP2[$i]['plafon_kredit'];
            $jenisKur = strtolower($arrSP2[$i]['jenis_kur']);
            $opmt_transaksi_penjaminan['id_dd_jenis_kur'] 				= $jenisKur;
            $opmt_transaksi_penjaminan['tingkat_margin'] 				= 0;
            $opmt_transaksi_penjaminan['jangka_waktu'] 					= $jangka_waktu;
            $opmt_transaksi_penjaminan['waktu_realisasi_pembayaran'] 	= $arrSP2[$i]['tanggal_awal'];
            $opmt_transaksi_penjaminan['tgl_jatuh_tempo'] 	= $arrSP2[$i]['tanggal_akhir'];
            $opmt_transaksi_penjaminan['jumlah_tenaga_kerja'] 			= $jml_t_kerja;

            if (!isset($arrSektor[$kode_sektor])) {
                $obj = read_tabel_tanpa_trans('dc_sektor_lbu', 'id_dc_sektor, id_dc_sektor_lbu', "WHERE lbu_kode = '{$kode_sektor}'");
                $id_dc_sektor 		= $obj->Fields('id_dc_sektor');
                $id_dc_sektor_lbu 	= $obj->Fields('id_dc_sektor_lbu');

                $arrSektor[$kode_sektor] = array(
                    'id_dc_sektor' => $id_dc_sektor,
                    'id_dc_sektor_lbu' => $id_dc_sektor_lbu
                );
            } else {
                $id_dc_sektor 		= $arrSektor[$kode_sektor]['id_dc_sektor'];
                $id_dc_sektor_lbu 	= $arrSektor[$kode_sektor]['id_dc_sektor_lbu'];
            }
            unset($ket_covid);
            $ket_covid ='';
            /*penambahan flag covid*/
            if(is_numeric($flag_covid) && $flag_covid ==1)
            {
                $ket_covid = ' - Terdampak Covid19';
            }

            $opmt_transaksi_penjaminan['id_dc_sektor'] 					= $id_dc_sektor;
            $opmt_transaksi_penjaminan['id_prev'] 						= $id_opmt_transaksi_lama;
            $opmt_transaksi_penjaminan['ket_peruntukan'] 				= $jenis_kredit1.$ket_covid ;
            $keyPeruntukan = strtolower($jenis_kredit);
            $opmt_transaksi_penjaminan['id_dc_peruntukan_kredit'] 		= $keyPeruntukan;
            $opmt_transaksi_penjaminan['no_pk'] 						= $arrSP2[$i]['no_pk'];
            $opmt_transaksi_penjaminan['flag_realisasi'] 				= 0;
            $opmt_transaksi_penjaminan['flag_proses'] 					= 1;
            $opmt_transaksi_penjaminan['flag_transfer'] 				= 0;
            $opmt_transaksi_penjaminan['flag_sync'] 					= 0;
            $opmt_transaksi_penjaminan['flag_sent_email'] 				= 0;
            $opmt_transaksi_penjaminan['tgl_status'] 					= date("Y-m-d H:i:s");
            $opmt_transaksi_penjaminan['id_dc_wilayah_kerja']			= $id_dc_wilayah_kerja;
            $opmt_transaksi_penjaminan['id_pengajuan_spr']				= $id_pengajuan_spr;
            $opmt_transaksi_penjaminan['no_batch']						= $batch;
            $opmt_transaksi_penjaminan['flag_suplesi']					= $suplesi;
            $opmt_transaksi_penjaminan['flag_perpanjangan']				= $perpanjang;

            $opmt_transaksi_penjaminan['flag_penundaan']				= $flag_penundaan;
            $opmt_transaksi_penjaminan['jw_sebelumnya']					= $jw_sebelumnya;
            $opmt_transaksi_penjaminan['jw_terlewati']					= $jw_terlewati;
            $opmt_transaksi_penjaminan['sisa_jw_sebelumnya']			= $sisa_jw_sebelumnya;
            $opmt_transaksi_penjaminan['status_rek_prev']			= $status_rekening;
            $opmt_transaksi_penjaminan['flag_covid']			= $flag_covid;

            //idi ini seharusnya jangan di insert
            //$opmt_transaksi_penjaminan['id_pengajuan_spr']			= $id_calon_debitur_kur;
            //print_r($opmt_transaksi_penjaminan);
            //die();
            $result = insert_tabel_tanpa_trans('opmt_transaksi_penjaminan', $opmt_transaksi_penjaminan);

            if($result != false)
            {
                $id_opmt_transaksi_penjaminan = $lastInsertID;

            }
            else{
                $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT TRANSAKSI PENJAMINAN - spr!';
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                continue;
            }

            //update opmt transaksi penjaminan
            unset($update_opmt_transaksi_penjaminan);
            $update_opmt_transaksi_penjaminan = array();
            if($status_rekening == 1)
                $update_opmt_transaksi_penjaminan['flag_proses']		= 4;
            else
                $update_opmt_transaksi_penjaminan['flag_proses']		= 9;
            //$update_opmt_transaksi_penjaminan['flag_perpanjangan']	= $perpanjang;
            //$update_opmt_transaksi_penjaminan['flag_suplesi']		= $suplesi;
            $result = update_tabel_tanpa_trans('opmt_transaksi_penjaminan', $update_opmt_transaksi_penjaminan , "Where id_opmt_transaksi_penjaminan = {$id_opmt_transaksi_lama}");
            if(!$result){
                $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT TRANSAKSI PENJAMINAN!';
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                continue;
            }

            //pengecekan pengendalian lamanya sudah ada atau belum ada
            if($id_opmt_pengendalian_lama == 'null' or empty($id_opmt_pengendalian_lama)){
                // insert pengendalian data baru
                unset($isi_opmt_pengendalian);
                $isi_opmt_pengendalian = array();
                //$isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_lama;
                $isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_penjaminan;
                $isi_opmt_pengendalian["tanggal_saldo"]=date("Y-m-d");
                $isi_opmt_pengendalian["posisi_piutang"]=$plafon_kredit;
                $isi_opmt_pengendalian["tunggakan_pokok"]=0;
                $isi_opmt_pengendalian["tunggakan_bunga"]=0;
                $isi_opmt_pengendalian["denda"]=0;
                $isi_opmt_pengendalian["flag_pembayaran"]=0;
                $isi_opmt_pengendalian["flag_position"]=1;
                $isi_opmt_pengendalian["flag_transfer"]=0;
                $isi_opmt_pengendalian["tgl_status"]=date("Y-m-d H:i:s");

                $result = insert_tabel_tanpa_trans("opmt_pengendalian",$isi_opmt_pengendalian);

                if(!$result){

                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT pengendalian data spr- SPR!';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    continue;
                }

                // insert pengendalian data lama
                unset($isi_opmt_pengendalian);
                $isi_opmt_pengendalian = array();
                //$isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_lama;
                $isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_lama;
                $isi_opmt_pengendalian["tanggal_saldo"]=date("Y-m-d");
                $isi_opmt_pengendalian["posisi_piutang"]= empty($outstanding) ? 0 : $outstanding ;
                $isi_opmt_pengendalian["tunggakan_pokok"]=0;
                $isi_opmt_pengendalian["tunggakan_bunga"]=0;
                $isi_opmt_pengendalian["denda"]=0;
                $isi_opmt_pengendalian["flag_pembayaran"]=0;
                $isi_opmt_pengendalian["flag_position"]=1;
                $isi_opmt_pengendalian["flag_transfer"]=0;
                $isi_opmt_pengendalian["tgl_status"]=date("Y-m-d H:i:s");

                $result = insert_tabel_tanpa_trans("opmt_pengendalian",$isi_opmt_pengendalian);

                if(!$result){

                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT pengendalian data lama- SPR!';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    continue;
                }

            }
            else{
                unset($update_opmt_pengendalian);

                $update_opmt_pengendalian['flag_position'] = 0;
                $update_opmt_pengendalian['flag_sync'] = 2;

                $result = update_tabel_tanpa_trans('opmt_pengendalian', $update_opmt_pengendalian, "where id_opmt_transaksi_penjaminan = {$id_opmt_transaksi_lama}");

                if(!$result){
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL update KE OPMT Pengendalian - spr!';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    continue;
                }

                // insert lagi opmt_pengendalian baru
                unset($isi_opmt_pengendalian);
                $isi_opmt_pengendalian = array();
                $isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_penjaminan;
                $isi_opmt_pengendalian["tanggal_saldo"]=date("Y-m-d");
                $isi_opmt_pengendalian["posisi_piutang"]=$plafon_kredit;
                $isi_opmt_pengendalian["tunggakan_pokok"]=0;
                $isi_opmt_pengendalian["tunggakan_bunga"]=0;
                $isi_opmt_pengendalian["denda"]=0;
                $isi_opmt_pengendalian["flag_pembayaran"]=0;
                $isi_opmt_pengendalian["flag_position"]=1;
                $isi_opmt_pengendalian["flag_transfer"]=0;
                $isi_opmt_pengendalian["tgl_status"]=date("Y-m-d H:i:s");

                $result = insert_tabel_tanpa_trans("opmt_pengendalian",$isi_opmt_pengendalian);

                if(!$result){

                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT pengendalian data baru spr - SPR!';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    continue;
                }

                // insert pengendalian data lama
                unset($isi_opmt_pengendalian);
                $isi_opmt_pengendalian = array();
                //$isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_lama;
                $isi_opmt_pengendalian["id_opmt_transaksi_penjaminan"]=$id_opmt_transaksi_lama;
                $isi_opmt_pengendalian["tanggal_saldo"]=date("Y-m-d");
                $isi_opmt_pengendalian["posisi_piutang"]= empty($outstanding) ? 0 : $outstanding ;
                $isi_opmt_pengendalian["tunggakan_pokok"]=0;
                $isi_opmt_pengendalian["tunggakan_bunga"]=0;
                $isi_opmt_pengendalian["denda"]=0;
                $isi_opmt_pengendalian["flag_pembayaran"]=0;
                $isi_opmt_pengendalian["flag_position"]=1;
                $isi_opmt_pengendalian["flag_transfer"]=0;
                $isi_opmt_pengendalian["tgl_status"]=date("Y-m-d H:i:s");

                $result = insert_tabel_tanpa_trans("opmt_pengendalian",$isi_opmt_pengendalian);

                if(!$result){

                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INPUT KE OPMT pengendalian data lama- SPR!';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    continue;
                }


            }

            // insert opmt sektor
            unset($opmt_sektor);
            $opmt_sektor = array();
            $opmt_sektor['id_opmt_transaksi_penjaminan'] 	= $id_opmt_transaksi_penjaminan;
            $opmt_sektor['id_dc_sektor_lbu'] 				= $id_dc_sektor_lbu;
            $opmt_sektor['flag_sync'] 						= 0;
            $result = insert_tabel_tanpa_trans('opmt_sektor', $opmt_sektor);
            IF(!$result)
                throw new exception("GAGAL INSERT KE OPMT SEKTOR - spr");

            unset($arrIJP);
            $arrIJP = array();
            $arrIJP['id_opmt_transaksi_penjaminan'] = $id_opmt_transaksi_penjaminan;
            $arrIJP['nilai_ijp'] 	=  $ijpTot[0]['ijp'];
            $arrIJP['flag_lunas'] 					= 1; // Automatis lunas
            $arrIJP['flag_transfer'] 				= 0;
            $arrIJP['tgl_status'] 					= date("Y-m-d H:i:s");
            $arrIJP['id_dc_wilayah_kerja'] 			= $id_dc_wilayah_kerja;
            $arrIJP['flag_sync'] 					= 0;
            $result = insert_tabel_tanpa_trans('opmt_pembayaran_ijp', $arrIJP);
            IF(!$result){

                $dd_temp_error[$i]['keterangan_error'] = "GAGAL INPUT KE OPMT PEMBAYARAN IJP -spr!";
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                continue;
                //throw new exception("GAGAL INSERT KE OPMT PEMBAYARAN IJP");
            }

            //vania 9 oktober 2018 cobain bikin generate angsuran ijp saat transaksi -- klo gagal di comment aja dulu  tapi kabari ya, terus bikin data yang agak banyak ya, biar bisa dites
            // ----------- dari sini --------------------
            $id_opmt_pembayaran_ijp = $lastInsertID;
            if(($jangka_waktu <> 0)&&($arrSP2[$i]['plafon_kredit'] <> 0)){
                $result= generate_angsuran($jangka_waktu,$id_dd_bank,$plafon_kredit,$arrSP2[$i]['tanggal_pk'],$arrSP2[$i]['jenis_kur'],
                    $id_opmt_pembayaran_ijp,$tgl_sertifikat_baru,$ijpTot[0]['ijp'],$id_calon_debitur_kur,$id_pengajuan_spr,2);
                IF(!$result){
                }
            }

            unset($arrTransaksiSPR);
            $arrTransaksiSPR = array();
            //$arrTransaksiSPR['id_pengajuan_spr']					= $id_pengajuan_spr;
            $arrTransaksiSPR['id_opmt_sertifikat_baru']				= $id_opmt_sertifikat;
            $arrTransaksiSPR['id_opmt_transaksi_baru']				= $id_opmt_transaksi_penjaminan;

            var_dump($arrTransaksiSPR);

            // ini di aplikasi depan harus sudah update id_pengajuan_spr di	tbl_transaksi_spr_count
            //$hasilnya = update_tabel_tanpa_trans('tbl_transaksi_spr_count', $arrTransaksiSPR , "WHERE no_aplikasi = {$nomor_aplikasi}");
            $hasilnya = update_tabel_tanpa_trans('tbl_transaksi_spr_count', $arrTransaksiSPR , "WHERE id_pengajuan_spr = {$id_pengajuan_spr}");
            if(!$hasilnya){
                $dd_temp_error[$i]['keterangan_error'] = 'GAGAL update tbl_transaksi_spr_count!';
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                $db->RollbackTrans();
                continue;
            }

            $tanggal_akhir_tambah= date('Y-m-d', strtotime("+$jangka_waktu months", strtotime($arrSP2[$i]['tanggal_pk'])));
            if(
                // sebelum diupdate 23102020
                (($arrSP2[$i]['jenis_kur'] == 2)&&(($arrSP2[$i]['plafon_kredit']<25000000) || ($arrSP2[$i]['plafon_kredit']>500000000))) ||
                (($arrSP2[$i]['jenis_kur'] == 1) && ($arrSP2[$i]['plafon_kredit'] > 25000000)) || ($arrSP2[$i]['plafon_kredit'] <= 0) ||

                // update 23102020
                //(($arrSP2[$i]['jenis_kur'] == 2)&&(($arrSP2[$i]['plafon_kredit']<25000000) || ($arrSP2[$i]['plafon_kredit']>500000000))&&($arrSP2[$i]['tanggal_pk'] < '2020-01-02')) ||
                //(($arrSP2[$i]['jenis_kur'] == 1) && ($arrSP2[$i]['plafon_kredit'] > 25000000)&&($arrSP2[$i]['tanggal_pk'] < '2020-01-02')) ||

                //(($arrSP2[$i]['jenis_kur'] == 2)&&(($arrSP2[$i]['plafon_kredit']<50000000) || ($arrSP2[$i]['plafon_kredit']>500000000))&&($arrSP2[$i]['tanggal_pk'] >= '2020-01-02')) ||
                //(($arrSP2[$i]['jenis_kur'] == 1) && ($arrSP2[$i]['plafon_kredit'] > 50000000)&&($arrSP2[$i]['tanggal_pk'] >= '2020-01-02')) ||
                // end update 23102020

                ($arrSP2[$i]['plafon_kredit']=='') || (($arrSP2[$i]['jenis_kredit']==1)&&($arrSP2[$i]['jenis_kur']==1)&&($jangka_waktu > 36)) ||
                (($arrSP2[$i]['jenis_kredit']==2)&&($arrSP2[$i]['jenis_kur']==1)&&($jangka_waktu > 60)) ||
                (($arrSP2[$i]['jenis_kredit']==1)&&($arrSP2[$i]['jenis_kur']==2)&&($jangka_waktu > 48)) ||
                (($jenis_kredit==2)&&($arrSP2[$i]['jenis_kur']==2)&&($jangka_waktu > 60)) ||
                ((($arrSP2[$i]['tanggal_pk'] <= '2015-12-31')&&($cov <= 0)) || (($arrSP2[$i]['tanggal_pk'] > '2015-12-31')&&($cov_permen <= 0))) ||
                // tino edit 17072019 berdasarkan memo B.36/INT/PST/PST/VII/2019
                // 1. Tanggal Akhir SP < Tanggal Sertifikat
                ($arrSP2[$i]['tanggal_akhir'] < $tgl_sertifikat_baru) ||
                // 2. (Tanggal PK + Jangka waktu) < Tanggal Sertifikat
                ($tanggal_akhir_tambah < $tgl_sertifikat_baru) ||
                // 3. (Tanggal PK > Tanggal Sertifikat
                ($arrSP2[$i]['tanggal_pk'] > $tgl_sertifikat_baru) ||
                // 4. Permenko 8 - Plafon Kredit
                (($arrSP2[$i]['tanggal_pk'] > '2018-10-30')&&($arrSP2[$i]['jenis_kur'] == 5) && ($arrSP2[$i]['plafon_kredit'] > 500000000)) ||
                // 5. Permenko 8 - Coverage Per Sektor
                (($arrSP2[$i]['tanggal_pk'] > '2018-10-30')&&($cov_permen8 <= 0)&&(in_array($arrSP2[$i]['jenis_kur'], array(1,2,4)))) ||
                // 6. Permenko 8 - Coverage Per Sektor Khusus
                (($arrSP2[$i]['tanggal_pk'] > '2018-10-30')&&($cov_permen8_khusus <= 0)&&($arrSP2[$i]['jenis_kur']==5))

            )
            {
                unset($opmt_penjaminan_bri);
                $opmt_penjaminan_bri = array();
                echo 'Konfirmasi KUR<br>ID Calon Debitur KUR : '.$arrSP2[$i]['id_calon_debitur_kur'].'<br>ID Pengajuan SPR : '.$arrSP2[$i]['id_pengajuan_spr'].'<br>Tgl Akhir Tambah : '.$tanggal_akhir_tambah.'<br>Tgl SP : '.$tgl_sertifikat_baru.'<br>Tgl Akhir SP : '.$arrSP2[$i]['tanggal_akhir'].'<br>tanggal PK : '.$arrSP2[$i]['tanggal_pk'].'<br>Jenis KUR : '.$arrSP2[$i]['jenis_kur'].'<br>Plafon : '.$arrSP2[$i]['plafon_kredit'].'<br>Jenis Kredit : '.$arrSP2[$i]['jenis_kredit'].'<br>JW : '.$jangka_waktu.'<br>Cov 8 : '.$cov_permen8.'<br>Cover 8 Khusus :'.$cov_permen8_khusus;

                $opmt_penjaminan_bri['j_flag_persetujuan_spr']	= 90;
                $opmt_penjaminan_bri['j_proses_ip']	= 1;
                $result = update_tabel_tanpa_trans('pengajuan_spr', $opmt_penjaminan_bri , "Where id_pengajuan_spr = {$id_pengajuan_spr}");


                $dd_temp_error[$i]['keterangan_error'] = 'Konfirmasi KUR<br>ID Calon Debitur KUR : '.$arrSP2[$i]['id_calon_debitur_kur'].'<br>ID Pengajuan SPR : '.$arrSP2[$i]['id_pengajuan_spr'].'<br>Tgl Akhir Tambah : '.$tanggal_akhir_tambah.'<br>Tgl SP : '.$tgl_sertifikat_baru.'<br>Tgl Akhir SP : '.$arrSP2[$i]['tanggal_akhir'].'<br>tanggal PK : '.$arrSP2[$i]['tanggal_pk'].'<br>Jenis KUR : '.$arrSP2[$i]['jenis_kur'].'<br>Plafon : '.$arrSP2[$i]['plafon_kredit'].'<br>Jenis Kredit : '.$arrSP2[$i]['jenis_kredit'].'<br>JW : '.$jangka_waktu.'<br>Cov 8 : '.$cov_permen8.'<br>Cover 8 Khusus :'.$cov_permen8_khusus;
                $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;


                IF(!$result){
                    //throw new exception("GAGAL UPDATE KE CALON DEBITUR KUR");
                    //$dd_temp_error = array();
                    //unset($dd_temp_error);
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL UPDATE KE PENGAJUAN SPR';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                    $db->RollbackTrans();
                    continue;
                }

            }
            else{
                unset($opmt_penjaminan_bri);
                $opmt_penjaminan_bri = array();
                echo 'Tidak Terkena Validasi KUR <br>ID Calon Debitur KUR : '.$arrSP2[$i]['id_calon_debitur_kur'].'<br>ID Pengajuan SPR : '.$arrSP2[$i]['id_pengajuan_spr'].'<br>Tgl Akhir Tambah : '.$tanggal_akhir_tambah.'<br>Tgl SP : '.$tgl_sertifikat_baru.'<br>Tgl Akhir SP : '.$arrSP2[$i]['tanggal_akhir'].'<br>tanggal PK : '.$arrSP2[$i]['tanggal_pk'].'<br>Jenis KUR : '.$arrSP2[$i]['jenis_kur'].'<br>Plafon : '.$arrSP2[$i]['plafon_kredit'].'<br>Jenis Kredit : '.$arrSP2[$i]['jenis_kredit'].'<br>JW : '.$jangka_waktu.'<br>Kode Sektor '.$kode_sektor.' <br>Cov 8 : '.$cov_permen8.'<br>Cover 8 Khusus :'.$cov_permen8_khusus;
                $opmt_penjaminan_bri['j_flag_persetujuan_spr']	= 2;
                $opmt_penjaminan_bri['j_proses_ip']	= 1;
                $result = update_tabel_tanpa_trans('pengajuan_spr', $opmt_penjaminan_bri , "Where id_pengajuan_spr = {$id_pengajuan_spr}");
                IF(!$result){
                    //throw new exception("GAGAL UPDATE KE CALON DEBITUR KUR");
                    //$dd_temp_error = array();
                    //unset($dd_temp_error);
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL UPDATE KE PENGAJUAN SPR';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    $db->RollbackTrans();
                    //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                    continue;
                }
            }

            //untuk bank ocbc nisp
            if ($id_dd_bank == 179){
                $dataforws = array();
                $dataforws['nomor_aplikasi']=$nomor_aplikasi;
                $dataforws['nomor_rekening']=$arrSP2[$i]['no_rekening'];
                $dataforws['nama_debitur']=$arrSP2[$i]['nama_debitur'];
                $dataforws['nomor_sertifikat']=$nomor_sertifikat;
                $dataforws['tanggal_sertifikat']=$tanggalSertifikat;
                $dataforws['ijp'] = $ijpTot[0]['ijp'];
                $dataforws['coverage']=$coverage;
                $dataforws['flag_send']=0;
                $dataforws['date_created']=date("Y-m-d");
                $dataforws['is_koreksi']=$opmt_penjaminan_bri['j_flag_persetujuan'];
                if((($arrSP2[$i]['jenis_kur'] == 2)&&(($arrSP2[$i]['plafon_kredit']<25000000)|| ($arrSP2[$i]['plafon_kredit']>500000000))) || (($arrSP2[$i]['jenis_kur'] == 1) && ($arrSP2[$i]['plafon_kredit'] > 25000000)) || ($arrSP2[$i]['plafon_kredit'] <= 0) || ($arrSP2[$i]['plafon_kredit']==''))
                    $dataforws['ket_koreksi']= 'Plafon Tidak Sesuai';
                else if ((($arrSP2[$i]['jenis_kredit']==1)&&($arrSP2[$i]['jenis_kur']==1)&&($jangka_waktu > 36)) || (($arrSP2[$i]['jenis_kredit']==2)&&($arrSP2[$i]['jenis_kur']==1)&&($jangka_waktu > 60)) || (($arrSP2[$i]['jenis_kredit']==1)&&($arrSP2[$i]['jenis_kur']==2)&&($jangka_waktu > 48)) || (($jenis_kredit==2)&&($arrSP2[$i]['jenis_kur']==2)&&($jangka_waktu > 60)))
                    $dataforws['ket_koreksi']= 'Jangka Waktu Tidak Sesuai';
                else if	(($suku_bunga > 12) || (($suku_bunga <= 0) && ($id_dd_bank != 11)) || (($suku_bunga == '')&& ($id_dd_bank != 11)))
                    $dataforws['ket_koreksi']= 'Suku Bunga Tidak Sesuai';
                else if ($cov <= 0)
                    $dataforws['ket_koreksi']= 'Coverage Tidak Sesuai';
                //print_r($dataforws);die();
                $result = insert_tabel_tanpa_trans('data_for_send_ocbc_nisp', $dataforws);
                IF(!$result){
                    //throw new exception("GAGAL INSERT KE data_for_send_ws_mandiri");
                    //$dd_temp_error = array();
                    //unset($dd_temp_error);
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL INSERT KE ocbc_nisp';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                    $db->RollbackTrans();
                    continue;
                }
            }
            //untuk bank Mandiri
            else if ($id_dd_bank == 9){
                $dataforws = array();
                $dataforws['nomor_aplikasi']=$nomor_aplikasi;
                $dataforws['nomor_sertifikat']=$nomor_sertifikat;
                $dataforws['no_urut_lampiran']=$no_lampiran+1;
                $dataforws['tanggal_sertifikat']=$tanggalSertifikat;
                $dataforws['ijp'] = $ijpTot[0]['ijp'];
                $dataforws['coverage']=$coverage;
                $dataforws['flag_ws']=0;
                $dataforws['date_created']=date("Y-m-d");
                //print_r($dataforws);die();
                $result = insert_tabel_tanpa_trans('data_for_send_ws_mandiri', $dataforws);
                IF(!$result){
                    //throw new exception("GAGAL INSERT KE data_for_send_ws_mandiri");
                    //$dd_temp_error = array();
                    //unset($dd_temp_error);
                    $dd_temp_error[$i]['keterangan_error'] = 'GAGAL UPDATE KE CALON DEBITUR KUR';
                    $dd_temp_error[$i]['id_calon_debitur_kur'] = $id_calon_debitur_kur;
                    $dd_temp_error[$i]['id_pengajuan_spr'] = $id_pengajuan_spr;
                    //$result = insert_tabel_tanpa_trans('dd_temp_error', $dd_temp_error);
                    $db->RollbackTrans();
                    continue;
                }
            }
            //die();
            $db->CommitTrans($result != false);

        } catch (Exception $e) {
            $result = false;
            $message = $e->getMessage();
            print_r($message);
            continue;

        }
        echo "<br/>===============================================================================<br/>";
    }
    //die();
    //$db->debug=true;
    foreach($dd_temp_error as $key => $error){
        //$error['keterangan_error']=
        //PRINT_R($error);
        $db->BeginTrans();
        $result = insert_tabel_tanpa_trans('dd_temp_error', $error);
        //print_r($result);
        //die();
        $db->CommitTrans($result != false);
    }
    unlink($nama_file);
// else kalau sp2_kur belum ada ?

}else{
    $result == false;
    //die();
    unlink($nama_file);
}


?>





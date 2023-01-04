<?php
require_once("../_lib/db.php");
//require_once("../_lib/conn_kur.php");

loadlib("class","paging");
loadlib("function","paging_bawah");
loadlib("function","button");
loadlib("function","olah_tabel");
loadlib("function","date2str");
loadlib("function","uang");
loadlib("function","input_uang");
loadlib("function","submit_uang");
loadlib("function", "form_tgl_new");
require_once("../_lib/configs/penjaminan.php");
//$db->debug = true;

$host = 'http://'.$_SERVER['HTTP_HOST'];

try
{
    // INIT
    //$arrBank = select_tabel("dd_bank","id_dd_bank,nama_bank"," WHERE jenis_lembaga = 1 ORDER BY nama_bank");

    // GET DATA KLAIM & Subrogasi
    // GET DATA KLAIM & SUBROGASI
    //print_r($table_id);
    /*if($table_id == 'spr') // berarti pengajuan spr
    {
        $sqlIDCalon  ="
        select id_calon_debitur_kur
        from pengajuan_spr
        where id_pengajuan_spr ={$id_calon_debitur_kur}
        ";
        //print_r($sqlIDCalon);
        $rowIDCalon = $conn_kur->GetRow($sqlIDCalon);
        //print_r($rowIDCalon);
        $id_calon_debitur_kur		= $rowIDCalon["id_calon_debitur_kur"];
        //print_r($id_calon_debitur_kur);
    }
    print_r($id_calon_debitur_kur);die();
    */
    //print_r($table_id);
    if($table_id=='baru'){
        $sql = "SELECT
			a.id_calon_debitur_kur
			,a.id_dd_bank
			,nama_debitur
			,a.kode_uker
			,d.nama_bank_cabang
			,a.tanggal_rekam
			,a.nomor_aplikasi
			,b.no_rekening
			,a.plafon_kredit
			,f.nomor_sk
			,f.tgl_sk
			,a.j_flag_persetujuan
			,a.j_transfer
			,g.total_ijp
			,a.jangka_waktu
			,j.id_opmt_pembayaran_ijp
			, nama_bank
			,b.no_pk
			,b.tanggal_pk,
			jns.jenis_kur,
			dpk.nama_peruntukan
		FROM calon_debitur_kur a WITH (NOLOCK)

		INNER JOIN sp2_kur b WITH (NOLOCK) ON a.nomor_aplikasi = b.nomor_aplikasi and a.id_dd_bank = b.id_dd_bank
		--LEFT JOIN sertifikat_kur c ON b.no_rekening = c.no_rekening
		inner join dc_wilayah_kerja h WITH (NOLOCK) on h.ko_wil = a.cabang_rekanan
	--	INNER JOIN dd_bank_cabang d ON a.kode_uker = d.kode_uker AND a.id_dd_bank = d.id_dd_bank

		INNER JOIN opmt_transaksi_penjaminan k WITH (NOLOCK) on k.id_calon_debitur_kur = a.id_calon_debitur_kur
		INNER join opmt_pembayaran_ijp j WITH (NOLOCK) on j.id_opmt_transaksi_penjaminan =k.id_opmt_transaksi_penjaminan
		INNER JOIN opmt_permohonan g WITH (NOLOCK) ON k.id_opmt_permohonan = g.id_opmt_permohonan
		INNER JOIN opmt_sertifikat f WITH (NOLOCK) ON f.id_opmt_sertifikat = g.id_opmt_sertifikat

		INNER JOIN dd_bank_cabang d WITH (NOLOCK) on d.id_dd_bank_cabang = g.id_dd_bank_cabang
		INNER JOIN dd_bank e WITH (NOLOCK) ON d.id_dd_bank = e.id_dd_bank
		inner join dd_jenis_kur jns with (nolock) on jns.id_dd_jenis_kur = a.jenis_kur
		inner join dc_peruntukan_kredit dpk with (nolock) on dpk.id_dc_peruntukan_kredit = k.id_dc_peruntukan_kredit
			where	a.id_calon_debitur_kur = {$id_calon_debitur_kur} --and a.id_dd_bank = {$id_dd_bank}
			order by nama_debitur desc
            ";
    }
    else if($table_id=='spr'){
        $sql = "SELECT
			a.id_calon_debitur_kur
			,a.id_dd_bank
			,a.nama_debitur
			,a.kode_uker
			,d.nama_bank_cabang
			,a.tanggal_rekam
			,a.nomor_aplikasi
			,b.no_rekening
			,a.plafon_kredit
			,f.nomor_sk
			,f.tgl_sk
			,a.j_flag_persetujuan
			,a.j_transfer
			,g.total_ijp
			,a.jangka_waktu
			,j.id_opmt_pembayaran_ijp
			, nama_bank
			,b.no_pk
			,b.tanggal_pk,
			jns.jenis_kur,
			dpk.nama_peruntukan
		FROM pengajuan_spr spr
		inner join calon_debitur_kur a WITH (NOLOCK) on spr.id_calon_debitur_kur = a.id_calon_debitur_kur

		INNER JOIN sp2_kur b WITH (NOLOCK) ON a.nomor_aplikasi = b.nomor_aplikasi and a.id_dd_bank = b.id_dd_bank
		--LEFT JOIN sertifikat_kur c ON b.no_rekening = c.no_rekening
		inner join dc_wilayah_kerja h WITH (NOLOCK) on h.ko_wil = a.cabang_rekanan
	--	INNER JOIN dd_bank_cabang d ON a.kode_uker = d.kode_uker AND a.id_dd_bank = d.id_dd_bank

		INNER JOIN opmt_transaksi_penjaminan k WITH (NOLOCK) on k.id_calon_debitur_kur = a.id_calon_debitur_kur
		INNER join opmt_pembayaran_ijp j WITH (NOLOCK) on j.id_opmt_transaksi_penjaminan =k.id_opmt_transaksi_penjaminan
		INNER JOIN opmt_permohonan g WITH (NOLOCK) ON k.id_opmt_permohonan = g.id_opmt_permohonan
		INNER JOIN opmt_sertifikat f WITH (NOLOCK) ON f.id_opmt_sertifikat = g.id_opmt_sertifikat

		INNER JOIN dd_bank_cabang d WITH (NOLOCK) on d.id_dd_bank_cabang = g.id_dd_bank_cabang
		INNER JOIN dd_bank e WITH (NOLOCK) ON d.id_dd_bank = e.id_dd_bank
		inner join dd_jenis_kur jns with (nolock) on jns.id_dd_jenis_kur = a.jenis_kur
		inner join dc_peruntukan_kredit dpk with (nolock) on dpk.id_dc_peruntukan_kredit = k.id_dc_peruntukan_kredit
			where	spr.id_pengajuan_spr = {$id_calon_debitur_kur} --and a.id_dd_bank = {$id_dd_bank}
			order by nama_debitur desc
            ";
    }
    //	print_r("<pre>".$sql);
    $row = $db->GetRow($sql);

    // DETAIL
    $nama_bank			= $row["nama_bank"];
    $kode_uker			= $row["kode_uker"];
    $nama_bank_cabang	= $row["nama_bank_cabang"];
    $nama_debitur		= $row["nama_debitur"];
    $jenis_kur			= $row["jenis_kur"];
    $nama_peruntukan	= $row["nama_peruntukan"];
    $cabang_rekanan		= $row["cabang_rekanan"];
    $id_calon_debitur_kur2 = $row["id_calon_debitur_kur"];

    // GET DATA ANGSURAN SUBROGASI
    $sqlDetail = "
			select cdk.*,a.id_opmt_transaksi_penjaminan, ijp.nilai_ijp,coalesce(ijpdetail.ijp_bayar,0) as ijp_bayar ,
 ijp.nilai_ijp -coalesce(ijpdetail.ijp_bayar,0) as selisih,
( case when a.id_opmt_transaksi_penjaminan is null then 'Belum Terbentuk Transaksi' else 'Telah Terbentuk Transaksi' end ) as status_pengajuan,
( case when a.id_opmt_transaksi_penjaminan is null then 0 else 1 end ) as status_trans
  from
(
						SELECT
						a.id_calon_debitur_kur as table_id,
						a.id_calon_debitur_kur,
						a.plafon_kredit,
						a.jangka_waktu,
						a.nomor_aplikasi,
						b.no_rekening,
						c.no_sertifikat,
						'baru' as jenis_pengajuan,
						0 as jns
					FROM calon_debitur_kur a  WITH(nolock)
					join sp2_kur b  WITH(nolock) on a.nomor_aplikasi = b.nomor_aplikasi and a.id_dd_bank = b.id_dd_bank
					join sertifikat_kur c on c.no_rekening = b.no_rekening and c.id_dd_bank = b.id_dd_bank
					where a.id_calon_debitur_kur = {$id_calon_debitur_kur2}
					union
					SELECT
						id_pengajuan_spr as table_id,
						id_calon_debitur_kur,
						plafon_kredit,
						jangka_waktu,
						nomor_aplikasi,
						no_rekening_baru,
						no_sertifikat,
						concat('spr - ', (case
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=0  then 'perpanjangan'
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=0  then 'suplesi'
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=1  then 'restrukrisasi'
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=0  then 'perpanjangan - suplesi'
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=1  then 'perpanjangan - restruk'
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=1  then 'suplesi - restruk'
							else 'perpanjangan - suplesi - restrukrisasi'
						 end
						) )
						 as jenis_pengajuan ,
						 (case
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=0  then 1
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=0  then 2
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=1  then 3
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=0  then 4
							when coalesce(flag_p,0)=1 and coalesce(flag_s,0)=0 and coalesce(flag_r,0)=1  then 5
							when coalesce(flag_p,0)=0 and coalesce(flag_s,0)=1 and coalesce(flag_r,0)=1  then 6
							else 7
						 end
						) as jns
					FROM pengajuan_spr WITH(nolock)
					where id_calon_debitur_kur = {$id_calon_debitur_kur2}
				)cdk
left join opmt_transaksi_penjaminan a WITH(NOLOCK) on  --cdk.id_calon_debitur_kur  = a.id_calon_debitur_kur
		(case when cdk.jns =0 then  a.id_calon_debitur_kur
 else a.id_pengajuan_spr end )= cdk.table_id
		left join opmt_pembayaran_ijp ijp on ijp.id_opmt_transaksi_penjaminan = a.id_opmt_transaksi_penjaminan
		left join (select id_opmt_pembayaran_ijp , sum(ijp_bayar) as ijp_bayar from opmt_pembayaran_ijp_detail  with(nolock)
		WHERE flag_delete IS NULL
		group by id_opmt_pembayaran_ijp) ijpdetail on ijpdetail.id_opmt_pembayaran_ijp = ijp.id_opmt_pembayaran_ijp
--		left join opmt_permohonan b WITH(NOLOCK) on b.id_opmt_permohonan = a.id_opmt_permohonan
--		left join opmt_sertifikat c WITH(NOLOCK) on c.id_opmt_sertifikat = b.id_opmt_sertifikat
--		left join dd_bank_cabang f WITH(NOLOCK) on f.id_dd_bank_cabang = b.id_dd_bank_cabang
--		left join dd_bank g WITH(NOLOCK) on g.id_dd_bank = f.id_dd_bank
--		left join dc_wilayah_kerja h WITH(NOLOCK) on a.id_dc_wilayah_kerja = h.id_dc_wilayah_kerja

			";
    $recperpages=5;

    $sqlCombineBaruAndSpr = "
    SELECT * FROM (
                        SELECT
                        a.id_calon_debitur_kur as table_id,
                        a.flag_lunas_all,
						a.id_calon_debitur_kur,
						a.plafon_kredit,
						a.jangka_waktu,
						a.nomor_aplikasi,
						b.no_rekening,
						c.no_sertifikat,
						'baru' as jenis_pengajuan,
						0 as jns,
						d.id_opmt_transaksi_penjaminan,
						ijp.nilai_ijp,
						coalesce(ijpdetail.ijp_bayar,0) as ijp_bayar,
						coalesce(ori.nilai_refund_persetujuan,0) as nilai_refund,
						(CASE WHEN ori.nilai_refund_persetujuan IS NOT NULL THEN 1 ELSE 0 END) as status_refund,
						floor(coalesce(ijp.nilai_ijp,0) - (coalesce(ijpdetail.ijp_bayar,0) + coalesce(ori.nilai_refund_persetujuan,0))) as selisih,
						(case
							when d.id_opmt_transaksi_penjaminan is null then 'Belum Terbentuk Transaksi' else 'Telah Terbentuk Transaksi'
						end) as status_pengajuan,
						(case when d.id_opmt_transaksi_penjaminan is null then 0 else 1 end ) as status_trans
                   FROM calon_debitur_kur a
                    JOIN sp2_kur b  WITH(nolock) on a.nomor_aplikasi = b.nomor_aplikasi and a.id_dd_bank = b.id_dd_bank
					JOIN sertifikat_kur c WITH(nolock) on c.no_rekening = b.no_rekening and c.id_dd_bank = b.id_dd_bank
					LEFT JOIN opmt_transaksi_penjaminan d WITH(NOLOCK) ON a.id_calon_debitur_kur = d.id_calon_debitur_kur
					LEFT JOIN opmt_pembayaran_ijp ijp WITH(nolock) on ijp.id_opmt_transaksi_penjaminan = d.id_opmt_transaksi_penjaminan
					LEFT JOIN
						(SELECT
							id_opmt_pembayaran_ijp
							,sum(ijp_bayar) as ijp_bayar
						FROM opmt_pembayaran_ijp_detail  with(nolock)
						WHERE flag_delete IS NULL
						GROUP BY
							id_opmt_pembayaran_ijp
						) ijpdetail on ijpdetail.id_opmt_pembayaran_ijp = ijp.id_opmt_pembayaran_ijp
					    LEFT JOIN opmt_refund_ijp ori on ijp.id_opmt_transaksi_penjaminan = ori.id_opmt_transaksi_penjaminan

                   UNION ALL

                   SELECT
				a.id_pengajuan_spr as table_id,
                cdk.flag_lunas_all,
				a.id_calon_debitur_kur,
				a.plafon_kredit,
				a.jangka_waktu,
				a.nomor_aplikasi,
				a.no_rekening_baru as no_rekening,
				a.no_sertifikat,
				concat('spr - ', (case
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=0  then 'perpanjangan'
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=0  then 'suplesi'
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=1  then 'restrukrisasi'
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=0  then 'perpanjangan - suplesi'
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=1  then 'perpanjangan - restruk'
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=1  then 'suplesi - restruk'
					else 'perpanjangan - suplesi - restrukrisasi'
				 end
				) )
				 as jenis_pengajuan ,
				 (case
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=0  then 1
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=0  then 2
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=1  then 3
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=0  then 4
					when coalesce(a.flag_p,0)=1 and coalesce(a.flag_s,0)=0 and coalesce(a.flag_r,0)=1  then 5
					when coalesce(a.flag_p,0)=0 and coalesce(a.flag_s,0)=1 and coalesce(a.flag_r,0)=1  then 6
					else 7
				 end
				) as jns,
				b.id_opmt_transaksi_penjaminan,
				ijp.nilai_ijp,
				coalesce(ijpdetail.ijp_bayar,0) as ijp_bayar,
				coalesce(ori_spr.nilai_refund_persetujuan,0) as nilai_refund,
                (CASE WHEN ori_spr.nilai_refund_persetujuan IS NOT NULL THEN 1 ELSE 0 END) as status_refund,
				floor(coalesce(ijp.nilai_ijp,0) - (coalesce(ijpdetail.ijp_bayar,0) + coalesce(ori_spr.nilai_refund_persetujuan,0))) as selisih,
				(case
					when b.id_opmt_transaksi_penjaminan is null then 'Belum Terbentuk Transaksi' else 'Telah Terbentuk Transaksi'
				end) as status_pengajuan,
				(case when b.id_opmt_transaksi_penjaminan is null then 0 else 1 end ) as status_trans
			FROM pengajuan_spr a WITH(nolock)
			INNER JOIN calon_debitur_kur cdk ON cdk.id_calon_debitur_kur = a.id_calon_debitur_kur
			LEFT JOIN opmt_transaksi_penjaminan b WITH(NOLOCK) ON a.id_pengajuan_spr = b.id_pengajuan_spr
			LEFT JOIN opmt_pembayaran_ijp ijp WITH(nolock) on ijp.id_opmt_transaksi_penjaminan = b.id_opmt_transaksi_penjaminan
			LEFT JOIN
				(SELECT
					id_opmt_pembayaran_ijp
					,sum(ijp_bayar) as ijp_bayar
				FROM opmt_pembayaran_ijp_detail  with(nolock)
				WHERE flag_delete IS NULL
				GROUP BY
					id_opmt_pembayaran_ijp
				) ijpdetail on ijpdetail.id_opmt_pembayaran_ijp = ijp.id_opmt_pembayaran_ijp
			LEFT JOIN opmt_refund_ijp ori_spr on ijp.id_opmt_transaksi_penjaminan = ori_spr.id_opmt_transaksi_penjaminan
                        ) com
WHERE com.id_calon_debitur_kur = '{$id_calon_debitur_kur2}' ORDER BY com.id_opmt_transaksi_penjaminan
    ";

//    var_dump("<pre>.$sqlCombineBaruAndSpr");die;

    $arrData = $db->GetAll($sqlCombineBaruAndSpr);

}catch(exception $e){
    $result = false;
    $msg = $e->getMessage();
}
?>

<html>
<head>
    <title>PEMBAYARAN IJP</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <link href="/_komponen/css/main.css" rel="stylesheet" type="text/css">
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/pngfix.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/x_core.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/x_event.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/x_misc.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/x_dom.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/main.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/kalender.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/retrieveData.js"></script>
    <script language="JavaScript" type="text/JavaScript" src="/_komponen/js/jquery.js"></script>
    <link href="/_komponen/css/toastr.min.css" rel="stylesheet" type="text/css">
</head>
<body scroll="no" style='overflow:hidden !important;'>
<div id="barJudul">PEMBAYARAN IJP</div>


<div id="isiAtas">
    <form id="averin_form" method="post">
        <div id="isiInput" >
            <table id="isiInputPokok" cellpadding="1" cellspacing="1" width="100%">
                <tbody>
                <tr>
                    <td class="fieldIsi" style='width:15%;'>Nama Bank</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $nama_bank; ?></b></td>
                </tr>
                <tr>
                    <td class="fieldIsi">Kode Uker</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $kode_uker; ?></b></td>
                </tr>
                <tr>
                    <td class="fieldIsi">Nama Unit Kerja</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $nama_bank_cabang ?></b></td>
                </tr>
                <tr>
                    <td class="fieldIsi">Nama Debitur</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $nama_debitur; ?></b></td>
                </tr>
                <tr>
                    <td class="fieldIsi">Jenis Kredit</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $nama_peruntukan ?></b></td>
                </tr>
                <tr>
                    <td class="fieldIsi">Jenis KUR</td>
                    <td>&nbsp;</td>
                    <td class="inputIsi"><b><?php echo $jenis_kur?></b></td>
                </tr>
                </tbody>
            </table>
            <!--<input type="hidden" name="id_opmt_klaim_detail" value="<?= $id_opmt_klaim_detail ?>"/>
			<input type="hidden" name="id_dc_produk" value="<?= $id_dc_produk ?>"/>
			<input type="hidden" name="id_dc_wilayah_kerja" value="<?= $id_dc_wilayah_kerja ?>"/>
			<input type="hidden" name="no_rekening" value="<?= $no_rekening ?>"/>-->

        </div>


</div>
<!--<div id="actBar">
    <div id="ButtonNonCetak">
        <!-- DISEMBUNYIIN DULU -->
<!--<a class="actLink" href="javascript:checkField()"><img src="../_images/icon/ico_actsimpan.png" border="0" alt="" align="absmiddle">&nbsp;</a>
            <!-- SEMBUNYI -->
<?php //button_cetak("subrogasiProsesCetak.php?tgl_surat_spu={date2str(urldecode($tgl_surat_spu))}&jumlah_piutang={$jumlah_piutang}&nama_bank_cabang={$nama_bank_cabang}&id_dc_produk={$id_dc_produk}&no_rekening={$no_rekening}&id_opmt_klaim_detail={$id_opmt_klaim_detail}&nama={$nama}&nomor_sk={$nomor_sk}"); ?>
<!--</div>
</div> -->
<div id="isi">
    <table id="tbl" width="100%" border="0" cellpadding="3" cellspacing="0" >
        <thead>
        <tr>
            <th class="thno">No.</th>
            <th width="50"><B>Detail IJP</B></th>
            <th width="100"><B>No Rekening</B></th>
            <th width="350"><B>No. SP</B></th>
            <!--th width="150"><B>Kode Voucher</B></th -->
            <th width="150"><B>Plafon</B></th>
            <th width="150"><B>Jangka Waktu</B></th>
            <th width="150"><B>Jenis Pengajuan</B></th>
            <th width="150"><B>Status Pengajuan</B></th>
            <th width="150"><B>Total IJP</B></th>
            <th width="150"><B>IJP Seharusnya</B></th>
            <th width="150"><B>IJP Bayar</B></th>
            <th width="150"><B>IJP Refund</B></th>
            <th width="150"><B>Selesih IJP</B></th>
            <th width="150"><B>Button Refund</B></th>
        </tr>
        </thead>
        <tbody id="tblBody">
        <?php
        $i = 1;
        $totalIJPBayar = 0;
        $totalIJP = 0;
        $totalSelsisih = 0;
        //die('vania');
        //while ($arrRow=$hasil_paging->FetchRow()) {
        foreach($arrData as $key => $arrRow){
            $totalIJPBayar = $totalIJPBayar+ $arrRow['ijp_bayar'];
            $totalIJP = $totalIJPBayar+ $arrRow['nilai_ijp'];
            $totalSelsisih = $totalIJPBayar+ $arrRow['selisih'];
            ?>
            <tr bgcolor="#EFEFF7">
                <td align="right" ><?= $i++ ?>.</td>
                <td align="center">
                    <?php
                    if ($arrRow['status_trans'] == 1) {
                        ?>
                        <a href="#" onclick="openPop('DetailIjp.php?table_id=<?= $arrRow['table_id'] ?>&jns_pengajuan=<?= $arrRow['jns'] ?>&id_opmt_klaim_detail=<?php echo $id_opmt_klaim_detail?>&ko_wil=<?php echo $cabang_rekanan;?>','600','500')" ><img src="../_images/icon/ico_actedit.png" border="0" alt="Proses"></a>&nbsp;
                    <?php } else echo "&nbsp;";?>
                </td>
                <td align="left"><?= $arrRow['no_rekening']?>&nbsp;</td>
                <td align="left"><?= $arrRow['no_sertifikat'] ?>&nbsp;</td>
                <td align="right"><?= uang($arrRow['plafon_kredit'], true) ?>&nbsp;</td>
                <td align="center"><?= $arrRow['jangka_waktu'] ?>&nbsp;</td>
                <td align="center"><?=$arrRow['jenis_pengajuan'] ?>&nbsp;</td>
                <td align="center"><?=$arrRow['status_pengajuan'] ?>&nbsp;</td>
                <td align="right"><?= uang($arrRow['nilai_ijp'], true) ?>&nbsp;</td>
                <td align="right"><?= uang( ($arrRow['nilai_ijp'] - $arrRow['selisih']) , true) ?>&nbsp;</td>
                <td align="right"><?= uang($arrRow['ijp_bayar'], true) ?>&nbsp;</td>
                <td align="right"><?= uang($arrRow['nilai_refund'], true) ?>&nbsp;</td>
                <td align="right"><?= uang($arrRow['selisih'], true) ?></td>
                <td align="right">
                    <?php
                    if( ($arrRow['selisih'] > 0 && $arrRow['nilai_refund'] < 1 && $arrRow['ijp_bayar'] >= 1) ){
                        ?>
                        <a href="#" class="btn submit01 isDisabled_<?= $arrRow['id_opmt_transaksi_penjaminan'] ?>"
                           onclick="storeRefund(<?= $arrRow['id_opmt_transaksi_penjaminan'] ?>)"
                        >Refund</a>
                        <?php
                    } else {
                        if($key + 1 == count($arrData) && (round($arrRow['ijp_bayar'],0) == round($arrRow['nilai_ijp'] - $arrRow['selisih'],0)) ){
                            $lenghtData = count($arrData) - 1;
                            $checkLunas = 0;
                            for($i = 0; $i < $lenghtData; $i++){
                                $checkLunas += $arrData[$i]['status_refund'];
                            }
                            if($checkLunas == ( count($arrData) - 1 ) && $arrRow['flag_lunas_all'] != 1) {
                                ?>
                                <a href="#" class="btn submit01 isDisabled_<?= $arrRow['id_opmt_transaksi_penjaminan'] ?>"
                                   onclick="updateLunas(<?= $arrRow['id_calon_debitur_kur'] ?>)"
                                > Update Lunas </a>
                                <?php
                            }
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php
            //print_r('vania');
        }
        ?>
        <tr>
            <td colspan='10'>&nbsp;</td>
        </tr>
        </tbody>

    </table>
</div>
</form>

<script language="JavaScript" type="text/JavaScript" src="/_komponen/js/jquery.js"></script>
<script language="JavaScript" type="text/JavaScript" src="/_komponen/js/toastr.min.js"></script>
<script language="JavaScript" type="text/javascript">
    function storeRefund(idOpmt) {
        $('.isDisabled_'+idOpmt).prop("hidden",true)
        $.post('<?= $host ?>'+'/op_kur/refundIJP.php',
            {
                id_opmt_transaksi_penjaminan: idOpmt
            },
            function(data){
                const result = JSON.parse(data)
                if(result.status === 'ok'){
                    toastr["success"](result.message)
                    setTimeout( () => {
                        location.reload();
                    },2000);
                } else {
                    toastr["error"](result.message);
                    setTimeout( () => {
                        $('.isDisabled_'+idOpmt).prop("hidden",false)
                    },3000);
                }
            });
    }

    function updateLunas(idCalon) {
        alert(idCalon)
        $('.isDisabled_'+idCalon).prop("hidden",true)
        $.post('<?= $host ?>'+'/op_kur/refundIJP.php',
            {
                id_calon_debitur_kur: idCalon,
                lunas: true
            },
            function(data){
                const result = JSON.parse(data)
                if(result.status === 'ok'){
                    toastr["success"](result.message)
                    setTimeout( () => {
                        location.reload();
                    },2000);
                } else {
                    toastr["error"](result.message);
                    setTimeout( () => {
                        $('.isDisabled_'+idOpmt).prop("hidden",false)
                    },3000);
                }
            });
    }

</script>
</body>
</html>

<?php

namespace App\Models\KurMikro;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalonDebiturKurMikro extends Model
{
    use HasFactory;
    protected $table = 'calon_debitur_kur';
    protected $fillable = [
        'alamat_debitur','alamat_usaha','cabang_rekanan','cif','coverage','jangka_waktu','jenis_agunan','jenis_identitas',
        'jenis_kelamin','jenis_kredit','jenis_kur','jenis_linkage','jenis_pengikatan','jml_t_kerja','kode_bank','kode_pekerjaan',
        'kode_pos','kode_sektor','kode_uker','lembaga_linkage','modal_usaha','nama_debitur','nilai_agunan','no_hp','no_identitas',
        'no_ijin_usaha','no_pk','no_rekening','no_telepon','nomor_aplikasi','pendidikan','marital_status','omset_usaha','npwp','plafon_kredit',
        'status_kolektibilitas','status_lunas','suku_bunga','tanggal_akhir','tanggal_awal','tanggal_lahir','tanggal_mulai_usaha','tanggal_pk',
        'tanggal_rekam','negara_tujuan','marital_status','id_dd_bank','created_by','j_create_time','j_flag_persetujuan','tipe_kredit',
        'date_created'
    ];
    public $timestamps = false;
}

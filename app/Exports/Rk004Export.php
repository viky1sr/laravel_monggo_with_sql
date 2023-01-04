<?php

namespace App\Exports;

use App\Models\CoreKur\Rk004Dev;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class Rk004Export implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        $countRk = Rk004Dev::where('id_dd_bank',15)->limit(100)->get();
        $index = 0;
        return Rk004Dev::select(
            'nawil_kerja',
            'kode_uker',
            'nama_bank',
            'nama_bank_cabang'
        )->where('id_dd_bank',15)->cursor()->each(function ($q){
            return  [
                'nawil_kerja' => $q['nawil_kerja'],
                'kode_uker' => $q['kode_uker'],
                'nama_bank' => $q['nama_bank'],
                'nama_bank_cabang' => $q['nama_bank_cabang']
            ];
        });
    }

    public function map($row): array
    {
        return [
            $row->nawil_kerja,
            $row->kode_uker,
            $row->nama_bank,
            $row->nama_bank_cabang
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Wilahay Kerja',
            'Kode Uker',
            'Nama Bank',
            'Nama Bank Cabang',
        ];
    }

}

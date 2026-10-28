<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PasienExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    public function collection(): Enumerable
    {
        return User::where('role', 'pasien')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Rekam Medis (RM)',
            'Nama Pasien',
            'Alamat',
            'No. KTP',
            'No. Handphone',
            'Email',
        ];
    }

    public function map(mixed $pasien): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $pasien->no_rm ?? '-',
            $pasien->nama,
            $pasien->alamat ?? '-',
            "'" . ($pasien->no_ktp ?? '-'),
            "'" . ($pasien->no_hp ?? '-'),
            $pasien->email,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

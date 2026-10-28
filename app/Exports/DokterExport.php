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

class DokterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    public function collection(): Enumerable
    {
        return User::with('poli')
            ->where('role', 'dokter')
            ->orderBy('nama')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Dokter',
            'Poli',
            'Alamat',
            'No. KTP',
            'No. Handphone',
            'Email',
        ];
    }

    public function map(mixed $dokter): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $dokter->nama,
            $dokter->poli->nama_poli ?? '-',
            $dokter->alamat ?? '-',
            "'" . ($dokter->no_ktp ?? '-'),
            "'" . ($dokter->no_hp ?? '-'),
            $dokter->email,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

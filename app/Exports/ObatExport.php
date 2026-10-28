<?php

namespace App\Exports;

use App\Models\Obat;
use Illuminate\Support\Enumerable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ObatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    public function collection(): Enumerable
    {
        return Obat::orderBy('nama_obat')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Obat',
            'Kemasan',
            'Harga (Rp)',
            'Stok',
        ];
    }

    public function map(mixed $obat): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $obat->nama_obat,
            $obat->kemasan ?? '-',
            $obat->harga,
            $obat->stok ?? 0,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

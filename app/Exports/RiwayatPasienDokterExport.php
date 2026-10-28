<?php

namespace App\Exports;

use App\Models\Periksa;
use Carbon\Carbon;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatPasienDokterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    public function collection(): Enumerable
    {
        return Periksa::whereHas('daftarPoli.jadwalPeriksa', function ($query) {
                $query->where('id_dokter', Auth::id());
            })
            ->with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa',
                'detailPeriksas.obat'
            ])
            ->orderBy('tgl_periksa', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Periksa',
            'Nama Pasien',
            'No. Rekam Medis (RM)',
            'Keluhan',
            'Catatan Dokter / Diagnosis',
            'Resep Obat',
            'Total Biaya (Rp)',
        ];
    }

    public function map(mixed $periksa): array
    {
        $this->rowNumber++;

        $obatList = $periksa->detailPeriksas->map(function ($detail) {
            return ($detail->obat->nama_obat ?? 'Obat') . ' (' . ($detail->obat->kemasan ?? '-') . ')';
        })->implode(', ');

        return [
            $this->rowNumber,
            Carbon::parse($periksa->tgl_periksa)->format('d-m-Y H:i'),
            $periksa->daftarPoli->pasien->nama ?? '-',
            $periksa->daftarPoli->pasien->no_rm ?? '-',
            $periksa->daftarPoli->keluhan ?? '-',
            $periksa->catatan ?? '-',
            $obatList ?: '-',
            $periksa->biaya_periksa,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDiverifikasiNotification;
use Illuminate\Http\Request;

class VerifikasiPembayaranController extends Controller
{
    /**
     * Tampilkan daftar seluruh tagihan dan status verifikasi.
     */
    public function index()
    {
        $pembayarans = Periksa::with([
                'daftarPoli.pasien',
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat'
            ])
            ->orderByRaw("CASE 
                WHEN status_pembayaran = 'menunggu_verifikasi' THEN 1 
                WHEN status_pembayaran = 'belum_bayar' THEN 2 
                ELSE 3 END")
            ->orderBy('tgl_periksa', 'desc')
            ->get();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    /**
     * Konfirmasi pembayaran lunas oleh admin.
     */
    public function konfirmasi($id)
    {
        $periksa = Periksa::with([
            'daftarPoli.pasien',
            'daftarPoli.jadwalPeriksa.dokter.poli'
        ])->findOrFail($id);

        $periksa->update([
            'status_pembayaran' => 'lunas',
        ]);

        // Kirim notifikasi ke Pasien bahwa pembayaran telah diverifikasi (lunas) (sesuai PDF 2.b)
        $pasien = $periksa->daftarPoli->pasien ?? null;
        if ($pasien) {
            $pasien->notify(new PembayaranDiverifikasiNotification($periksa));
        }

        return back()->with('success', 'Pembayaran berhasil diverifikasi dan status telah berubah menjadi LUNAS!');
    }
}


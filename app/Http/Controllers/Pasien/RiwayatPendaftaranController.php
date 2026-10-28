<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use Illuminate\Support\Facades\Auth;

class RiwayatPendaftaranController extends Controller
{
    /**
     * Tampilkan riwayat pendaftaran pasien login.
     * Menggunakan Eager Loading untuk mencegah N+1 query.
     */
    public function index()
    {
        $riwayats = DaftarPoli::with([
                'jadwalPeriksa.dokter.poli',
                'periksa.detailPeriksas.obat'
            ])
            ->where('id_pasien', Auth::id())
            ->latest()
            ->get();

        return view('pasien.riwayat.index', compact('riwayats'));
    }

    /**
     * Tampilkan detail hasil pemeriksaan pendaftaran tertentu.
     */
    public function show($id)
    {
        $daftarPoli = DaftarPoli::with([
                'pasien',
                'jadwalPeriksa.dokter.poli',
                'periksa.detailPeriksas.obat'
            ])
            ->where('id_pasien', Auth::id())
            ->findOrFail($id);

        // Jika belum diperiksa, tidak bisa buka detail
        if (!$daftarPoli->periksa) {
            return redirect()
                ->route('pasien.riwayat.index')
                ->with('error', 'Pemeriksaan untuk pendaftaran ini belum selesai.');
        }

        return view('pasien.riwayat.show', compact('daftarPoli'));
    }
}
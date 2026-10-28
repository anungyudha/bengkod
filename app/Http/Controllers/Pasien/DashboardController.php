<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Pasien dengan Banner Antrian Aktif dan Tabel Antrian per-Poli.
     */
    public function index()
    {
        $pasienId = Auth::id();

        // 1. Antrian aktif milik pasien login (belum selesai diperiksa)
        $antrianAktif = DaftarPoli::with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', $pasienId)
            ->doesntHave('periksas')
            ->first();

        // 2. Daftar semua jadwal poliklinik & dokter beserta nomor antrian yang sedang dilayani
        $jadwals = JadwalPeriksa::with(['dokter.poli'])->get();

        return view('pasien.dashboard', compact('antrianAktif', 'jadwals'));
    }

    /**
     * Endpoint API status antrian (fallback & sync).
     */
    public function antrianStatus()
    {
        $pasienId = Auth::id();

        $antrianAktif = DaftarPoli::with(['jadwalPeriksa'])
            ->where('id_pasien', $pasienId)
            ->doesntHave('periksas')
            ->first();

        $jadwals = JadwalPeriksa::select('id', 'antrian_sekarang')->get();

        return response()->json([
            'antrian_aktif' => $antrianAktif ? [
                'id_jadwal' => $antrianAktif->id_jadwal,
                'no_antrian' => $antrianAktif->no_antrian,
                'antrian_sekarang' => $antrianAktif->jadwalPeriksa->antrian_sekarang ?? 0,
            ] : null,
            'jadwals' => $jadwals,
        ]);
    }
}


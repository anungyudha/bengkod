<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoliController extends Controller
{
    public function get()
    {
        $user = Auth::user();
        $polis = Poli::all();
        $jadwal = JadwalPeriksa::with('dokter', 'dokter.poli')->get();

        // Cek apakah pasien punya antrian aktif yang belum diperiksa
        $antrianAktif = DaftarPoli::with('jadwalPeriksa.dokter.poli')
            ->where('id_pasien', $user->id)
            ->doesntHave('periksas')
            ->first();

        return view('pasien.daftar', [
            'user' => $user,
            'polis' => $polis,
            'jadwals' => $jadwal,
            'antrianAktif' => $antrianAktif,
        ]);
    }

    public function submit(Request $request)
    {
        $request->validate([
            'id_poli' => 'required|exists:poli,id',
            'id_jadwal' => 'required|exists:jadwal_periksa,id',
            'keluhan' => 'nullable|string',
        ]);

        $pasienId = Auth::id();

        // CONSTRAINT: Cek jika pasien masih memiliki antrian aktif (belum diperiksa)
        $antrianAktif = DaftarPoli::where('id_pasien', $pasienId)
            ->doesntHave('periksas')
            ->first();

        if ($antrianAktif) {
            return redirect()
                ->back()
                ->with('error', 'Anda masih memiliki antrian aktif yang belum selesai diperiksa! Pasien tidak dapat mendaftar ke dua poli sekaligus.')
                ->withInput();
        }

        // Hitung nomor antrian pada jadwal yang dipilih
        $jumlahSudahDaftar = DaftarPoli::where('id_jadwal', $request->id_jadwal)->count();

        DaftarPoli::create([
            'id_pasien' => $pasienId,
            'id_jadwal' => $request->id_jadwal,
            'keluhan' => $request->keluhan,
            'no_antrian' => $jumlahSudahDaftar + 1,
        ]);

        return redirect()
            ->route('pasien.riwayat.index')
            ->with('success', 'Berhasil mendaftar ke poli! Nomor antrian Anda: ' . ($jumlahSudahDaftar + 1));
    }
}
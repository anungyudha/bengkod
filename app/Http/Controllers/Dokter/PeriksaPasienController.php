<?php

namespace App\Http\Controllers\Dokter;

use App\Events\AntrianUpdated;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeriksaPasienController extends Controller
{
    public function index()
    {
        $dokterId = Auth::id();

        $daftarPasien = DaftarPoli::with(['pasien', 'jadwalPeriksa.dokter.poli', 'periksas'])
            ->whereHas('jadwalPeriksa', function ($query) use ($dokterId) {
                $query->where('id_dokter', $dokterId);
            })
            ->orderBy('no_antrian')
            ->get();

        return view('dokter.periksa-pasien.index', compact('daftarPasien'));
    }

    /**
     * Panggil nomor antrian pasien secara live via Reverb WebSocket.
     */
    public function panggil($id)
    {
        $daftarPoli = DaftarPoli::with('jadwalPeriksa.dokter.poli')->findOrFail($id);
        $jadwal = $daftarPoli->jadwalPeriksa;

        if ($jadwal && $jadwal->id_dokter === Auth::id()) {
            $jadwal->update([
                'antrian_sekarang' => $daftarPoli->no_antrian,
            ]);

            // Broadcast real-time ke dashboard pasien via Reverb
            broadcast(new AntrianUpdated($jadwal));
        }

        return back()->with('success', "Nomor antrian #{$daftarPoli->no_antrian} berhasil dipanggil!");
    }

    public function create($id)
    {
        $obats = Obat::all();
        return view('dokter.periksa-pasien.create', compact('obats', 'id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'obat_json' => 'required',
            'catatan' => 'nullable|string',
            'biaya_periksa' => 'required|integer',
        ]);

        $obatIds = json_decode($request->obat_json, true);

        $periksa = Periksa::create([
            'id_daftar_poli' => $request->id_daftar_poli,
            'tgl_periksa' => now(),
            'catatan' => $request->catatan,
            'biaya_periksa' => $request->biaya_periksa + 150000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        if (is_array($obatIds)) {
            foreach ($obatIds as $idObat) {
                DetailPeriksa::create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $idObat,
                ]);
            }
        }

        // Perbarui nomor antrian saat ini pada jadwal dan broadcast live
        $daftarPoli = DaftarPoli::with('jadwalPeriksa.dokter.poli')->find($request->id_daftar_poli);
        if ($daftarPoli && $daftarPoli->jadwalPeriksa) {
            $jadwal = $daftarPoli->jadwalPeriksa;
            $jadwal->update([
                'antrian_sekarang' => $daftarPoli->no_antrian,
            ]);
            broadcast(new AntrianUpdated($jadwal));
        }

        return redirect()
            ->route('periksa-pasien.index')
            ->with('success', 'Data periksa berhasil disimpan.');
    }
}

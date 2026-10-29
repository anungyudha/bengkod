<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Periksa;
use App\Notifications\PembayaranDibuatNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Tampilkan daftar tagihan pemeriksaan pasien login.
     */
    public function index()
    {
        $tagihans = Periksa::whereHas('daftarPoli', function ($q) {
                $q->where('id_pasien', Auth::id());
            })
            ->with([
                'daftarPoli.jadwalPeriksa.dokter.poli',
                'detailPeriksas.obat'
            ])
            ->orderBy('tgl_periksa', 'desc')
            ->get();

        return view('pasien.pembayaran.index', compact('tagihans'));
    }

    /**
     * Upload bukti pembayaran oleh pasien.
     */
    public function upload(Request $request, $id)
    {
        $request->validate([
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'bukti_pembayaran.required' => 'Foto bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.image' => 'File harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar yang diperbolehkan: jpeg, png, jpg, webp.',
            'bukti_pembayaran.max' => 'Ukuran gambar maksimal 3MB.',
        ]);

        $periksa = Periksa::whereHas('daftarPoli', function ($q) {
                $q->where('id_pasien', Auth::id());
            })
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->findOrFail($id);

        // Jika sebelumnya sudah ada file bukti, hapus file lama
        if ($periksa->bukti_pembayaran && Storage::disk('public')->exists($periksa->bukti_pembayaran)) {
            Storage::disk('public')->delete($periksa->bukti_pembayaran);
        }

        // Simpan file ke storage/app/public/bukti_pembayaran
        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');

        $periksa->update([
            'bukti_pembayaran' => $path,
            'status_pembayaran' => 'menunggu_verifikasi',
            'tgl_bayar' => now(),
        ]);

        // Kirim notifikasi ke Dokter terkait
        $dokter = $periksa->daftarPoli->jadwalPeriksa->dokter ?? null;
        if ($dokter) {
            $dokter->notify(new PembayaranDibuatNotification($periksa));
        }

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Mohon menunggu verifikasi admin.');
    }
}


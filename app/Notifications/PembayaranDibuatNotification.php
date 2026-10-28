<?php

namespace App\Notifications;

use App\Models\Periksa;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranDibuatNotification extends Notification
{
    use Queueable;

    public Periksa $periksa;

    /**
     * Create a new notification instance.
     */
    public function __construct(Periksa $periksa)
    {
        $this->periksa = $periksa;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $pasienNama = $this->periksa->daftarPoli->pasien->nama ?? 'Pasien';
        $poliNama = $this->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';
        $tgl = Carbon::parse($this->periksa->tgl_periksa)->format('d/m/Y');

        return [
            'type' => 'pembayaran_dibuat',
            'title' => 'Bukti Pembayaran Diunggah',
            'message' => "Pasien {$pasienNama} ({$poliNama}) telah mengunggah bukti pembayaran sebesar Rp " . number_format($this->periksa->biaya_periksa, 0, ',', '.') . " untuk pemeriksaan tanggal {$tgl}.",
            'id_periksa' => $this->periksa->id,
            'nominal' => $this->periksa->biaya_periksa,
            'url' => route('riwayat-pasien.show', $this->periksa->id),
        ];
    }
}


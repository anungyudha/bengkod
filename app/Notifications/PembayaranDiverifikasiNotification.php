<?php

namespace App\Notifications;

use App\Models\Periksa;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PembayaranDiverifikasiNotification extends Notification
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
        $poliNama = $this->periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli';
        $tgl = Carbon::parse($this->periksa->tgl_periksa)->format('d/m/Y');
        $nominal = number_format($this->periksa->biaya_periksa, 0, ',', '.');

        return [
            'type' => 'pembayaran_diverifikasi',
            'title' => 'Pembayaran Terverifikasi (Lunas)',
            'message' => "Pembayaran Anda sebesar Rp {$nominal} untuk pemeriksaan di {$poliNama} (tanggal {$tgl}) telah diverifikasi oleh admin dan dinyatakan LUNAS.",
            'id_periksa' => $this->periksa->id,
            'status' => 'lunas',
            'nominal' => $this->periksa->biaya_periksa,
            'url' => route('pasien.pembayaran.index'),
        ];
    }
}


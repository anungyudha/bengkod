<?php

namespace App\Events;

use App\Models\JadwalPeriksa;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AntrianUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public JadwalPeriksa $jadwal;

    /**
     * Create a new event instance.
     */
    public function __construct(JadwalPeriksa $jadwal)
    {
        $this->jadwal = $jadwal;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('antrian-channel'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'antrian.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id_jadwal' => $this->jadwal->id,
            'antrian_sekarang' => $this->jadwal->antrian_sekarang,
            'id_dokter' => $this->jadwal->id_dokter,
            'nama_dokter' => $this->jadwal->dokter->nama ?? '-',
            'nama_poli' => $this->jadwal->dokter->poli->nama_poli ?? '-',
        ];
    }
}


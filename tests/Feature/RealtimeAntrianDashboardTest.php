<?php

namespace Tests\Feature;

use App\Events\AntrianUpdated;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RealtimeAntrianDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function setupData()
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Mata',
            'deskripsi' => 'Layanan Kesehatan Mata',
        ]);

        $dokter = User::factory()->create([
            'nama' => 'Dr. Rian Mata',
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Rabu',
            'jam_mulai' => '10:00',
            'jam_selesai' => '13:00',
            'antrian_sekarang' => 2,
        ]);

        $pasien = User::factory()->create([
            'nama' => 'Rina Sugiarto',
            'role' => 'pasien',
            'no_rm' => '202610-015',
        ]);

        return [$poli, $dokter, $jadwal, $pasien];
    }

    public function test_dashboard_shows_empty_message_when_no_active_queue(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));
        $response->assertOk();
        $response->assertSee('Tidak Ada Antrian Aktif');
        $response->assertSee('Poli Mata');
        $response->assertSee('Dr. Rian Mata');
    }

    public function test_dashboard_shows_banner_when_patient_has_active_queue(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Mata sering berair dan buram',
            'no_antrian' => 5,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.dashboard'));
        $response->assertOk();
        $response->assertSee('ANTRIAN AKTIF ANDA');
        $response->assertSee('NOMOR ANDA');
        $response->assertSee('SEDANG DILAYANI');
        $response->assertSee('Poli Mata');
        $response->assertSee('Dr. Rian Mata');
        $response->assertSee('5'); // nomor anda
        $response->assertSee('2'); // sedang dilayani
    }

    public function test_dokter_can_call_queue_and_dispatches_broadcast_event(): void
    {
        Event::fake([AntrianUpdated::class]);

        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Mata perih',
            'no_antrian' => 3,
        ]);

        $response = $this->actingAs($dokter)->post(route('periksa-pasien.panggil', $daftar->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $jadwal->refresh();
        $this->assertEquals(3, $jadwal->antrian_sekarang);

        // Verify AntrianUpdated broadcast event was dispatched
        Event::assertDispatched(AntrianUpdated::class, function ($event) use ($jadwal) {
            return $event->jadwal->id === $jadwal->id && $event->jadwal->antrian_sekarang === 3;
        });
    }

    public function test_antrian_status_endpoint_returns_json_for_live_sync(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        $response = $this->actingAs($pasien)->get(route('pasien.antrian-status'));
        $response->assertOk();
        $response->assertJsonStructure([
            'antrian_aktif',
            'jadwals' => [
                '*' => ['id', 'antrian_sekarang']
            ]
        ]);
    }
}


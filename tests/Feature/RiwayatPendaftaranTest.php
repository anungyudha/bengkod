<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPendaftaranTest extends TestCase
{
    use RefreshDatabase;

    private function setupData()
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Umum',
            'deskripsi' => 'Layanan Umum',
        ]);

        $dokter = User::factory()->create([
            'nama' => 'Dr. Budi',
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '12:00',
        ]);

        $pasien = User::factory()->create([
            'nama' => 'Pasien Andi',
            'role' => 'pasien',
            'no_rm' => '202610-001',
        ]);

        return [$poli, $dokter, $jadwal, $pasien];
    }

    public function test_pasien_can_view_riwayat_index(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam tinggi',
            'no_antrian' => 1,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.index'));
        $response->assertOk();
        $response->assertSee('Riwayat Pendaftaran Poli');
        $response->assertSee('Poli Umum');
        $response->assertSee('Menunggu Pemeriksaan');
    }

    public function test_pasien_cannot_register_if_active_queue_exists(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        // Antrian 1 aktif (belum ada data periksa)
        DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam tinggi',
            'no_antrian' => 1,
        ]);

        // Coba daftar lagi
        $response = $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $poli->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Sakit kepala',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertEquals(1, DaftarPoli::where('id_pasien', $pasien->id)->count());
    }

    public function test_pasien_can_register_again_after_examination_completed(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        // Antrian 1
        $daftar1 = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Demam tinggi',
            'no_antrian' => 1,
        ]);

        // Selesai diperiksa
        Periksa::create([
            'id_daftar_poli' => $daftar1->id,
            'tgl_periksa' => now(),
            'catatan' => 'Istirahat cukup',
            'biaya_periksa' => 150000,
        ]);

        // Daftar lagi (seharusnya berhasil)
        $response = $this->actingAs($pasien)->post(route('pasien.daftar.submit'), [
            'id_poli' => $poli->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Flu ringan',
        ]);

        $response->assertRedirect(route('pasien.riwayat.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(2, DaftarPoli::where('id_pasien', $pasien->id)->count());
    }

    public function test_pasien_can_view_examination_detail_with_medicines_and_cost(): void
    {
        [$poli, $dokter, $jadwal, $pasien] = $this->setupData();

        $obat = Obat::create([
            'nama_obat' => 'Paracetamol 500mg',
            'kemasan' => 'Strip',
            'harga' => 10000,
        ]);

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Sakit kepala',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Minum obat 3x sehari',
            'biaya_periksa' => 160000,
        ]);

        DetailPeriksa::create([
            'id_periksa' => $periksa->id,
            'id_obat' => $obat->id,
        ]);

        $response = $this->actingAs($pasien)->get(route('pasien.riwayat.show', $daftar->id));
        $response->assertOk();
        $response->assertSee('Detail Hasil Pemeriksaan');
        $response->assertSee('Minum obat 3x sehari');
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('160.000');
    }
}


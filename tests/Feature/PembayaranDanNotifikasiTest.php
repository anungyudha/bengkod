<?php

namespace Tests\Feature;

use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use App\Notifications\PembayaranDibuatNotification;
use App\Notifications\PembayaranDiverifikasiNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PembayaranDanNotifikasiTest extends TestCase
{
    use RefreshDatabase;

    private function setupData()
    {
        $poli = Poli::create([
            'nama_poli' => 'Poli Gigi',
            'deskripsi' => 'Layanan Gigi',
        ]);

        $dokter = User::factory()->create([
            'nama' => 'Dr. Gigi Sarah',
            'role' => 'dokter',
            'id_poli' => $poli->id,
        ]);

        $jadwal = JadwalPeriksa::create([
            'id_dokter' => $dokter->id,
            'hari' => 'Selasa',
            'jam_mulai' => '09:00',
            'jam_selesai' => '12:00',
        ]);

        $pasien = User::factory()->create([
            'nama' => 'Budi Santoso',
            'role' => 'pasien',
            'no_rm' => '202610-009',
        ]);

        $daftar = DaftarPoli::create([
            'id_pasien' => $pasien->id,
            'id_jadwal' => $jadwal->id,
            'keluhan' => 'Sakit gigi geraham',
            'no_antrian' => 1,
        ]);

        $periksa = Periksa::create([
            'id_daftar_poli' => $daftar->id,
            'tgl_periksa' => now(),
            'catatan' => 'Pembersihan karang gigi',
            'biaya_periksa' => 200000,
            'status_pembayaran' => 'belum_bayar',
        ]);

        return [$dokter, $pasien, $periksa];
    }

    public function test_pasien_can_view_pembayaran_page(): void
    {
        [$dokter, $pasien, $periksa] = $this->setupData();

        $response = $this->actingAs($pasien)->get(route('pasien.pembayaran.index'));
        $response->assertOk();
        $response->assertSee('Pembayaran & Tagihan');
        $response->assertSee('200.000');
        $response->assertSee('Belum Bayar');
    }

    public function test_pasien_can_upload_payment_proof_and_notifies_doctor(): void
    {
        Storage::fake('public');
        Notification::fake();

        [$dokter, $pasien, $periksa] = $this->setupData();

        $file = UploadedFile::fake()->image('bukti_transfer.jpg');

        $response = $this->actingAs($pasien)->post(route('pasien.pembayaran.upload', $periksa->id), [
            'bukti_pembayaran' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('menunggu_verifikasi', $periksa->status_pembayaran);
        $this->assertNotNull($periksa->bukti_pembayaran);
        Storage::disk('public')->assertExists($periksa->bukti_pembayaran);

        // Verify notification sent to dokter
        Notification::assertSentTo($dokter, PembayaranDibuatNotification::class);
    }

    public function test_admin_can_view_and_verify_payment_and_notifies_pasien(): void
    {
        Notification::fake();

        [$dokter, $pasien, $periksa] = $this->setupData();
        $periksa->update([
            'bukti_pembayaran' => 'bukti_pembayaran/dummy.jpg',
            'status_pembayaran' => 'menunggu_verifikasi',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        // Admin opens verification page
        $response = $this->actingAs($admin)->get(route('admin.pembayaran.index'));
        $response->assertOk();
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Budi Santoso');

        // Admin confirms payment
        $postResponse = $this->actingAs($admin)->post(route('admin.pembayaran.konfirmasi', $periksa->id));
        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('success');

        $periksa->refresh();
        $this->assertEquals('lunas', $periksa->status_pembayaran);

        // Verify notification sent to pasien
        Notification::assertSentTo($pasien, PembayaranDiverifikasiNotification::class);
    }

    public function test_user_can_view_and_mark_notification_as_read(): void
    {
        [$dokter, $pasien, $periksa] = $this->setupData();

        // Send a real database notification
        $pasien->notify(new PembayaranDiverifikasiNotification($periksa));

        $this->assertEquals(1, $pasien->unreadNotifications->count());

        $notification = $pasien->unreadNotifications->first();

        // View notification index
        $response = $this->actingAs($pasien)->get(route('notifications.index'));
        $response->assertOk();
        $response->assertSee('Pembayaran Terverifikasi (Lunas)');

        // Mark as read
        $readResponse = $this->actingAs($pasien)->post(route('notifications.read', $notification->id));
        $readResponse->assertRedirect();

        $pasien->refresh();
        $this->assertEquals(0, $pasien->unreadNotifications->count());
    }
}


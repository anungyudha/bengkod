<?php

namespace Tests\Feature;

use App\Exports\DokterExport;
use App\Exports\JadwalDokterExport;
use App\Exports\ObatExport;
use App\Exports\PasienExport;
use App\Exports\RiwayatPasienDokterExport;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ExportExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_dokter_excel(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('dokter.export'));
        $response->assertOk();

        Excel::assertDownloaded('data-dokter.xlsx', function (DokterExport $export) {
            return true;
        });
    }

    public function test_admin_can_export_pasien_excel(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('pasien.export'));
        $response->assertOk();

        Excel::assertDownloaded('data-pasien.xlsx', function (PasienExport $export) {
            return true;
        });
    }

    public function test_admin_can_export_obat_excel_with_stok_and_harga(): void
    {
        Excel::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('obat.export'));
        $response->assertOk();

        Excel::assertDownloaded('data-obat.xlsx', function (ObatExport $export) {
            return true;
        });
    }

    public function test_dokter_can_export_jadwal_periksa_excel(): void
    {
        Excel::fake();

        $dokter = User::factory()->create([
            'role' => 'dokter',
        ]);

        $response = $this->actingAs($dokter)->get(route('jadwal-periksa.export'));
        $response->assertOk();

        Excel::assertDownloaded('jadwal-periksa.xlsx', function (JadwalDokterExport $export) {
            return true;
        });
    }

    public function test_dokter_can_export_riwayat_pasien_excel(): void
    {
        Excel::fake();

        $dokter = User::factory()->create([
            'role' => 'dokter',
        ]);

        $response = $this->actingAs($dokter)->get(route('riwayat-pasien.export'));
        $response->assertOk();

        Excel::assertDownloaded('riwayat-pasien.xlsx', function (RiwayatPasienDokterExport $export) {
            return true;
        });
    }

    public function test_pasien_cannot_access_admin_or_dokter_exports(): void
    {
        $pasien = User::factory()->create([
            'role' => 'pasien',
        ]);

        $this->actingAs($pasien)->get(route('dokter.export'))->assertForbidden();
        $this->actingAs($pasien)->get(route('jadwal-periksa.export'))->assertForbidden();
    }
}


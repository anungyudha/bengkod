<x-layouts.app title="Detail Hasil Pemeriksaan">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('pasien.riwayat.index') }}" 
           class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Detail Hasil Pemeriksaan
            </h2>
            <p class="text-xs text-slate-500">
                Nomor Antrian: <span class="font-bold text-indigo-600">#{{ $daftarPoli->no_antrian }}</span> • Tanggal: {{ \Carbon\Carbon::parse($daftarPoli->periksa->tgl_periksa)->translatedFormat('d F Y, H:i') }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Info Dokter & Poli --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-6">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">
                        Informasi Pemeriksaan
                    </h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-xs text-slate-400 block font-semibold">POLI</span>
                            <span class="font-bold text-slate-800">
                                {{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-semibold">DOKTER PEMERIKSA</span>
                            <span class="font-bold text-slate-800">
                                Dr. {{ $daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-semibold">KELUHAN AWAL</span>
                            <p class="text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100 mt-1">
                                {{ $daftarPoli->keluhan ?: 'Tidak ada keluhan tercatat.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Catatan Dokter & Rincian Obat & Biaya --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Catatan Dokter --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-6">
                    <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-notes-medical text-indigo-600"></i> Catatan Dokter / Diagnosis
                    </h3>
                    <div class="bg-indigo-50/50 p-4 rounded-xl border border-indigo-100 text-slate-700 text-sm leading-relaxed">
                        {{ $daftarPoli->periksa->catatan ?: 'Tidak ada catatan dokter.' }}
                    </div>
                </div>
            </div>

            {{-- Daftar Obat & Total Biaya --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-6">
                    <h3 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-pills text-indigo-600"></i> Resep Obat & Rincian Biaya
                    </h3>

                    <div class="overflow-x-auto mb-4">
                        <table class="table w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs">
                                <tr>
                                    <th class="py-3 px-4">Deskripsi</th>
                                    <th class="py-3 px-4 text-center">Kemasan</th>
                                    <th class="py-3 px-4 text-right">Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="py-3 px-4 font-medium text-slate-700">
                                        Jasa Pemeriksaan Dokter
                                    </td>
                                    <td class="py-3 px-4 text-center text-slate-400">-</td>
                                    <td class="py-3 px-4 text-right font-medium text-slate-700">
                                        Rp 150.000
                                    </td>
                                </tr>
                                @forelse ($daftarPoli->periksa->detailPeriksas as $detail)
                                    <tr>
                                        <td class="py-3 px-4 font-medium text-slate-700">
                                            {{ $detail->obat->nama_obat ?? 'Obat' }}
                                        </td>
                                        <td class="py-3 px-4 text-center text-slate-500">
                                            {{ $detail->obat->kemasan ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-right font-medium text-slate-700">
                                            Rp {{ number_format($detail->obat->harga ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-3 px-4 text-center text-slate-400 italic">
                                            Tidak ada resep obat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold text-slate-800">
                                <tr>
                                    <td colspan="2" class="py-4 px-4 text-base">TOTAL BIAYA</td>
                                    <td class="py-4 px-4 text-right text-lg text-indigo-600">
                                        Rp {{ number_format($daftarPoli->periksa->biaya_periksa, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>

</x-layouts.app>
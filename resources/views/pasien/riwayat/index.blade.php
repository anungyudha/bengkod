<x-layouts.app title="Riwayat Pendaftaran Poli">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Riwayat Pendaftaran Poli
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Daftar seluruh riwayat pendaftaran pemeriksaan Anda di Poliklinik.
            </p>
        </div>
        <a href="{{ route('pasien.daftar') }}" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
            <i class="fas fa-plus mr-1"></i> Daftar Poli Baru
        </a>
    </div>

    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Poli & Dokter</th>
                        <th class="py-4 px-6">Jadwal Periksa</th>
                        <th class="py-4 px-6 text-center">No. Antrian</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($riwayats as $index => $riwayat)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6 text-center font-medium text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">
                                    {{ $riwayat->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    <i class="fas fa-user-doctor text-[10px] mr-1"></i>
                                    Dr. {{ $riwayat->jadwalPeriksa->dokter->nama ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-700">
                                    {{ $riwayat->jadwalPeriksa->hari ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $riwayat->jadwalPeriksa->jam_mulai ?? '' }} - {{ $riwayat->jadwalPeriksa->jam_selesai ?? '' }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-indigo-50 text-indigo-700 font-extrabold text-sm border border-indigo-200">
                                    {{ $riwayat->no_antrian }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($riwayat->periksa)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-check-circle text-[10px]"></i> Sudah Diperiksa
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-clock text-[10px]"></i> Menunggu Pemeriksaan
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($riwayat->periksa)
                                    <a href="{{ route('pasien.riwayat.show', $riwayat->id) }}" 
                                       class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-sm">
                                        <i class="fas fa-file-medical mr-1"></i> Detail
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-disabled rounded-lg opacity-50" title="Belum diperiksa oleh dokter">
                                        Detail
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-slate-400">
                                <i class="fas fa-notes-medical text-4xl mb-3 block text-slate-300"></i>
                                Belum ada riwayat pendaftaran poli.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>
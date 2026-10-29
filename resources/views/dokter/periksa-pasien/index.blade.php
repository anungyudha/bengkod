<x-layouts.app title="Periksa Pasien">

    {{-- Header & Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Periksa Pasien
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Panggil nomor antrian pasien secara live ke dashboard pasien dan lakukan pemeriksaan medis.
            </p>
        </div>

        {{-- Filter per Jadwal --}}
        @if(isset($jadwals) && $jadwals->count() > 1)
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Filter Jadwal:</span>
            <div class="join">
                <a href="{{ route('periksa-pasien.index') }}" 
                   class="btn btn-sm join-item {{ empty($selectedJadwal) ? 'btn-primary' : 'btn-outline border-slate-300' }}">
                    Semua
                </a>
                @foreach($jadwals as $j)
                    <a href="{{ route('periksa-pasien.index', ['jadwal_id' => $j->id]) }}" 
                       class="btn btn-sm join-item {{ ($selectedJadwal == $j->id) ? 'btn-primary' : 'btn-outline border-slate-300' }}">
                        {{ $j->hari }} ({{ $j->jam_mulai }})
                    </a>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Alert Flash Message --}}
    @if (session('message'))
    <div class="alert alert-{{ session('type') ?? 'success' }} mb-4 rounded-xl shadow-sm" role="alert">
        <i class="fas fa-{{ session('type') == 'danger' ? 'circle-xmark' : 'circle-check' }}"></i>
        <span>{{ session('message') }}</span>
    </div>
    @endif

    {{-- Card --}}
    <div class="card bg-base-100 shadow-md rounded-2 border">
        <div class="card-body p-0">

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">

                    {{-- Head --}}
                    <thead class="bg-slate-100 text-slate-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-4">No</th>
                            <th class="px-6 py-4">Pasien</th>
                            <th class="px-6 py-4">Jadwal Praktik</th>
                            <th class="px-6 py-4">Keluhan</th>
                            <th class="px-6 py-4 text-center">No Antrian</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    {{-- Body --}}
                    <tbody>
                        @forelse ($daftarPasien as $dp)
                        <tr class="hover:bg-slate-50 transition {{ ($dp->jadwalPeriksa->antrian_sekarang ?? 0) == $dp->no_antrian && $dp->periksas->isEmpty() ? 'bg-indigo-50/50' : '' }}">

                            <td class="px-6 py-4 text-slate-500">
                                {{ $loop->iteration }}
                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $dp->pasien->nama }}
                                <div class="text-[11px] text-slate-400 font-normal">
                                    RM: {{ $dp->pasien->no_rm ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <i class="fas fa-calendar-day text-[10px]"></i>
                                    {{ $dp->jadwalPeriksa->hari ?? '-' }}
                                </span>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $dp->jadwalPeriksa->jam_mulai ?? '' }} - {{ $dp->jadwalPeriksa->jam_selesai ?? '' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $dp->keluhan ?: '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-bold text-sm {{ ($dp->jadwalPeriksa->antrian_sekarang ?? 0) == $dp->no_antrian ? 'bg-indigo-600 text-white animate-pulse shadow-sm' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $dp->no_antrian }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if ($dp->periksas->isNotEmpty())
                                    <span class="badge bg-green-100 text-green-700 border border-green-200 rounded-lg px-3 py-1 text-xs font-semibold">
                                        <i class="fas fa-circle-check mr-1"></i>Sudah Diperiksa
                                    </span>
                                @else
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('periksa-pasien.panggil', $dp->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" 
                                                    class="btn btn-sm {{ ($dp->jadwalPeriksa->antrian_sekarang ?? 0) == $dp->no_antrian ? 'bg-indigo-600 text-white' : 'btn-outline border-slate-300 text-slate-700 hover:bg-slate-100' }} rounded-lg text-xs" 
                                                    title="Panggil Antrian Live">
                                                <i class="fas fa-bullhorn mr-1"></i>
                                                {{ ($dp->jadwalPeriksa->antrian_sekarang ?? 0) == $dp->no_antrian ? 'Dipanggil' : 'Panggil' }}
                                            </button>
                                        </form>

                                        <a href="{{ route('periksa-pasien.create', $dp->id) }}"
                                            class="btn btn-sm bg-amber-500 hover:bg-amber-600 text-white border-none rounded-lg px-4 text-xs font-semibold">
                                            <i class="fas fa-stethoscope mr-1"></i>
                                            Periksa
                                        </a>
                                    </div>
                                @endif
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-14 text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <i class="fas fa-inbox text-3xl"></i>
                                    <span>Belum ada antrian pasien</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</x-layouts.app>

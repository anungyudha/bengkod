<x-layouts.app title="Dashboard Pasien">

    {{-- Welcome Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Halo, {{ auth()->user()->nama ?? auth()->user()->name ?? 'Pasien' }} 👋
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Selamat datang di sistem antrian dan layanan kesehatan Poliklinik.
        </p>
    </div>

    {{-- ================= 1. BANNER ANTRIAN AKTIF ================= --}}
    @if ($antrianAktif)
        <div id="banner-antrian-container" class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-indigo-700 to-indigo-900 text-white shadow-lg mb-8 p-6 sm:p-8 border border-indigo-400/30">
            
            {{-- Background Decors --}}
            <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                
                {{-- Info Antrian Pasien --}}
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-white/20 text-white border border-white/30 backdrop-blur-sm">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        ANTRIAN AKTIF ANDA
                    </span>

                    <h3 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
                        {{ $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? 'Poli Umum' }}
                    </h3>

                    <div class="flex flex-wrap items-center gap-3 text-indigo-100 text-sm">
                        <span class="font-medium flex items-center gap-1.5">
                            <i class="fas fa-user-doctor text-indigo-300"></i>
                            Dr. {{ $antrianAktif->jadwalPeriksa->dokter->nama ?? '-' }}
                        </span>
                        <span class="text-white/40">•</span>
                        <span class="font-medium flex items-center gap-1.5">
                            <i class="fas fa-calendar-day text-indigo-300"></i>
                            {{ $antrianAktif->jadwalPeriksa->hari }} ({{ $antrianAktif->jadwalPeriksa->jam_mulai }} - {{ $antrianAktif->jadwalPeriksa->jam_selesai }})
                        </span>
                    </div>

                    @if ($antrianAktif->keluhan)
                        <p class="text-xs text-indigo-200/90 pt-1">
                            <span class="font-semibold text-white">Keluhan:</span> {{ $antrianAktif->keluhan }}
                        </p>
                    @endif
                </div>

                {{-- 2 Kotak Nomor Antrian (Persis Screenshot PDF) --}}
                <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                    
                    {{-- Kotak 1: Nomor Anda --}}
                    <div class="flex-1 md:flex-initial w-32 sm:w-36 bg-white/10 backdrop-blur-md rounded-2xl p-4 text-center border border-white/20 shadow-inner">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-200 block mb-1">
                            NOMOR ANDA
                        </span>
                        <span class="text-4xl sm:text-5xl font-black text-white block">
                            {{ $antrianAktif->no_antrian }}
                        </span>
                    </div>

                    {{-- Kotak 2: Sedang Dilayani (LIVE UPDATE) --}}
                    <div class="flex-1 md:flex-initial w-32 sm:w-36 bg-white/20 backdrop-blur-md rounded-2xl p-4 text-center border-2 border-indigo-300 shadow-xl relative overflow-hidden">
                        <div class="absolute top-1 right-2">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                        </div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-yellow-300 block mb-1">
                            SEDANG DILAYANI
                        </span>
                        <span id="banner-antrian-sekarang" 
                              data-jadwal-id="{{ $antrianAktif->id_jadwal }}" 
                              class="text-4xl sm:text-5xl font-black text-yellow-300 block transition-all duration-300">
                            {{ ($antrianAktif->jadwalPeriksa->antrian_sekarang ?? 0) > 0 ? $antrianAktif->jadwalPeriksa->antrian_sekarang : '-' }}
                        </span>
                    </div>

                </div>

            </div>
        </div>
    @else
        {{-- Jika Tidak Memiliki Antrian Aktif --}}
        <div class="card bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8 mb-8 text-center shadow-sm">
            <div class="max-w-md mx-auto">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3 text-2xl font-bold">
                    <i class="fas fa-ticket-simple"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">
                    Tidak Ada Antrian Aktif
                </h3>
                <p class="text-xs text-slate-500 mt-1 mb-4 leading-relaxed">
                    Anda sedang tidak memiliki pendaftaran antrian aktif. Silakan pilih jadwal poli dan dokter untuk mendaftar pemeriksaan.
                </p>
                <a href="{{ route('pasien.daftar') }}" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-6 text-sm font-semibold shadow-sm">
                    <i class="fas fa-plus mr-1.5"></i> Daftar Poli Sekarang
                </a>
            </div>
        </div>
    @endif

    {{-- ================= 2. TABEL JADWAL & NOMOR SEDANG DILAYANI PER POLI ================= --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-hospital text-indigo-600"></i>
                    Daftar Jadwal Poliklinik & Antrian Live
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Nomor antrian yang sedang dipanggil di setiap poli ter-update secara otomatis tanpa reload.
                </p>
            </div>
            
            <div class="flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 w-fit">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>WebSocket Live Reverb Active</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Poliklinik</th>
                        <th class="py-4 px-6">Dokter Spesialis</th>
                        <th class="py-4 px-6">Hari & Jam Periksa</th>
                        <th class="py-4 px-6 text-center">Sedang Dilayani</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($jadwals as $index => $jadwal)
                        <tr class="hover:bg-slate-50/70 transition" id="row-jadwal-{{ $jadwal->id }}">
                            <td class="py-4 px-6 text-center font-medium text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-4 px-6 font-bold text-slate-800">
                                {{ $jadwal->dokter->poli->nama_poli ?? '-' }}
                            </td>
                            <td class="py-4 px-6 font-semibold text-slate-700">
                                <i class="fas fa-user-doctor text-indigo-600 text-xs mr-1"></i>
                                Dr. {{ $jadwal->dokter->nama ?? '-' }}
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-semibold text-slate-700">{{ $jadwal->hari }}</span>
                                <span class="text-xs text-slate-500 block">
                                    {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <span id="jadwal-antrian-{{ $jadwal->id }}" 
                                      class="inline-flex items-center justify-center min-w-[2.5rem] h-9 px-3 rounded-xl font-extrabold text-base transition-all duration-300 {{ ($jadwal->antrian_sekarang ?? 0) > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-400' }}">
                                    {{ ($jadwal->antrian_sekarang ?? 0) > 0 ? $jadwal->antrian_sekarang : '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-slate-400">
                                Belum ada data jadwal poliklinik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- ================= SCRIPT REAL-TIME WEBSOCKET (LARAVEL ECHO + REVERB) ================= --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            function updateQueueUI(data) {
                const idJadwal = data.id_jadwal;
                const nomorSekarang = data.antrian_sekarang;

                // 1. Update di Banner Pasien jika jadwalnya sama
                const bannerEl = document.getElementById('banner-antrian-sekarang');
                if (bannerEl) {
                    const activeJadwalId = bannerEl.getAttribute('data-jadwal-id');
                    if (activeJadwalId == idJadwal) {
                        bannerEl.textContent = (nomorSekarang > 0) ? nomorSekarang : '-';
                        bannerEl.classList.add('scale-125', 'text-white');
                        setTimeout(() => bannerEl.classList.remove('scale-125', 'text-white'), 800);
                    }
                }

                // 2. Update di Tabel Daftar Jadwal
                const tableCell = document.getElementById(`jadwal-antrian-${idJadwal}`);
                if (tableCell) {
                    tableCell.textContent = (nomorSekarang > 0) ? nomorSekarang : '-';
                    tableCell.className = 'inline-flex items-center justify-center min-w-[2.5rem] h-9 px-3 rounded-xl font-extrabold text-base transition-all duration-300 bg-indigo-600 text-white scale-125 shadow-md';
                    setTimeout(() => {
                        tableCell.className = (nomorSekarang > 0)
                            ? 'inline-flex items-center justify-center min-w-[2.5rem] h-9 px-3 rounded-xl font-extrabold text-base transition-all duration-300 bg-indigo-50 text-indigo-700 border border-indigo-200'
                            : 'inline-flex items-center justify-center min-w-[2.5rem] h-9 px-3 rounded-xl font-extrabold text-base transition-all duration-300 bg-slate-100 text-slate-400';
                    }, 1000);
                }
            }

            // 1. Inisialisasi WebSocket Listener via Laravel Echo
            if (window.Echo) {
                window.Echo.channel('antrian-channel')
                    .listen('.antrian.updated', (event) => {
                        console.log('⚡ [Live Reverb] Antrian Diperbarui:', event);
                        updateQueueUI(event);
                    });
            }

            // 2. Fallback Polling (Setiap 4 detik sinkronisasi status otomatis)
            setInterval(() => {
                fetch('{{ route("pasien.antrian-status") }}')
                    .then(res => res.json())
                    .then(data => {
                        if (data.jadwals) {
                            data.jadwals.forEach(j => {
                                const el = document.getElementById(`jadwal-antrian-${j.id}`);
                                if (el && el.textContent.trim() !== ((j.antrian_sekarang > 0) ? String(j.antrian_sekarang) : '-')) {
                                    updateQueueUI({ id_jadwal: j.id, antrian_sekarang: j.antrian_sekarang });
                                }
                            });
                        }
                    })
                    .catch(err => console.debug('Sync fallback check:', err));
            }, 4000);

        });
    </script>
    @endpush

</x-layouts.app>

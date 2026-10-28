<x-layouts.app title="Pendaftaran Poli">

    <div class="flex items-center justify-center px-4 py-6">
        <div class="w-full max-w-3xl">

            {{-- JIKA PASIEN MEMILIKI ANTRIAN AKTIF --}}
            @if ($antrianAktif)
                <div class="card bg-amber-50 border-2 border-amber-300 shadow-sm rounded-2xl mb-6">
                    <div class="card-body p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-500 text-white flex items-center justify-center text-2xl font-bold shrink-0">
                                <i class="fas fa-triangle-exclamation"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-amber-900 leading-tight">
                                    Anda Memiliki Antrian Aktif
                                </h3>
                                <p class="text-sm text-amber-800 mt-1">
                                    Sesuai ketentuan, Anda tidak dapat mendaftar ke dua poli sekaligus. Anda dapat mendaftar kembali setelah pemeriksaan selesai.
                                </p>

                                <div class="mt-4 p-3 bg-white/80 rounded-xl border border-amber-200 grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                                    <div>
                                        <span class="text-slate-400 block font-semibold">POLI</span>
                                        <span class="font-bold text-slate-800 text-sm">
                                            {{ $antrianAktif->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold">DOKTER</span>
                                        <span class="font-bold text-slate-800 text-sm">
                                            {{ $antrianAktif->jadwalPeriksa->dokter->nama ?? '-' }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block font-semibold">NO. ANTRIAN</span>
                                        <span class="font-extrabold text-indigo-600 text-base">
                                            #{{ $antrianAktif->no_antrian }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <a href="{{ route('pasien.riwayat.index') }}" class="btn btn-sm bg-amber-600 hover:bg-amber-700 text-white rounded-lg">
                                        <i class="fas fa-clock-rotate-left mr-1"></i> Lihat di Riwayat Pendaftaran
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- CARD FORM PENDAFTARAN --}}
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-8">

                    <div class="text-center mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-2 text-xl">
                            <i class="fas fa-hospital-user"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-800">
                            Pendaftaran Poli
                        </h2>
                        <p class="text-xs text-slate-500">Pilih poli dan jadwal dokter yang tersedia</p>
                    </div>

                    {{-- Form Pendaftaran --}}
                    <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                        @csrf

                        {{-- Nomor RM --}}
                        <div class="mb-4">
                            <label class="label pb-1">
                                <span class="text-sm font-semibold text-slate-700">Nomor Rekam Medis (No. RM)</span>
                            </label>
                            <input type="text" value="{{ $user->no_rm }}"
                                class="input input-bordered w-full rounded-xl bg-slate-100 font-mono font-bold text-slate-700" readonly>
                        </div>

                        {{-- Pilih Poli --}}
                        <div class="mb-4">
                            <label class="label pb-1">
                                <span class="text-sm font-semibold text-slate-700">Pilih Poli <span class="text-red-500">*</span></span>
                            </label>
                            <select name="id_poli" id="poliSelect" class="select select-bordered w-full rounded-xl" {{ $antrianAktif ? 'disabled' : '' }} required>
                                <option value="">-- Pilih Poli Tujuan --</option>
                                @foreach ($polis as $poli)
                                <option value="{{ $poli->id }}">
                                    {{ $poli->nama_poli }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pilih Jadwal --}}
                        <div class="mb-4">
                            <label class="label pb-1">
                                <span class="text-sm font-semibold text-slate-700">Pilih Jadwal Periksa <span class="text-red-500">*</span></span>
                            </label>
                            <select name="id_jadwal" id="jadwalSelect" class="select select-bordered w-full rounded-xl" {{ $antrianAktif ? 'disabled' : '' }} required>
                                <option value="">-- Pilih Jadwal Dokter --</option>
                                @foreach ($jadwals as $jadwal)
                                <option value="{{ $jadwal->id }}" data-poli="{{ $jadwal->dokter->id_poli }}">
                                    {{ $jadwal->hari }} ({{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}) • Dr. {{ $jadwal->dokter->nama ?? '--' }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Keluhan --}}
                        <div class="mb-6">
                            <label class="label pb-1">
                                <span class="text-sm font-semibold text-slate-700">Keluhan Pasien</span>
                            </label>
                            <textarea name="keluhan" rows="3" class="textarea textarea-bordered w-full rounded-xl"
                                placeholder="Jelaskan gejala atau keluhan yang dirasakan..." {{ $antrianAktif ? 'disabled' : '' }}>{{ old('keluhan') }}</textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit"
                                class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-8 font-semibold shadow-sm transition"
                                {{ $antrianAktif ? 'disabled' : '' }}>
                                <i class="fas fa-paper-plane mr-2"></i>
                                {{ $antrianAktif ? 'Pendaftaran Dikunci (Masih Ada Antrian)' : 'Daftar Poli' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const poliSelect = document.getElementById('poliSelect');
            const jadwalSelect = document.getElementById('jadwalSelect');
            const allJadwalOptions = Array.from(jadwalSelect.querySelectorAll('option')).slice(1);

            poliSelect.addEventListener('change', function () {
                const selectedPoliId = this.value;
                jadwalSelect.value = '';

                allJadwalOptions.forEach(opt => {
                    if (!selectedPoliId || opt.getAttribute('data-poli') === selectedPoliId) {
                        opt.style.display = '';
                    } else {
                        opt.style.display = 'none';
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts.app>
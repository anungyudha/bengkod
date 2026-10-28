<x-layouts.app title="Verifikasi Pembayaran">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Verifikasi Pembayaran Pasien
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Kelola dan verifikasi bukti pembayaran pemeriksaan pasien.
            </p>
        </div>
    </div>

    {{-- Stat Cards --}}
    @php
        $menungguCount = $pembayarans->where('status_pembayaran', 'menunggu_verifikasi')->count();
        $belumBayarCount = $pembayarans->where('status_pembayaran', 'belum_bayar')->count();
        $lunasCount = $pembayarans->where('status_pembayaran', 'lunas')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-sm border border-slate-200 rounded-2xl p-5 flex flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Menunggu Verifikasi</span>
                <span class="text-2xl font-extrabold text-slate-800">{{ $menungguCount }}</span>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-slate-200 rounded-2xl p-5 flex flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-circle-exclamation"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Belum Bayar</span>
                <span class="text-2xl font-extrabold text-slate-800">{{ $belumBayarCount }}</span>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm border border-slate-200 rounded-2xl p-5 flex flex-row items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold">
                <i class="fas fa-circle-check"></i>
            </div>
            <div>
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Lunas</span>
                <span class="text-2xl font-extrabold text-slate-800">{{ $lunasCount }}</span>
            </div>
        </div>
    </div>

    {{-- Tabel Pembayaran --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Pasien</th>
                        <th class="py-4 px-6">Pemeriksaan</th>
                        <th class="py-4 px-6">Total Biaya</th>
                        <th class="py-4 px-6 text-center">Bukti Transfer</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($pembayarans as $index => $item)
                        <tr class="hover:bg-slate-50/60 transition {{ $item->status_pembayaran === 'menunggu_verifikasi' ? 'bg-amber-50/30' : '' }}">
                            <td class="py-4 px-6 text-center font-medium text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">
                                    {{ $item->daftarPoli->pasien->nama ?? 'Pasien' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    RM: <span class="font-mono font-semibold">{{ $item->daftarPoli->pasien->no_rm ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-700">
                                    {{ $item->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Dr. {{ $item->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }} • {{ \Carbon\Carbon::parse($item->tgl_periksa)->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-extrabold text-base text-indigo-600">
                                    Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($item->bukti_pembayaran)
                                    <button onclick="document.getElementById('modal-bukti-{{ $item->id }}').showModal()"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-100 text-xs font-semibold transition">
                                        <i class="fas fa-image"></i>
                                        Lihat Bukti
                                    </button>

                                    {{-- Modal Preview Foto Bukti --}}
                                    <dialog id="modal-bukti-{{ $item->id }}" class="modal text-left">
                                        <div class="modal-box rounded-2xl max-w-lg p-6">
                                            <form method="dialog">
                                                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                                            </form>
                                            <h3 class="font-bold text-lg text-slate-800 mb-2">
                                                Bukti Pembayaran - {{ $item->daftarPoli->pasien->nama ?? 'Pasien' }}
                                            </h3>
                                            <p class="text-xs text-slate-500 mb-4">
                                                Total Tagihan: <span class="font-bold text-indigo-600">Rp {{ number_format($item->biaya_periksa, 0, ',', '.') }}</span>
                                            </p>

                                            <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-100 flex items-center justify-center max-h-96">
                                                <img src="{{ asset('storage/' . $item->bukti_pembayaran) }}" 
                                                     alt="Bukti Transfer" 
                                                     class="max-h-96 w-auto object-contain">
                                            </div>

                                            <div class="mt-4 flex justify-between items-center">
                                                <a href="{{ asset('storage/' . $item->bukti_pembayaran) }}" target="_blank" class="btn btn-sm btn-outline rounded-lg text-xs">
                                                    <i class="fas fa-arrow-up-right-from-square mr-1"></i> Buka Gambar Asli
                                                </a>
                                                @if ($item->status_pembayaran !== 'lunas')
                                                    <form action="{{ route('admin.pembayaran.konfirmasi', $item->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" 
                                                                onclick="return confirm('Konfirmasi bahwa pembayaran ini sudah benar dan valid?')" 
                                                                class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                                                            <i class="fas fa-check-circle mr-1"></i> Verifikasi Sekarang
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button>close</button>
                                        </form>
                                    </dialog>
                                @else
                                    <span class="text-xs text-slate-400 italic">Belum diunggah</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($item->status_pembayaran === 'lunas')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-circle-check text-[10px]"></i> Lunas
                                    </span>
                                @elseif ($item->status_pembayaran === 'menunggu_verifikasi')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i class="fas fa-hourglass-half text-[10px]"></i> Menunggu Verifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                        <i class="fas fa-circle-exclamation text-[10px]"></i> Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($item->status_pembayaran !== 'lunas')
                                    <form action="{{ route('admin.pembayaran.konfirmasi', $item->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <button type="submit" 
                                                onclick="return confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini sebagai LUNAS?')" 
                                                class="btn btn-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm"
                                                {{ !$item->bukti_pembayaran ? 'disabled title=Bukti_belum_diupload' : '' }}>
                                            <i class="fas fa-check mr-1"></i> Konfirmasi
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-emerald-600 font-semibold flex items-center justify-center gap-1">
                                        <i class="fas fa-badge-check"></i> Terverifikasi
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-14 text-slate-400">
                                <i class="fas fa-receipt text-4xl mb-3 block text-slate-300"></i>
                                Belum ada tagihan pemeriksaan pasien.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>


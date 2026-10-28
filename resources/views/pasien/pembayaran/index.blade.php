<x-layouts.app title="Pembayaran & Tagihan">

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Pembayaran & Tagihan
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Lihat rincian tagihan pemeriksaan Anda dan unggah bukti transfer pembayaran.
            </p>
        </div>
        <a href="{{ route('pasien.riwayat.index') }}" class="btn btn-outline border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl">
            <i class="fas fa-clock-rotate-left mr-1"></i> Riwayat Pendaftaran
        </a>
    </div>

    {{-- Tabel Tagihan --}}
    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">No</th>
                        <th class="py-4 px-6">Poli & Dokter</th>
                        <th class="py-4 px-6">Tanggal Periksa</th>
                        <th class="py-4 px-6">Total Tagihan</th>
                        <th class="py-4 px-6 text-center">Status Pembayaran</th>
                        <th class="py-4 px-6 text-center w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($tagihans as $index => $tagihan)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6 text-center font-medium text-slate-500">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-bold text-slate-800">
                                    {{ $tagihan->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Dr. {{ $tagihan->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <div class="font-semibold text-slate-700">
                                    {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    Pukul {{ \Carbon\Carbon::parse($tagihan->tgl_periksa)->format('H:i') }} WIB
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-extrabold text-base text-indigo-600">
                                    Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}
                                </span>
                                <div class="text-[11px] text-slate-400">
                                    Jasa dokter + {{ $tagihan->detailPeriksas->count() }} obat
                                </div>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if ($tagihan->status_pembayaran === 'lunas')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fas fa-circle-check text-[10px]"></i> Lunas
                                    </span>
                                @elseif ($tagihan->status_pembayaran === 'menunggu_verifikasi')
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
                                <div class="flex items-center justify-center gap-2">
                                    @if ($tagihan->status_pembayaran === 'lunas')
                                        @if ($tagihan->bukti_pembayaran)
                                            <a href="{{ asset('storage/' . $tagihan->bukti_pembayaran) }}" target="_blank"
                                               class="btn btn-sm btn-outline border-slate-300 text-slate-600 hover:bg-slate-100 rounded-lg">
                                                <i class="fas fa-receipt mr-1"></i> Bukti
                                            </a>
                                        @endif
                                        <a href="{{ route('pasien.riwayat.show', $tagihan->id_daftar_poli) }}"
                                           class="btn btn-sm bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                                            Rincian
                                        </a>
                                    @else
                                        {{-- Tombol Buka Modal Upload --}}
                                        <button onclick="document.getElementById('modal-upload-{{ $tagihan->id }}').showModal()"
                                                class="btn btn-sm {{ $tagihan->status_pembayaran === 'menunggu_verifikasi' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-lg shadow-sm">
                                            <i class="fas fa-upload mr-1"></i>
                                            {{ $tagihan->status_pembayaran === 'menunggu_verifikasi' ? 'Ubah Bukti' : 'Upload Bukti' }}
                                        </button>

                                        @if ($tagihan->bukti_pembayaran)
                                            <a href="{{ asset('storage/' . $tagihan->bukti_pembayaran) }}" target="_blank"
                                               class="btn btn-sm btn-ghost rounded-lg text-slate-500" title="Lihat Foto Bukti">
                                                <i class="fas fa-image"></i>
                                            </a>
                                        @endif
                                    @endif
                                </div>

                                {{-- MODAL UPLOAD BUKTI PEMBAYARAN --}}
                                <dialog id="modal-upload-{{ $tagihan->id }}" class="modal text-left">
                                    <div class="modal-box rounded-2xl max-w-md">
                                        <form method="dialog">
                                            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
                                        </form>

                                        <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                                            <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                                            Upload Bukti Pembayaran
                                        </h3>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Silakan transfer total biaya pemeriksaan dan unggah bukti transfer Anda.
                                        </p>

                                        <div class="my-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                                            <div class="flex justify-between items-center text-sm mb-1">
                                                <span class="text-slate-500">Poli Tujuan:</span>
                                                <span class="font-bold text-slate-800">{{ $tagihan->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm mb-1">
                                                <span class="text-slate-500">Dokter:</span>
                                                <span class="font-bold text-slate-800">Dr. {{ $tagihan->daftarPoli->jadwalPeriksa->dokter->nama ?? '-' }}</span>
                                            </div>
                                            <div class="divider my-2"></div>
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold text-slate-700">Total Tagihan:</span>
                                                <span class="font-extrabold text-lg text-indigo-600">
                                                    Rp {{ number_format($tagihan->biaya_periksa, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>

                                        <form action="{{ route('pasien.pembayaran.upload', $tagihan->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-control mb-5">
                                                <label class="label pb-1.5" for="bukti_{{ $tagihan->id }}">
                                                    <span class="text-sm font-semibold text-slate-700">
                                                        Pilih Foto / Gambar Bukti Transfer <span class="text-red-500">*</span>
                                                    </span>
                                                </label>
                                                <input type="file" 
                                                       name="bukti_pembayaran" 
                                                       id="bukti_{{ $tagihan->id }}" 
                                                       accept="image/*" 
                                                       required
                                                       class="file-input file-input-bordered w-full rounded-xl">
                                                <span class="text-[11px] text-slate-400 mt-1">
                                                    Format: JPG, JPEG, PNG, WEBP (Maksimal 3MB).
                                                </span>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="document.getElementById('modal-upload-{{ $tagihan->id }}').close()" class="btn btn-ghost rounded-xl">
                                                    Batal
                                                </button>
                                                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-sm">
                                                    <i class="fas fa-upload mr-1"></i> Unggah Bukti
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-14 text-slate-400">
                                <i class="fas fa-receipt text-4xl mb-3 block text-slate-300"></i>
                                Belum ada tagihan pemeriksaan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-layouts.app>


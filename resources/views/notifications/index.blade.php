<x-layouts.app title="Notifikasi">

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">
                Notifikasi
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                Daftar pemberitahuan aktivitas akun, pemeriksaan, dan pembayaran.
            </p>
        </div>

        @if (auth()->user()->unreadNotifications->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl">
                    <i class="fas fa-check-double mr-1.5 text-indigo-600"></i>
                    Tandai Semua Sudah Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = $notification->unread();
                    $data = $notification->data;
                @endphp
                <div class="p-5 flex items-start gap-4 transition {{ $isUnread ? 'bg-indigo-50/40 border-l-4 border-indigo-600' : 'hover:bg-slate-50/60' }}">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $isUnread ? 'bg-indigo-100 text-indigo-700 font-bold' : 'bg-slate-100 text-slate-500' }}">
                        @if (($data['type'] ?? '') === 'pembayaran_diverifikasi')
                            <i class="fas fa-circle-check text-emerald-600"></i>
                        @elseif (($data['type'] ?? '') === 'pembayaran_dibuat')
                            <i class="fas fa-file-invoice-dollar text-indigo-600"></i>
                        @else
                            <i class="fas fa-bell"></i>
                        @endif
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="font-bold text-sm text-slate-800 {{ $isUnread ? 'font-extrabold text-indigo-950' : '' }}">
                                {{ $data['title'] ?? 'Pemberitahuan' }}
                            </h4>
                            <span class="text-xs text-slate-400 whitespace-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                            {{ $data['message'] ?? '-' }}
                        </p>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        @if ($isUnread)
                            <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg" title="Tandai Sudah Dibaca">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        @endif
                        @if (!empty($data['url']))
                            <a href="{{ $data['url'] }}" class="btn btn-xs btn-outline rounded-lg" title="Buka Rincian">
                                <i class="fas fa-arrow-up-right-from-square"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-16 text-slate-400">
                    <i class="fas fa-bell-slash text-4xl mb-3 block text-slate-300"></i>
                    <p class="text-base font-semibold text-slate-600">Belum Ada Notifikasi</p>
                    <p class="text-xs text-slate-400 mt-1">Anda akan menerima notifikasi di sini saat ada pembaruan status.</p>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>


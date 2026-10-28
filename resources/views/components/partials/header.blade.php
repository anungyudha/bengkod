<header class="bg-base-100 border-b border-base-300 h-16 flex items-center px-6 gap-3 sticky top-0 z-30 shadow-sm">

    {{-- Mobile Hamburger --}}
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars w-5 h-5"></i>
    </button>

    {{-- Breadcrumb --}}
    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm">
            <span class="text-base-content/50">Poliklinik</span>
            <i class="fas fa-chevron-right text-xs text-base-content/30"></i>
            <span class="font-semibold text-base-content">
                {{ $title ?? 'Dashboard' }}
            </span>
        </div>
    </div>

    {{-- Fullscreen --}}
    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost" title="Layar Penuh">
        <i id="fsIcon" class="fas fa-expand w-5 h-5"></i>
    </button>

    {{-- Notifikasi Dropdown --}}
    @php
        $unreadCount = auth()->user()?->unreadNotifications->count() ?? 0;
        $latestNotifications = auth()->user()?->notifications()->take(5)->get() ?? collect();
    @endphp
    <div class="dropdown dropdown-end">
        <button tabindex="0" class="btn btn-square btn-ghost relative" title="Notifikasi">
            <i class="fas fa-bell w-5 h-5 text-base-content/80"></i>
            @if ($unreadCount > 0)
                <span class="badge badge-xs badge-error absolute top-2 right-2 p-1 font-bold text-[10px] text-white">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            @endif
        </button>
        <div tabindex="0" class="dropdown-content z-[40] menu p-3 shadow-xl bg-base-100 rounded-2xl w-80 sm:w-96 border border-slate-200 mt-2">
            <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
                <span class="font-bold text-sm text-slate-800">
                    Notifikasi
                    @if ($unreadCount > 0)
                        <span class="badge badge-sm badge-primary ml-1">{{ $unreadCount }} baru</span>
                    @endif
                </span>
                <a href="{{ route('notifications.index') }}" class="text-xs text-primary hover:underline font-semibold">
                    Lihat Semua
                </a>
            </div>

            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 -mx-1">
                @forelse ($latestNotifications as $notif)
                    @php
                        $isUnread = $notif->unread();
                        $data = $notif->data;
                    @endphp
                    <div class="p-2.5 rounded-xl transition {{ $isUnread ? 'bg-indigo-50/60 font-medium' : 'hover:bg-slate-50' }}">
                        <div class="flex items-start justify-between gap-2">
                            <span class="text-xs font-bold text-slate-800 {{ $isUnread ? 'text-indigo-950 font-extrabold' : '' }}">
                                {{ $data['title'] ?? 'Pemberitahuan' }}
                            </span>
                            <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                {{ $notif->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">
                            {{ $data['message'] ?? '-' }}
                        </p>
                        @if (!empty($data['url']))
                            <div class="mt-1 text-right">
                                <a href="{{ route('notifications.read', $notif->id) }}" class="text-[11px] text-indigo-600 hover:underline font-semibold">
                                    Buka & Tandai Dibaca &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-6 text-slate-400">
                        <i class="fas fa-bell-slash text-2xl mb-1.5 block text-slate-300"></i>
                        <span class="text-xs">Belum ada notifikasi</span>
                    </div>
                @endforelse
            </div>

            @if ($unreadCount > 0)
                <div class="pt-2 mt-2 border-t border-slate-100 text-center">
                    <form action="{{ route('notifications.read-all') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-xs text-slate-500 hover:text-slate-800 font-semibold">
                            <i class="fas fa-check-double mr-1"></i> Tandai semua sudah dibaca
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- User Info (Direct Link to Profile) --}}
    <a href="{{ route('profile.edit') }}" 
       class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-base-200 transition group" 
       title="Profil & Ganti Password">

        <div class="text-right hidden sm:block">
            <div class="text-sm font-semibold leading-tight text-base-content group-hover:text-primary transition">
                {{ auth()->user()->nama ?? auth()->user()->name ?? 'Pengguna' }}
            </div>
            <div class="text-xs text-base-content/50 leading-tight capitalize">
                {{ auth()->user()->role ?? 'Admin Sistem' }}
            </div>
        </div>

        <div class="avatar">
            <div class="w-10 h-10 rounded-full bg-primary text-primary-content flex items-center justify-center shadow-sm">
                <span class="text-sm font-bold leading-none">
                    {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->name ?? 'U', 0, 1)) }}
                </span>
            </div>
        </div>
    </a>

</header>

<script>
    function toggleFullscreen() {
        const icon = document.getElementById('fsIcon');

        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        } else {
            document.exitFullscreen();
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        }
    }
</script>

<x-layouts.app title="Profil Saya">

    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-slate-800">
            Profil & Pengaturan Akun
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            Kelola data profil dan perbarui kata sandi akun Anda.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kartu Info Profil --}}
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-6 text-center">

                    <div class="avatar mx-auto mb-3">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 text-white flex items-center justify-center text-3xl font-bold shadow-md">
                            {{ strtoupper(substr($user->nama ?? $user->name ?? 'U', 0, 1)) }}
                        </div>
                    </div>

                    <h3 class="text-lg font-bold text-slate-800 leading-tight">
                        {{ $user->nama ?? $user->name ?? 'Pengguna' }}
                    </h3>
                    <p class="text-xs text-slate-500 mb-3">
                        {{ $user->email }}
                    </p>

                    <div>
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                <i class="fas fa-shield-halved text-[10px]"></i> Administrator
                            </span>
                        @elseif($user->role === 'dokter')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                <i class="fas fa-user-doctor text-[10px]"></i> Dokter {{ $user->poli ? '• ' . $user->poli->nama_poli : '' }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-hospital-user text-[10px]"></i> Pasien
                            </span>
                        @endif
                    </div>

                    <div class="divider my-4"></div>

                    <div class="text-left space-y-3 text-sm">
                        @if($user->role === 'pasien' && $user->no_rm)
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">No. Rekam Medis</span>
                                <span class="font-semibold text-slate-700">{{ $user->no_rm }}</span>
                            </div>
                        @endif

                        @if($user->no_ktp)
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">No. KTP</span>
                                <span class="font-medium text-slate-700">{{ $user->no_ktp }}</span>
                            </div>
                        @endif

                        @if($user->no_hp)
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">No. Handphone</span>
                                <span class="font-medium text-slate-700">{{ $user->no_hp }}</span>
                            </div>
                        @endif

                        @if($user->alamat)
                            <div>
                                <span class="text-xs font-semibold text-slate-400 block uppercase tracking-wider">Alamat</span>
                                <span class="font-medium text-slate-700">{{ $user->alamat }}</span>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- Form Ganti Password --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-sm rounded-2xl border border-slate-200">
                <div class="card-body p-6 sm:p-8">

                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="fas fa-key text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 leading-tight">
                                Ganti Password
                            </h3>
                            <p class="text-xs text-slate-500">
                                Amankan akun Anda dengan mengganti password secara berkala.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- 1. Password Lama --}}
                        <div class="form-control mb-5">
                            <label class="label pb-1.5" for="current_password">
                                <span class="text-sm font-semibold text-slate-700">
                                    Password Lama <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="current_password" 
                                       id="current_password" 
                                       required
                                       placeholder="Masukkan password lama akun Anda" 
                                       class="input input-bordered w-full rounded-xl pr-10 @error('current_password') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">
                                <button type="button" 
                                        onclick="toggleVisibility('current_password', this)"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1.5 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- 2. Password Baru --}}
                        <div class="form-control mb-5">
                            <label class="label pb-1.5" for="password">
                                <span class="text-sm font-semibold text-slate-700">
                                    Password Baru <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       id="password" 
                                       required
                                       placeholder="Minimal 8 karakter" 
                                       class="input input-bordered w-full rounded-xl pr-10 @error('password') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">
                                <button type="button" 
                                        onclick="toggleVisibility('password', this)"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                            <span class="text-xs text-slate-400 mt-1">
                                Password baru harus minimal 8 karakter dan tidak boleh sama dengan password lama.
                            </span>
                            @error('password')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1.5 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- 3. Konfirmasi Password Baru --}}
                        <div class="form-control mb-6">
                            <label class="label pb-1.5" for="password_confirmation">
                                <span class="text-sm font-semibold text-slate-700">
                                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                                </span>
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="password_confirmation" 
                                       id="password_confirmation" 
                                       required
                                       placeholder="Ulangi password baru Anda" 
                                       class="input input-bordered w-full rounded-xl pr-10 @error('password_confirmation') border-red-500 focus:border-red-500 focus:ring-red-200 @enderror">
                                <button type="button" 
                                        onclick="toggleVisibility('password_confirmation', this)"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <i class="fas fa-eye text-sm"></i>
                                </button>
                            </div>
                            @error('password_confirmation')
                                <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1.5 font-medium">
                                    <i class="fas fa-circle-exclamation"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="flex justify-end pt-2">
                            <button type="submit" 
                                    class="btn bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-6 font-medium shadow-sm transition">
                                <i class="fas fa-floppy-disk mr-2"></i>
                                Simpan Password Baru
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function toggleVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    @endpush

</x-layouts.app>


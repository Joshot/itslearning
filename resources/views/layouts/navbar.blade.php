<nav style="background: #234e7f" class="text-white p-2 flex justify-between items-center">
    <!-- Logo -->
    <div>
        <img src="/images/logo_kecil_putih.png" alt="Logo" class="h-12 pl-6">
    </div>

    <!-- Icons (Notifikasi & Chat) -->
    <div class="flex items-center pr-6 space-x-4">
        <div class="relative">
            <svg class="h-6 w-6 text-white cursor-pointer" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2a7 7 0 00-7 7v3.17a3 3 0 00-.83 2.07V16a3 3 0 003 3h10a3 3 0 003-3v-1.76a3 3 0 00-.83-2.07V9a7 7 0 00-7-7zM9 19a3 3 0 006 0z" />
            </svg>
        </div>

        <div class="relative">
            <svg class="h-6 w-6 text-white cursor-pointer" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21 6.5a2.5 2.5 0 00-2.5-2.5h-13A2.5 2.5 0 003 6.5v7A2.5 2.5 0 005.5 16H8v3l4-3h6.5A2.5 2.5 0 0021 13.5v-7z" />
            </svg>
        </div>
        <!-- Nama Pengguna -->
        <span class="text-white font-semibold">{{ Auth::guard('student')->user()->name ?? 'Guest' }}</span>
    </div>
</nav>

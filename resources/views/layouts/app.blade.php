<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'UMN Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-image: url('/images/background_logo.jpg'); background-size: cover; background-position: center;" class="flex flex-col min-h-screen">
<nav style="background: #234e7f" class="text-white p-2 flex justify-between items-center">
    <div>
        <img src="/images/logo_kecil_putih.png" alt="Logo" class="h-12 pl-6">
    </div>
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
        @if (Auth::guard('lecturer')->check())
        <span class="text-white font-semibold">{{ Auth::guard('lecturer')->user()->name ?? 'Guest' }}</span>
        <form action="{{ route('lecturer.logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-white text-xs">Logout</button>
        </form>
        @elseif (Auth::guard('student')->check())
        <span class="text-white font-semibold">{{ Auth::guard('student')->user()->name ?? 'Guest' }}</span>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-white text-xs">Logout</button>
        </form>
        @else
        <a href="{{ route('login') }}" class="text-white text-xs">Student Login</a>
        <a href="{{ route('lecturer.login') }}" class="text-white text-xs">Lecturer Login</a>
        @endif
    </div>
</nav>

<main class="flex-grow p-8">
    @yield('content')
    @stack('scripts')
</main>

@include('layouts.footer')

</body>
</html>

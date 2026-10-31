<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-neutral-50 text-dark-mahogany antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel') — {{ config('app.name', 'Chifheto') }}</title>

    <!-- Theme Color -->
    <meta name="theme-color" content="#0C0C0C">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-neutral-50 text-dark-mahogany selection:bg-safety-orange selection:text-industry-black">
    <!-- Admin Header / Navbar -->
    <header class="bg-industry-black border-b border-dark-mahogany sticky top-0 z-40 shadow-sm text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.articles.index') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-burnt-brick border border-burnt-brick/80 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="font-extrabold text-sm text-white tracking-tight block leading-none uppercase">
                                TRAINING <span class="text-safety-orange">ALAT BERAT</span>
                            </span>
                            <span class="text-[10px] uppercase tracking-wider text-neutral-300 font-semibold">
                                Admin CMS
                            </span>
                        </div>
                    </a>

                    <!-- Nav items -->
                    <nav class="hidden md:flex items-center space-x-1 pl-4 border-l border-neutral-700">
                        <a href="{{ route('admin.articles.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.articles.index') ? 'bg-dark-mahogany text-safety-orange border border-burnt-brick' : 'text-neutral-300 hover:text-white hover:bg-neutral-800' }} transition">
                            Semua Artikel
                        </a>
                        <a href="{{ route('admin.articles.create') }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.articles.create') ? 'bg-dark-mahogany text-safety-orange border border-burnt-brick' : 'text-neutral-300 hover:text-white hover:bg-neutral-800' }} transition">
                            + Tulis / AI Generator
                        </a>
                    </nav>
                </div>

                <!-- Right items: View site, User, Logout -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" target="_blank" class="text-xs text-neutral-300 hover:text-safety-orange flex items-center gap-1 font-medium transition">
                        <span>Lihat Website</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>

                    <div class="h-4 w-px bg-neutral-700"></div>

                    <span class="text-xs font-medium text-neutral-300 hidden sm:inline">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </span>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-neutral-300 hover:bg-dark-mahogany hover:text-white border border-neutral-700 transition cursor-pointer">
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-grow py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-white border border-neutral-200 text-xs font-medium text-dark-mahogany flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-safety-orange shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-white border border-burnt-brick/40 text-xs font-medium text-burnt-brick flex items-center gap-2 shadow-xs">
                <svg class="w-4 h-4 text-burnt-brick shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-white border border-burnt-brick/40 text-xs text-burnt-brick space-y-2 shadow-xs">
                <div class="flex items-center gap-2 font-bold text-burnt-brick">
                    <svg class="w-4 h-4 text-burnt-brick shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                    <span>Terdapat beberapa kesalahan pengisian form:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6 text-dark-mahogany">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Admin Footer -->
    <footer class="bg-white border-t border-neutral-200 py-4 text-center text-xs text-neutral-500">
        {{ config('app.name', 'Chifheto') }} &mdash; Administrator Panel &copy; {{ date('Y') }}
    </footer>

    @stack('scripts')
</body>
</html>

@extends('layouts.app')

@section('title', 'Login Administrator — ' . config('app.name', 'Chifheto'))
@section('meta_description', 'Masuk ke panel manajemen administrator ' . config('app.name', 'Chifheto') . '.')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-neutral-50">
    <div class="max-w-md w-full space-y-8 bg-white p-8 sm:p-10 rounded-2xl border border-neutral-200 shadow-md">
        <div class="text-center space-y-2">
            <div class="inline-flex w-12 h-12 rounded-xl bg-burnt-brick/10 text-burnt-brick items-center justify-center shadow-inner mb-2 border border-burnt-brick/20">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2L3 7v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-9-5z"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>
            <h1 class="text-2xl font-extrabold text-industry-black tracking-tight">
                Admin Panel Login
            </h1>
            <p class="text-xs text-dark-mahogany">
                Akses terbatas untuk administrator {{ config('app.name', 'Chifheto') }}
            </p>
        </div>

        @if(session('status'))
            <div class="p-3.5 rounded-xl bg-neutral-100 border border-neutral-300 text-xs text-dark-mahogany font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-xl bg-neutral-100 border border-burnt-brick/40 text-xs text-burnt-brick font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-xs font-bold text-industry-black mb-1.5 uppercase tracking-wider">
                    Alamat Email Admin
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                    value="{{ old('email') }}"
                    placeholder="admin@example.com"
                    class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 text-sm text-industry-black bg-white focus:outline-none focus:ring-2 focus:ring-safety-orange focus:border-safety-orange transition"
                >
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-industry-black mb-1.5 uppercase tracking-wider">
                    Password
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    placeholder="••••••••"
                    class="w-full px-4 py-2.5 rounded-lg border border-neutral-300 text-sm text-industry-black bg-white focus:outline-none focus:ring-2 focus:ring-safety-orange focus:border-safety-orange transition"
                >
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-dark-mahogany">
                    <input
                        type="checkbox"
                        name="remember"
                        class="rounded border-neutral-300 text-safety-orange focus:ring-safety-orange"
                    >
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <div>
                <button
                    type="submit"
                    class="w-full py-3 px-4 rounded-lg bg-safety-orange hover:bg-burnt-brick text-industry-black hover:text-white font-extrabold text-sm shadow-md transition flex items-center justify-center gap-2 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-safety-orange focus-visible:ring-offset-2"
                >
                    Masuk ke Admin Panel
                </button>
            </div>
        </form>

        <div class="text-center pt-3 border-t border-neutral-200">
            <a href="{{ route('home') }}" class="text-xs font-bold text-burnt-brick hover:text-safety-orange transition">
                &larr; Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection

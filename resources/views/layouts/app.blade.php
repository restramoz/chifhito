<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F4F6F8] text-[#111318] antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name', 'Najkaltare') }}</title>
    <meta name="description" content="@yield('meta_description', 'Katalog layanan dan pelatihan profesional ' . config('app.name', 'Najkaltare') . '. Informasi lengkap puluhan kategori dan ribuan layanan industri terpercaya.')">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Meta -->
    <meta property="og:title" content="@hasSection('title')@yield('title') — @endif{{ config('app.name', 'Najkaltare') }}">
    <meta property="og:description" content="@yield('meta_description', 'Katalog layanan dan pelatihan profesional ' . config('app.name', 'Najkaltare') . '. Konsultasikan kebutuhan industri Anda bersama tim kami.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Theme Color -->
    <meta name="theme-color" content="#111318">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @hasSection('robots')
    <meta name="robots" content="@yield('robots')">
    @endif

    @stack('schema')
    @stack('styles')
</head>
<body class="min-h-full flex flex-col bg-[#F4F6F8] text-[#111318] antialiased selection:bg-[#00C2A8] selection:text-[#111318]">
    <!-- Accessible Skip Link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 z-50 bg-[#00C2A8] text-[#111318] font-bold px-4 py-2 rounded-md shadow-lg outline-none ring-2 ring-[#111318]">
        Lewati ke konten utama
    </a>

    <!-- Site Header -->
    <x-header />

    <!-- Main Content Landmark -->
    <main id="main-content" class="flex-grow focus:outline-none" tabindex="-1">
        @if (isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    <!-- Site Footer -->
    <x-footer />

    <!-- Floating WhatsApp CTA -->
    <x-wa-float :message="View::hasSection('wa_message') ? View::getSection('wa_message') : null" />
</body>
</html>

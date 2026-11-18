<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Dharma Group IG Dashboard') }} — Autentikasi</title>

        <!-- Google Fonts: Inter -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
            }
        </style>
    </head>
    <body class="text-gray-900 antialiased bg-slate-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 p-4">
            <div class="mb-4">
                <a href="/">
                    <x-application-logo />
                </a>
            </div>

            <div class="w-full sm:max-w-md bg-white border border-gray-200/80 shadow-xl overflow-hidden rounded-2xl p-6 sm:p-8">
                {{ $slot }}
            </div>

            <div class="mt-6 text-center text-xs text-gray-400">
                &copy; {{ date('Y') }} PT Dharma Polimetal Tbk. All rights reserved.
            </div>
        </div>
    </body>
</html>

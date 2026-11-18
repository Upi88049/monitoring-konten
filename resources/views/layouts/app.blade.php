<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', isset($header) ? strip_tags($header) . ' — ' : '') Dharma Group IG Dashboard</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Apache ECharts -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f8f9fa;
        }
        .echart-container {
            width: 100%;
            height: 340px;
        }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="text-gray-800 antialiased min-h-screen flex flex-col">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 shrink-0 flex flex-col fixed inset-y-0 left-0 z-40 lg:static transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0">
            <!-- Brand Logo -->
            <div class="h-18 px-6 flex items-center justify-between border-b border-gray-100">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-700 flex items-center justify-center text-white font-bold shadow-md shadow-purple-200">
                        DG
                    </div>
                    <div>
                        <div class="font-extrabold text-gray-900 tracking-tight text-sm">DHARMA GROUP</div>
                        <div class="text-[11px] text-gray-500 font-medium">IG Content Analytics</div>
                    </div>
                </a>
                <button id="closeSidebarBtn" type="button" class="lg:hidden text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
                <div class="px-3 pt-2 pb-1 text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Menu Utama</div>

                @can('dashboard.view')
                <div x-data="{ open: {{ request()->routeIs('dashboard*') || request()->is('/') ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors cursor-pointer {{ request()->routeIs('dashboard*') || request()->is('/') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ request()->routeIs('dashboard*') || request()->is('/') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            <span>KPI Dashboard</span>
                        </div>
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="pl-4 pr-1 py-1 space-y-1">
                        <!-- Sub Menu 1 -->
                        <a href="{{ route('dashboard.overview') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-xs transition-colors {{ request()->routeIs('dashboard.overview') || (request()->routeIs('dashboard') && !request()->routeIs('dashboard.composition')) || request()->is('/') ? 'bg-purple-100/70 text-purple-900 font-bold shadow-xs' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('dashboard.overview') || (request()->routeIs('dashboard') && !request()->routeIs('dashboard.composition')) || request()->is('/') ? 'bg-purple-700' : 'bg-gray-300' }}"></span>
                            <span>Performa & Tren Konten</span>
                        </a>

                        <!-- Sub Menu 2 -->
                        <a href="{{ route('dashboard.composition') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg font-medium text-xs transition-colors {{ request()->routeIs('dashboard.composition') ? 'bg-purple-100/70 text-purple-900 font-bold shadow-xs' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('dashboard.composition') ? 'bg-purple-700' : 'bg-gray-300' }}"></span>
                            <span>Komposisi & Efektivitas Konten</span>
                        </a>
                    </div>
                </div>
                @endcan

                @can('content_logs.view')
                <a href="{{ route('content-logs.index') }}" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors {{ request()->routeIs('content-logs.*') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ request()->routeIs('content-logs.*') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <span>Master Log Konten</span>
                    </div>
                    @if(($pendingH30Count ?? 0) > 0)
                        <span class="bg-amber-100 text-amber-800 text-xs px-2 py-0.5 rounded-full font-bold">{{ $pendingH30Count }}</span>
                    @endif
                </a>
                @endcan

                @if(auth()->user()?->can('import.create') || auth()->user()?->can('export.view'))
                <div class="px-3 pt-6 pb-1 text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Integrasi & Laporan</div>

                @can('import.create')
                <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors text-left cursor-pointer">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Import Excel</span>
                </button>
                @endcan

                @can('export.view')
                <a href="{{ route('export.csv') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Export CSV / Excel</span>
                </a>
                @endcan
                @endif

                @if(auth()->user()?->can('roles.manage') || auth()->user()?->can('users.manage') || auth()->user()?->can('activity_logs.view'))
                <div class="px-3 pt-6 pb-1 text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Manajemen Sistem & Akses</div>

                @can('roles.manage')
                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors {{ request()->routeIs('roles.*') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('roles.*') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Kelola Role & Akses</span>
                </a>
                @endcan

                @can('users.manage')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors {{ request()->routeIs('users.*') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Kelola Pengguna</span>
                </a>
                @endcan

                @can('activity_logs.view')
                <a href="{{ route('activity-logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors {{ request()->routeIs('activity-logs.*') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('activity-logs.*') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Log Aktivitas</span>
                </a>
                @endcan
                @endif

                <div class="px-3 pt-6 pb-1 text-[11px] font-semibold tracking-wider text-gray-400 uppercase">Akun & Preferensi</div>

                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-colors {{ request()->routeIs('profile.*') ? 'bg-purple-50 text-purple-700 font-semibold shadow-xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-purple-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span>Profil Saya</span>
                </a>
            </nav>

            <!-- Bottom User & Company Info -->
            <div class="p-3 border-t border-gray-100 bg-gray-50/50 space-y-2">
                @auth
                <div class="rounded-xl p-3 bg-white border border-gray-200/70 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5 overflow-hidden">
                            <div class="w-8 h-8 rounded-full bg-purple-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <div class="overflow-hidden">
                                <div class="text-xs font-bold text-gray-900 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <div class="mt-2">
                        @if(Auth::user()->isSuperAdmin())
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md bg-purple-100 text-purple-800">
                                🛡️ Super Admin
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold tracking-wider px-2 py-0.5 rounded-md bg-gray-100 text-gray-700">
                                {{ Auth::user()->role?->label ?? 'User' }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-2 pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                        <a href="{{ route('profile.edit') }}" class="text-purple-700 hover:text-purple-900 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span>Profil</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-800 font-medium flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
                <div class="px-2 text-[11px] text-gray-400 flex items-center justify-between">
                    <span>PT Dharma Polimetal Tbk</span>
                    <span>HR Branding</span>
                </div>
            </div>
        </aside>

        <!-- Overlay backdrop for mobile sidebar -->
        <div id="sidebarBackdrop" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-18 bg-white border-b border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <button id="openSidebarBtn" type="button" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900 leading-tight">@yield('page_title', $header ?? 'Dashboard')</h1>
                        <p class="text-xs text-gray-500 hidden sm:block">PT Dharma Polimetal Tbk — Monitoring & Evaluasi H+30</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    @yield('topbar_actions')

                    @if(request()->routeIs('content-logs.*'))
                        @can('import.create')
                        <button type="button" onclick="document.getElementById('importModal').classList.remove('hidden')" class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <span>Import Excel</span>
                        </button>
                        @endcan

                        @can('content_logs.create')
                        <button type="button" onclick="document.getElementById('createContentModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-purple-700 text-white hover:bg-purple-800 shadow-sm transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>+ Tambah Konten</span>
                        </button>
                        @endcan
                    @endif

                    @auth
                    <!-- User Dropdown in Topbar -->
                    <div class="relative ml-1" x-data="{ open: false }">
                        <button @click="open = !open" type="button" class="flex items-center gap-2 p-1 rounded-lg hover:bg-gray-100 transition focus:outline-none cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-purple-700 text-white font-bold text-xs flex items-center justify-center">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            <svg class="w-4 h-4 text-gray-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</div>
                                <div class="text-[11px] text-gray-500 truncate">{{ Auth::user()->email }}</div>
                                <div class="mt-1">
                                    <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md {{ Auth::user()->isSuperAdmin() ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-700' }}">
                                        {{ Auth::user()->role?->label ?? 'User' }}
                                    </span>
                                </div>
                            </div>

                            @can('roles.manage')
                            <a href="{{ route('roles.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                                Kelola Role & Hak Akses
                            </a>
                            @endcan

                            @can('users.manage')
                            <a href="{{ route('users.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                                Kelola Pengguna
                            </a>
                            @endcan

                            @can('activity_logs.view')
                            <a href="{{ route('activity-logs.index') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                                Log Aktivitas
                            </a>
                            @endcan

                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-xs text-gray-700 hover:bg-purple-50 hover:text-purple-700">
                                Edit Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-xs text-red-600 hover:bg-red-50 cursor-pointer">
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                    @endauth
                </div>
            </header>

            <!-- Notification Messages -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if(session('success'))
                    <div class="flex items-center justify-between p-4 mb-3 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200" role="alert">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="flex items-center justify-between p-4 mb-3 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200" role="alert">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 mb-3 text-sm text-red-800 rounded-xl bg-red-50 border border-red-200" role="alert">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2 font-bold text-xs text-red-900">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>Terdapat kesalahan pengisian data:</span>
                            </div>
                            <button type="button" onclick="this.closest('[role=alert]').remove()" class="text-red-500 hover:text-red-700">&times;</button>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-red-700 pl-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>

            <!-- Footer -->
            <footer class="h-14 bg-white border-t border-gray-200 px-4 sm:px-6 lg:px-8 flex items-center justify-between text-xs text-gray-500">
                <div>&copy; {{ date('Y') }} PT Dharma Polimetal Tbk. All rights reserved.</div>
                <div class="flex items-center gap-4">
                    <span>IG Content & KPI Analytics v1.0 (Breeze Auth)</span>
                </div>
            </footer>
        </div>
    </div>

    <!-- Modal Import Excel -->
    <div id="importModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <h3 class="text-base font-bold text-gray-900">Import Data Excel</h3>
                <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            
            <div class="py-4 space-y-4">
                <!-- One Click Default Excel -->
                <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-100">
                    <div class="font-semibold text-xs text-purple-900 mb-1">Opsi 1: Sinkronisasi Master Spreadsheet Bawaan</div>
                    <p class="text-xs text-purple-700 mb-3">Otomatis membaca file <code class="bg-white px-1 py-0.5 rounded text-purple-900 font-mono">public/data/Copy_of_Master_Rekap_Konten_Dharma_Group_Copy.xlsx</code> (44 konten).</p>
                    <form action="{{ route('import.default') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            Sinkronisasi File Master Bawaan
                        </button>
                    </form>
                </div>

                <!-- Custom Upload -->
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="font-semibold text-xs text-gray-800 mb-1">Opsi 2: Upload File Excel Baru (.xlsx)</div>
                    <p class="text-xs text-gray-500 mb-3">Unggah spreadsheet rekapan konten Instagram terbaru yang memiliki lembar kerja <em>Log Konten</em>.</p>
                    <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="excel_file" accept=".xlsx" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 mb-3">
                        <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-lg shadow-xs transition">
                            Upload & Import Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Content Log -->
    <div id="createContentModal" onclick="if(event.target === this) this.classList.add('hidden')" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-gray-100 my-8">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-900">Tambah Konten Baru</h3>
                    <p class="text-xs text-gray-500">Jadwal evaluasi H+30 dan interaksi akan dihitung otomatis.</p>
                </div>
                <button type="button" onclick="document.getElementById('createContentModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>

            <form action="{{ route('content-logs.store') }}" method="POST" class="py-4 space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Posting *</label>
                        <input type="date" name="post_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Konten *</label>
                        <select name="content_type" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            @foreach(\App\Enums\ContentType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Judul / Topik Konten *</label>
                    <input type="text" name="title" placeholder="Contoh: Lowongan Management Trainee 2026" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tujuan / Pilar Konten *</label>
                        <select name="pillar" required class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                            @foreach(\App\Enums\ContentPillar::cases() as $pillar)
                                <option value="{{ $pillar->value }}">{{ $pillar->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama PIC Konten</label>
                        <input type="text" name="pic_name" placeholder="Nama PIC" class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Link Evidence Posting (Instagram URL)</label>
                    <input type="url" name="evidence_url" placeholder="https://www.instagram.com/p/..." class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none">
                    <span class="text-[11px] text-gray-400">Status Evidence akan otomatis berubah menjadi "Ada" jika link diisi.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                    <textarea name="notes" rows="2" placeholder="Catatan evaluasi atau brief..." class="w-full px-3 py-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('createContentModal').classList.add('hidden')" class="px-4 py-2 border border-gray-300 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-purple-700 hover:bg-purple-800 text-white text-xs font-bold rounded-lg shadow-sm">
                        Simpan Konten
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile sidebar script -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const openBtn = document.getElementById('openSidebarBtn');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);

        // Close modals and mobile sidebar on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                ['importModal', 'createContentModal', 'metricsModal', 'editModal', 'drilldownModal'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && !el.classList.contains('hidden')) {
                        el.classList.add('hidden');
                    }
                });
                closeSidebar();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

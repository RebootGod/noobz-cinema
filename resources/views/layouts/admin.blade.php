{{-- ======================================== --}}
{{-- ADMIN.BLADE.PHP - ADMIN LAYOUT --}}
{{-- ======================================== --}}
{{-- File: resources/views/layouts/admin.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Noobz Cinema')</title>
    
    {{-- Tailwind CDN Only --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- REMOVE THIS LINE - No more Vite --}}
    {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
    
    @stack('styles')
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <div class="flex">
        {{-- Sidebar Navigation --}}
        <aside class="w-64 bg-gray-800 min-h-screen">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-green-400">Admin Panel</h2>
            </div>
            
            <nav class="mt-6">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-6 py-3 hover:bg-gray-700 transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700 border-l-4 border-green-400' : '' }}">
                    <span>📊</span>
                    <span class="ml-3">Dashboard</span>
                </a>
                
                <a href="{{ route('admin.movies.index') }}" 
                   class="flex items-center px-6 py-3 hover:bg-gray-700 transition {{ request()->routeIs('admin.movies.*') ? 'bg-gray-700 border-l-4 border-green-400' : '' }}">
                    <span>🎬</span>
                    <span class="ml-3">Manage Movies</span>
                </a>
                
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center px-6 py-3 hover:bg-gray-700 transition {{ request()->routeIs('admin.users.*') ? 'bg-gray-700 border-l-4 border-green-400' : '' }}">
                    <span>👥</span>
                    <span class="ml-3">Manage Users</span>
                </a>
                
                <a href="{{ route('admin.invite-codes.index') }}" 
                   class="flex items-center px-6 py-3 hover:bg-gray-700 transition {{ request()->routeIs('admin.invite-codes.*') ? 'bg-gray-700 border-l-4 border-green-400' : '' }}">
                    <span>🎟️</span>
                    <span class="ml-3">Invite Codes</span>
                </a>

                <a href="{{ route('admin.reports.index') }}" 
                    class="flex items-center px-6 py-3 hover:bg-gray-700 transition {{ request()->routeIs('admin.reports.*') ? 'bg-gray-700 border-l-4 border-green-400' : '' }}">
                        <span>⚠️</span>
                        <span class="ml-3">Reports</span>
                </a>
                
                <div class="border-t border-gray-700 my-4"></div>
                
                <a href="{{ route('home') }}" 
                   class="flex items-center px-6 py-3 hover:bg-gray-700 transition">
                    <span>🌐</span>
                    <span class="ml-3">View Site</span>
                </a>
                
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" 
                            class="flex items-center w-full px-6 py-3 hover:bg-gray-700 transition text-left">
                        <span>🚪</span>
                        <span class="ml-3">Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1">
            {{-- Top Bar --}}
            <header class="bg-gradient-to-r from-green-400 to-green-500 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-black">Noobz Cinema</h1>
                    <div class="flex items-center space-x-4">
                        <span class="bg-white/20 text-black px-4 py-2 rounded-lg">
                            Admin: {{ auth()->user()->username }}
                        </span>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <div class="p-6">
                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="bg-green-500 text-white px-6 py-3 rounded-lg mb-6 flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-500 text-white px-6 py-3 rounded-lg mb-6 flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-500 text-black px-6 py-3 rounded-lg mb-6 flex items-center justify-between">
                        <span>{{ session('warning') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-4 text-black hover:text-gray-700">✕</button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
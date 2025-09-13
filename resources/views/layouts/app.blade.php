{{-- ======================================== --}}
{{-- APP.BLADE.PHP - MAIN LAYOUT WITH PROFILE --}}
{{-- ======================================== --}}
{{-- File: resources/views/layouts/app.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Noobz Cinema')</title>
    
    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    
    {{-- Custom Styles --}}
    <style>
        /* Dropdown menu visibility */
        .dropdown:hover .dropdown-menu {
            display: block;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-900 text-white min-h-screen">
    {{-- Navigation --}}
    <nav class="bg-gradient-to-r from-green-400 to-green-500 px-6 py-4">
        <div class="container mx-auto flex justify-between items-center">
            {{-- Logo --}}
            <a href="{{ route('home') }}" class="text-2xl font-bold text-black">
                Noobz Cinema
            </a>
            
            {{-- Navigation Items --}}
            <div class="flex items-center space-x-4">
                @auth
                    {{-- Watchlist Link --}}
                    <a href="{{ route('profile.watchlist') }}" 
                       class="text-black hover:text-white transition">
                        📋 Watchlist
                    </a>
                    
                    {{-- Admin Dashboard (if admin) --}}
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" 
                           class="bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg transition">
                            Admin Dashboard
                        </a>
                    @endif
                    
                    {{-- User Dropdown --}}
                    <div class="relative dropdown">
                        <button class="bg-red-400 hover:bg-red-500 text-black px-4 py-2 rounded-lg transition flex items-center space-x-2">
                            <span>{{ auth()->user()->username }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        
                        {{-- Dropdown Menu --}}
                        <div class="dropdown-menu hidden absolute right-0 mt-2 w-48 bg-gray-800 rounded-lg shadow-lg z-50">
                            <a href="{{ route('profile.index') }}" 
                               class="block px-4 py-3 text-white hover:bg-gray-700 transition rounded-t-lg">
                                👤 My Profile
                            </a>
                            <a href="{{ route('profile.watchlist') }}" 
                               class="block px-4 py-3 text-white hover:bg-gray-700 transition">
                                📋 My Watchlist
                            </a>
                            <a href="{{ route('profile.history') }}" 
                               class="block px-4 py-3 text-white hover:bg-gray-700 transition">
                                📜 Watch History
                            </a>
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-3 text-white hover:bg-gray-700 transition">
                                ⚙️ Settings
                            </a>
                            
                            {{-- Admin Link in Dropdown (optional) --}}
                            @if(auth()->user()->isAdmin())
                            <div class="border-t border-gray-700 my-1"></div>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="block px-4 py-3 text-yellow-400 hover:bg-gray-700 transition">
                                🛠️ Admin Panel
                            </a>
                            @endif
                            
                            {{-- Logout --}}
                            <div class="border-t border-gray-700 my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-3 text-red-400 hover:bg-gray-700 transition rounded-b-lg">
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    {{-- Guest Links --}}
                    <a href="{{ route('login') }}" 
                       class="bg-red-400 hover:bg-red-500 text-black px-4 py-2 rounded-lg transition">
                        Login
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-red-400 hover:bg-red-500 text-black px-4 py-2 rounded-lg transition">
                        Register
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Search Bar (if needed) --}}
    @yield('search-bar')

    {{-- Main Content --}}
    <main class="container mx-auto px-6 py-8">
        {{-- Flash Messages --}}
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
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-800 mt-16 py-8">
        <div class="container mx-auto text-center text-gray-400">
            <p>&copy; 2024 Noobz Cinema. All rights reserved.</p>
        </div>
    </footer>

    {{-- Global Scripts --}}
    @stack('scripts')
    
    {{-- Global Watchlist Function --}}
    @auth
    <script>
    function addToWatchlist(movieId) {
        fetch(`/watchlist/add/${movieId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update button
                if (event && event.target) {
                    const button = event.target;
                    button.innerHTML = '✓';
                    button.classList.remove('bg-white/20', 'hover:bg-green-500');
                    button.classList.add('bg-green-500');
                    button.onclick = null;
                    button.title = 'In Watchlist';
                }
                alert(data.message); // atau gunakan notification system
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
    
    // Optional: Notification function
    function showNotification(message, type = 'info') {
        const colors = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all`;
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">✕</button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    </script>
    @endauth
</body>
</html>
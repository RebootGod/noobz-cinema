{{-- ======================================== --}}
{{-- USER PROFILE INDEX VIEW --}}
{{-- ======================================== --}}
{{-- File: resources/views/profile/index.blade.php --}}

@extends('layouts.app')

@section('title', 'My Profile - Noobz Cinema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">My Profile</h1>
            <a href="{{ route('profile.edit') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition">
                ⚙️ Edit Profile
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Profile Info --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="text-center mb-6">
                        <div class="w-32 h-32 bg-gradient-to-br from-green-400 to-blue-500 rounded-full mx-auto mb-4 flex items-center justify-center">
                            <span class="text-5xl font-bold text-white">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold">{{ $user->username }}</h2>
                        <p class="text-gray-400">{{ $user->email }}</p>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Role:</span>
                            <span class="text-white">
                                @if($user->role === 'admin')
                                    <span class="px-2 py-1 bg-blue-500 rounded text-xs">Admin</span>
                                @elseif($user->role === 'super_admin')
                                    <span class="px-2 py-1 bg-purple-500 rounded text-xs">Super Admin</span>
                                @else
                                    <span class="px-2 py-1 bg-gray-600 rounded text-xs">Member</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Member Since:</span>
                            <span class="text-white">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Last Login:</span>
                            <span class="text-white">{{ $stats['last_login'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status:</span>
                            <span class="text-white">
                                @if($user->status === 'active')
                                    <span class="text-green-400">Active</span>
                                @elseif($user->status === 'inactive')
                                    <span class="text-yellow-400">Inactive</span>
                                @else
                                    <span class="text-red-400">Banned</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="bg-gray-800 rounded-lg p-6 mt-6">
                    <h3 class="text-lg font-semibold mb-4">Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Movies Watched:</span>
                            <span class="text-2xl font-bold text-green-400">{{ $stats['total_watched'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">In Watchlist:</span>
                            <span class="text-2xl font-bold text-blue-400">{{ $stats['watchlist_count'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Quick Links --}}
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="{{ route('profile.watchlist') }}" 
                           class="bg-gray-700 hover:bg-gray-600 rounded-lg p-4 transition">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl">📋</span>
                                <div>
                                    <h4 class="font-semibold">My Watchlist</h4>
                                    <p class="text-sm text-gray-400">{{ $stats['watchlist_count'] }} movies saved</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('profile.history') }}" 
                           class="bg-gray-700 hover:bg-gray-600 rounded-lg p-4 transition">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl">📜</span>
                                <div>
                                    <h4 class="font-semibold">Watch History</h4>
                                    <p class="text-sm text-gray-400">{{ $stats['total_watched'] }} movies watched</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('profile.edit') }}#password" 
                           class="bg-gray-700 hover:bg-gray-600 rounded-lg p-4 transition">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl">🔐</span>
                                <div>
                                    <h4 class="font-semibold">Change Password</h4>
                                    <p class="text-sm text-gray-400">Update your password</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('profile.edit') }}" 
                           class="bg-gray-700 hover:bg-gray-600 rounded-lg p-4 transition">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl">✏️</span>
                                <div>
                                    <h4 class="font-semibold">Edit Profile</h4>
                                    <p class="text-sm text-gray-400">Update your information</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Recent Activity --}}
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Recent Activity</h3>
                        <a href="{{ route('profile.history') }}" class="text-green-400 hover:text-green-300 text-sm">
                            View All →
                        </a>
                    </div>
                    
                    @php
                        $recentViews = \App\Models\MovieView::where('user_id', $user->id)
                            ->with('movie')
                            ->latest('watched_at')
                            ->limit(5)
                            ->get();
                    @endphp

                    @if($recentViews->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentViews as $view)
                        <div class="flex items-center space-x-3">
                            <img src="{{ $view->movie->poster_url }}" 
                                 alt="{{ $view->movie->title }}"
                                 class="w-12 h-16 object-cover rounded">
                            <div class="flex-1">
                                <a href="{{ route('movies.show', $view->movie->slug) }}" 
                                   class="font-medium hover:text-green-400 transition">
                                    {{ $view->movie->title }}
                                </a>
                                <p class="text-sm text-gray-400">
                                    Watched {{ $view->watched_at->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('movies.play', $view->movie->slug) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                                Watch Again
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-400">No recent activity</p>
                    @endif
                </div>

                {{-- Account Settings --}}
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-xl font-semibold mb-4">Account Settings</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium">Email Notifications</h4>
                                <p class="text-sm text-gray-400">Receive updates about new movies</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium">Two-Factor Authentication</h4>
                                <p class="text-sm text-gray-400">Add extra security to your account</p>
                            </div>
                            <span class="text-sm text-gray-500">Coming Soon</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
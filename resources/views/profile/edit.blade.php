{{-- ======================================== --}}
{{-- EDIT PROFILE VIEW --}}
{{-- ======================================== --}}
{{-- File: resources/views/profile/edit.blade.php --}}

@extends('layouts.app')

@section('title', 'Edit Profile - Noobz Cinema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Edit Profile</h1>
            <a href="{{ route('profile.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                ← Back to Profile
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Sidebar Navigation --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-800 rounded-lg p-4">
                    <nav class="space-y-2">
                        <a href="#username" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
                            👤 Change Username
                        </a>
                        <a href="#email" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
                            ✉️ Change Email
                        </a>
                        <a href="#password" class="block px-4 py-2 rounded hover:bg-gray-700 transition">
                            🔐 Change Password
                        </a>
                    </nav>
                </div>

                {{-- Current Info --}}
                <div class="bg-gray-800 rounded-lg p-4 mt-6">
                    <h3 class="font-semibold mb-3">Current Information</h3>
                    <div class="space-y-2 text-sm">
                        <div>
                            <span class="text-gray-400">Username:</span>
                            <p class="text-white">{{ $user->username }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Email:</span>
                            <p class="text-white">{{ $user->email }}</p>
                        </div>
                        <div>
                            <span class="text-gray-400">Member Since:</span>
                            <p class="text-white">{{ $user->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Forms Section --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Change Username --}}
                <div id="username" class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Change Username</h2>
                    
                    <form action="{{ route('profile.update.username') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="new_username" class="block text-sm font-medium text-gray-400 mb-2">
                                    New Username
                                </label>
                                <input type="text" 
                                       id="new_username"
                                       name="username" 
                                       value="{{ old('username', $user->username) }}"
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400 @error('username') border-red-500 @enderror"
                                       required>
                                @error('username')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-400 text-xs mt-1">
                                    Username must be 3-20 characters and can only contain letters, numbers, and underscores.
                                </p>
                            </div>
                            
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition">
                                Update Username
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Change Email --}}
                <div id="email" class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Change Email</h2>
                    
                    <form action="{{ route('profile.update.email') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="new_email" class="block text-sm font-medium text-gray-400 mb-2">
                                    New Email Address
                                </label>
                                <input type="email" 
                                       id="new_email"
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400 @error('email') border-red-500 @enderror"
                                       required>
                                @error('email')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email_password" class="block text-sm font-medium text-gray-400 mb-2">
                                    Current Password (Required)
                                </label>
                                <input type="password" 
                                       id="email_password"
                                       name="current_password" 
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400 @error('current_password') border-red-500 @enderror"
                                       required>
                                @error('current_password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition">
                                Update Email
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Change Password --}}
                <div id="password" class="bg-gray-800 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Change Password</h2>
                    
                    <form action="{{ route('profile.update.password') }}" method="POST">
                        @csrf
                        @method('PATCH')
                        
                        <div class="space-y-4">
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-400 mb-2">
                                    Current Password
                                </label>
                                <input type="password" 
                                       id="current_password"
                                       name="current_password" 
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400 @error('current_password') border-red-500 @enderror"
                                       required>
                                @error('current_password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-400 mb-2">
                                    New Password
                                </label>
                                <input type="password" 
                                       id="new_password"
                                       name="password" 
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400 @error('password') border-red-500 @enderror"
                                       required>
                                @error('password')
                                <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-gray-400 text-xs mt-1">
                                    Password must be at least 8 characters long.
                                </p>
                            </div>
                            
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-400 mb-2">
                                    Confirm New Password
                                </label>
                                <input type="password" 
                                       id="password_confirmation"
                                       name="password_confirmation" 
                                       class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg focus:ring-2 focus:ring-green-400"
                                       required>
                            </div>
                            
                            <button type="submit" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition">
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Danger Zone --}}
                <div class="bg-red-900/20 border border-red-500 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-red-400 mb-4">Danger Zone</h2>
                    <p class="text-gray-300 mb-4">
                        Once you delete your account, there is no going back. Please be certain.
                    </p>
                    <button onclick="confirmDelete()" 
                            class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
        if (confirm('This will permanently delete all your data including watch history and watchlist. Type "DELETE" to confirm.')) {
            // You can implement actual deletion here
            alert('Account deletion is disabled for safety. Contact admin if needed.');
        }
    }
}

// Smooth scroll to section
if (window.location.hash) {
    const element = document.querySelector(window.location.hash);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>
@endpush
@endsection
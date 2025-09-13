@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container mx-auto px-6 py-8">
    <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-gray-400 text-sm">Total Movies</h3>
            <p class="text-3xl font-bold text-green-400">{{ $stats['total_movies'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-gray-400 text-sm">Total Users</h3>
            <p class="text-3xl font-bold text-blue-400">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-gray-400 text-sm">Active Users</h3>
            <p class="text-3xl font-bold text-yellow-400">{{ $stats['active_users'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-gray-400 text-sm">Invite Codes</h3>
            <p class="text-3xl font-bold text-purple-400">{{ $stats['total_invite_codes'] }}</p>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <h3 class="text-gray-400 text-sm">Pending Reports</h3>
            <p class="text-3xl font-bold text-yellow-400">{{ $stats['pending_reports'] ?? 0 }}</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <a href="{{ route('admin.movies.index') }}" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition">
            <h2 class="text-xl font-semibold mb-2">Manage Movies</h2>
            <p class="text-gray-400">Add, edit, or delete movies</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition">
            <h2 class="text-xl font-semibold mb-2">Manage Users</h2>
            <p class="text-gray-400">View and manage user accounts</p>
        </a>
        <a href="{{ route('admin.invite-codes.index') }}" class="bg-gray-800 hover:bg-gray-700 rounded-lg p-6 transition">
            <h2 class="text-xl font-semibold mb-2">Manage Invite Codes</h2>
            <p class="text-gray-400">Create and manage invite codes</p>
        </a>
    </div>
</div>
@endsection
{{-- ======================================== --}}
{{-- WATCHLIST VIEW --}}
{{-- ======================================== --}}
{{-- File: resources/views/profile/watchlist.blade.php --}}

@extends('layouts.app')

@section('title', 'My Watchlist - Noobz Cinema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">My Watchlist</h1>
                <p class="text-gray-400 mt-1">{{ $watchlist->total() }} movies saved</p>
            </div>
            <a href="{{ route('profile.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                ← Back to Profile
            </a>
        </div>

        {{-- Watchlist Grid --}}
        @if($watchlist->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($watchlist as $item)
            <div class="bg-gray-800 rounded-lg overflow-hidden hover:ring-2 hover:ring-green-400 transition group">
                <div class="relative">
                    <img src="{{ $item->movie->poster_url }}" 
                         alt="{{ $item->movie->title }}"
                         class="w-full h-auto">
                    
                    {{-- Quality Badge --}}
                    <span class="absolute top-2 left-2 bg-black/70 text-white px-2 py-1 rounded text-xs">
                        {{ $item->movie->quality }}
                    </span>
                    
                    {{-- Overlay Actions --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition flex flex-col justify-end p-3">
                        <h3 class="text-white font-semibold text-sm mb-2 line-clamp-2">
                            {{ $item->movie->title }}
                        </h3>
                        <div class="flex gap-2">
                            <a href="{{ route('movies.play', $item->movie->slug) }}" 
                               class="flex-1 bg-green-500 hover:bg-green-600 text-white text-center py-1 rounded text-xs transition">
                                ▶️ Play
                            </a>
                            <form action="{{ route('profile.watchlist.remove', $item->movie) }}" method="POST" class="flex-1">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="w-full bg-red-500 hover:bg-red-600 text-white py-1 rounded text-xs transition"
                                        onclick="return confirm('Remove from watchlist?')">
                                    ✕ Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                {{-- Movie Info --}}
                <div class="p-3">
                    <a href="{{ route('movies.show', $item->movie->slug) }}" 
                       class="font-medium text-sm hover:text-green-400 transition line-clamp-1">
                        {{ $item->movie->title }}
                    </a>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">{{ $item->movie->year }}</span>
                        <span class="text-xs text-yellow-400">⭐ {{ number_format($item->movie->rating, 1) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Added {{ $item->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $watchlist->links() }}
        </div>
        @else
        {{-- Empty State --}}
        <div class="bg-gray-800 rounded-lg p-16 text-center">
            <div class="text-6xl mb-4">📋</div>
            <h2 class="text-2xl font-semibold mb-2">Your watchlist is empty</h2>
            <p class="text-gray-400 mb-6">Start adding movies you want to watch later!</p>
            <a href="{{ route('movies.index') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition inline-block">
                Browse Movies
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
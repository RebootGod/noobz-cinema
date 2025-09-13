{{-- ======================================== --}}
{{-- MOVIE DETAIL PAGE WITH WATCHLIST --}}
{{-- ======================================== --}}
{{-- File: resources/views/movies/show.blade.php --}}

@extends('layouts.app')

@section('title', $movie->title . ' - Noobz Cinema')

@section('content')
{{-- Backdrop Image Container --}}
@if($movie->backdrop_path)
<div class="relative w-full h-96 -mt-8 mb-8 overflow-hidden">
    <img 
        src="{{ $movie->backdrop_url }}" 
        alt="{{ $movie->title }} backdrop"
        class="w-full h-full object-cover opacity-30"
    >
    <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/50 to-transparent"></div>
</div>
@endif

<div class="container mx-auto px-6 {{ $movie->backdrop_path ? '-mt-32' : 'py-8' }}">
    <div class="flex flex-col lg:flex-row gap-8 relative z-10">
        {{-- Poster --}}
        <div class="flex-shrink-0">
            <div class="w-64 mx-auto lg:mx-0">
                <img 
                    src="{{ $movie->poster_url ?: 'https://via.placeholder.com/256x384/1f2937/ffffff?text=No+Poster' }}" 
                    alt="{{ $movie->title }}"
                    class="w-full rounded-xl shadow-2xl"
                >
            </div>
        </div>

        {{-- Info --}}
        <div class="flex-grow">
            <h1 class="text-4xl font-bold mb-2">{{ $movie->title }}</h1>
            
            {{-- Movie Meta Info --}}
            <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                <span class="bg-yellow-500 text-black px-2 py-1 rounded">
                    ⭐ {{ $movie->rating ?: 'N/A' }}
                </span>
                <span class="bg-gray-700 px-3 py-1 rounded">
                    {{ $movie->year }}
                </span>
                <span class="bg-gray-700 px-3 py-1 rounded">
                    {{ $movie->getFormattedDuration() }}
                </span>
                <span class="bg-green-600 px-3 py-1 rounded">
                    {{ $movie->quality }}
                </span>
            </div>

            {{-- Genres --}}
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($movie->genres as $genre)
                <a href="{{ route('movies.genre', $genre->slug) }}" 
                   class="bg-gray-700 hover:bg-gray-600 px-3 py-1 rounded-full text-sm transition">
                    {{ $genre->name }}
                </a>
                @endforeach
            </div>

            {{-- Description --}}
            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <h2 class="text-xl font-semibold mb-3">Deskripsi Film</h2>
                <p class="text-gray-300 leading-relaxed">
                    {{ $movie->description ?: 'Deskripsi tidak tersedia.' }}
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-4">
                @auth
                    {{-- Watch Now Button --}}
                    <a href="{{ route('movies.play', $movie->slug) }}" 
                       class="bg-green-400 hover:bg-green-500 text-black px-8 py-3 rounded-lg font-bold transition">
                        ▶️ WATCH NOW
                    </a>
                    
                    {{-- Watchlist Button --}}
                    @php
                        $inWatchlist = \App\Models\Watchlist::where('user_id', auth()->id())
                            ->where('movie_id', $movie->id)
                            ->exists();
                    @endphp
                    
                    @if($inWatchlist)
                        <button class="bg-gray-600 px-6 py-3 rounded-lg cursor-not-allowed" disabled>
                            ✓ In Watchlist
                        </button>
                    @else
                        <button onclick="addToWatchlist({{ $movie->id }})" 
                                id="watchlist-btn-{{ $movie->id }}"
                                class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-lg transition">
                            + Add to Watchlist
                        </button>
                    @endif
                @else
                    {{-- Guest Buttons --}}
                    <a href="{{ route('login') }}" 
                       class="bg-red-400 hover:bg-red-500 text-black px-8 py-3 rounded-lg font-bold transition">
                        LOGIN UNTUK MENONTON
                    </a>
                    <a href="{{ route('register') }}" 
                       class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-lg transition">
                        REGISTER
                    </a>
                @endauth
            </div>

            {{-- View Count --}}
            <div class="mt-6 text-gray-400 text-sm">
                👁️ {{ number_format($movie->view_count) }} views
            </div>
        </div>
    </div>

    {{-- Related Movies --}}
    @if($relatedMovies->count() > 0)
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Film Serupa</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($relatedMovies as $related)
            <a href="{{ route('movies.show', $related->slug) }}" class="group">
                <div class="bg-gray-800 rounded-lg overflow-hidden hover:scale-105 transition-transform relative">
                    {{-- Watchlist Button on Card --}}
                    @auth
                        @php
                            $relatedInWatchlist = \App\Models\Watchlist::where('user_id', auth()->id())
                                ->where('movie_id', $related->id)
                                ->exists();
                        @endphp
                        <div class="absolute top-2 right-2 z-10">
                            @if($relatedInWatchlist)
                                <span class="bg-green-500 text-white p-1.5 rounded-full text-xs">
                                    ✓
                                </span>
                            @else
                                <button onclick="event.preventDefault(); event.stopPropagation(); addToWatchlist({{ $related->id }})" 
                                        id="watchlist-btn-{{ $related->id }}"
                                        class="bg-black/50 hover:bg-green-500 text-white p-1.5 rounded-full text-xs transition">
                                    +
                                </button>
                            @endif
                        </div>
                    @endauth
                    
                    <div class="aspect-[2/3] bg-gray-700">
                        <img 
                            src="{{ $related->poster_url ?: 'https://via.placeholder.com/200x300' }}" 
                            alt="{{ $related->title }}"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-2">
                        <h3 class="text-sm font-medium truncate">{{ $related->title }}</h3>
                        <p class="text-xs text-gray-400">{{ $related->year }}</p>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function addToWatchlist(movieId) {
    // Get the button that was clicked
    const button = document.getElementById(`watchlist-btn-${movieId}`);
    
    // Disable button to prevent double clicks
    if (button) {
        button.disabled = true;
        button.textContent = 'Adding...';
    }
    
    fetch(`/watchlist/add/${movieId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update button appearance
            if (button) {
                button.textContent = '✓ In Watchlist';
                button.classList.remove('bg-gray-700', 'hover:bg-gray-600', 'bg-black/50', 'hover:bg-green-500');
                button.classList.add('bg-gray-600', 'cursor-not-allowed');
                button.onclick = null;
            }
            
            // Show success message (optional)
            showNotification(data.message || 'Added to watchlist!', 'success');
        } else {
            // Show error message
            showNotification(data.message || 'Failed to add to watchlist', 'error');
            
            // Re-enable button
            if (button) {
                button.disabled = false;
                button.textContent = '+ Add to Watchlist';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
        
        // Re-enable button
        if (button) {
            button.disabled = false;
            button.textContent = '+ Add to Watchlist';
        }
    });
}

// Simple notification function
function showNotification(message, type = 'info') {
    const colors = {
        'success': 'bg-green-500',
        'error': 'bg-red-500',
        'warning': 'bg-yellow-500',
        'info': 'bg-blue-500'
    };
    
    // Remove existing notifications
    const existing = document.querySelector('.notification-toast');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = `notification-toast fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all transform translate-x-0`;
    notification.innerHTML = message;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
@endpush
@endsection
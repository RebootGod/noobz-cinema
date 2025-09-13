{{-- ======================================== --}}
{{-- WATCH HISTORY VIEW --}}
{{-- ======================================== --}}
{{-- File: resources/views/profile/history.blade.php --}}

@extends('layouts.app')

@section('title', 'Watch History - Noobz Cinema')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold">Watch History</h1>
                <p class="text-gray-400 mt-1">{{ $history->total() }} movies watched</p>
            </div>
            <div class="flex gap-3">
                @if($history->count() > 0)
                <form action="{{ route('profile.history.clear') }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to clear your watch history?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                        🗑️ Clear History
                    </button>
                </form>
                @endif
                <a href="{{ route('profile.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition">
                    ← Back to Profile
                </a>
            </div>
        </div>

        {{-- History List --}}
        @if($history->count() > 0)
        <div class="space-y-6">
            @foreach($groupedHistory as $date => $dayHistory)
            <div class="bg-gray-800 rounded-lg p-4">
                <h3 class="text-lg font-semibold mb-4 text-green-400">
                    @if(\Carbon\Carbon::parse($date)->isToday())
                        Today
                    @elseif(\Carbon\Carbon::parse($date)->isYesterday())
                        Yesterday
                    @else
                        {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                    @endif
                </h3>
                
                <div class="space-y-3">
                    @foreach($dayHistory as $view)
                    <div class="flex items-center space-x-4 bg-gray-700 rounded-lg p-3 hover:bg-gray-600 transition">
                        {{-- Poster --}}
                        <img src="{{ $view->movie->poster_url }}" 
                             alt="{{ $view->movie->title }}"
                             class="w-16 h-20 object-cover rounded">
                        
                        {{-- Movie Info --}}
                        <div class="flex-1">
                            <a href="{{ route('movies.show', $view->movie->slug) }}" 
                               class="font-semibold hover:text-green-400 transition">
                                {{ $view->movie->title }}
                            </a>
                            <div class="flex items-center gap-4 text-sm text-gray-400 mt-1">
                                <span>{{ $view->movie->year }}</span>
                                <span>•</span>
                                <span>{{ $view->movie->getFormattedDuration() }}</span>
                                <span>•</span>
                                <span>⭐ {{ number_format($view->movie->rating, 1) }}</span>
                                <span>•</span>
                                <span>{{ $view->movie->quality }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Watched at {{ $view->watched_at->format('g:i A') }}
                                @if($view->watch_duration)
                                • Duration: {{ number_format($view->watch_duration / 60, 0) }} minutes
                                @endif
                            </p>
                        </div>
                        
                        {{-- Actions --}}
                        <div class="flex gap-2">
                            <a href="{{ route('movies.play', $view->movie->slug) }}" 
                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                                Watch Again
                            </a>
                            @php
                                $inWatchlist = \App\Models\Watchlist::isInWatchlist(auth()->id(), $view->movie->id);
                            @endphp
                            @if(!$inWatchlist)
                            <button onclick="addToWatchlist({{ $view->movie->id }})" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition">
                                + Watchlist
                            </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $history->links() }}
        </div>
        @else
        {{-- Empty State --}}
        <div class="bg-gray-800 rounded-lg p-16 text-center">
            <div class="text-6xl mb-4">📜</div>
            <h2 class="text-2xl font-semibold mb-2">No watch history yet</h2>
            <p class="text-gray-400 mb-6">Start watching movies to build your history!</p>
            <a href="{{ route('movies.index') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition inline-block">
                Browse Movies
            </a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
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
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>
@endpush
@endsection
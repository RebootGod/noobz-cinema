{{-- ======================================== --}}
{{-- 1. MOVIE CARD COMPONENT --}}
{{-- ======================================== --}}
{{-- File: resources/views/components/movie-card.blade.php --}}

<a href="{{ route('movies.show', $movie->slug) }}" class="group">
    <div class="bg-gray-800 rounded-xl overflow-hidden hover:scale-105 transition-transform duration-300">
        {{-- Poster --}}
        <div class="aspect-[2/3] bg-green-400 relative">
            @if($movie->poster_url)
                <img 
                    src="{{ $movie->poster_url }}" 
                    alt="{{ $movie->title }}"
                    class="w-full h-full object-cover"
                    loading="lazy"
                >
            @else
                <div class="flex items-center justify-center h-full text-black font-bold">
                    MOVIE Poster
                </div>
            @endif
            
            {{-- Quality Badge --}}
            <div class="absolute top-2 right-2 bg-black/70 text-white px-2 py-1 rounded text-xs font-semibold">
                {{ $movie->quality }}
            </div>
            
            {{-- Hover Overlay --}}
            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="bg-green-400 text-black px-4 py-2 rounded-lg font-semibold">
                    View Details
                </span>
            </div>
        </div>
        
        {{-- Info --}}
        <div class="p-3 bg-red-300">
            <h3 class="font-semibold truncate text-black">{{ $movie->title }}</h3>
            <div class="text-sm mt-1 space-y-1 text-gray-800">
                <p>Tahun: {{ $movie->year ?? 'N/A' }}</p>
                <p class="truncate">Genre: {{ $movie->genres->pluck('name')->take(2)->join(', ') ?: 'N/A' }}</p>
                <p>Rating: {{ $movie->rating ?? 'N/A' }}/10</p>
            </div>
        </div>
    </div>
</a>
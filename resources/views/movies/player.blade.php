{{-- ======================================== --}}
{{-- ENHANCED MOVIE PLAYER PAGE --}}
{{-- ======================================== --}}
{{-- File: resources/views/movies/player.blade.php --}}

@extends('layouts.app')

@section('title', 'Watching: ' . $movie->title . ' - Noobz Cinema')

@section('content')
<div class="container mx-auto px-4 py-6">
    {{-- Movie Header --}}
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold">{{ $movie->title }}</h1>
                <div class="flex items-center gap-4 text-sm text-gray-400 mt-2">
                    <span>{{ $movie->year }}</span>
                    <span>•</span>
                    <span>{{ $movie->getFormattedDuration() }}</span>
                    <span>•</span>
                    <span>⭐ {{ number_format($movie->rating, 1) }}/10</span>
                    <span>•</span>
                    <span>👁️ {{ number_format($movie->view_count) }} views</span>
                </div>
            </div>
            <a href="{{ route('movies.show', $movie->slug) }}" 
               class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                ← Back
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Player Section --}}
            <div class="lg:col-span-2">
                {{-- Video Player --}}
                <div class="bg-black rounded-lg overflow-hidden" style="aspect-ratio: 16/9;">
                    <iframe 
                        id="moviePlayer"
                        src="{{ $currentSource->embed_url }}"
                        frameborder="0"
                        allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        class="w-full h-full"
                        referrerpolicy="no-referrer"
                    ></iframe>
                </div>

                {{-- Player Controls --}}
                <div class="bg-gray-800 rounded-lg p-4 mt-4">
                    <div class="flex flex-wrap justify-between items-center gap-3">
                        <div class="flex items-center gap-2">
                            <button onclick="toggleLights()" 
                                    class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm transition">
                                💡 Lights
                            </button>
                            <button onclick="reloadPlayer()" 
                                    class="bg-gray-700 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-sm transition">
                                🔄 Reload
                            </button>
                        </div>
                        <button onclick="openReportModal()" 
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded text-sm transition">
                            ⚠️ Report Issue
                        </button>
                    </div>
                </div>

                {{-- Source & Quality Selector --}}
                @if($sources->count() > 1)
                <div class="bg-gray-800 rounded-lg p-4 mt-4">
                    <h3 class="text-lg font-semibold mb-3">Select Source & Quality</h3>
                    
                    {{-- Quality Filter Buttons --}}
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button onclick="filterQuality('all')" 
                                class="quality-btn px-3 py-1 rounded text-sm bg-green-500 text-white transition"
                                data-quality="all">
                            All
                        </button>
                        @foreach($sourcesByQuality as $quality => $qualitySources)
                        <button onclick="filterQuality('{{ $quality }}')" 
                                class="quality-btn px-3 py-1 rounded text-sm bg-gray-700 text-gray-300 hover:bg-gray-600 transition"
                                data-quality="{{ $quality }}">
                            {{ $quality }} ({{ $qualitySources->count() }})
                        </button>
                        @endforeach
                    </div>
                    
                    {{-- Source List --}}
                    <div class="space-y-2" id="sourceList">
                        @foreach($sources as $source)
                        <div class="source-item flex items-center justify-between bg-gray-700 rounded p-2 {{ $currentSource->id == $source->id ? 'ring-2 ring-green-500' : '' }}"
                             data-quality="{{ $source->quality }}">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $source->source_name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-600">{{ $source->quality }}</span>
                                @if(isset($source->has_subtitle) && $source->has_subtitle)
                                <span class="text-xs px-2 py-0.5 rounded bg-blue-500">CC</span>
                                @endif
                                @if(isset($source->report_count) && $source->report_count > 5)
                                <span class="text-xs text-yellow-400">⚠️ Issues reported</span>
                                @endif
                            </div>
                            @if($currentSource->id == $source->id)
                            <span class="text-green-400 text-sm">Playing</span>
                            @else
                            <button onclick="switchSource({{ $source->id }})" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm transition">
                                Play
                            </button>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Movie Description --}}
                <div class="bg-gray-800 rounded-lg p-6 mt-4">
                    <h2 class="text-xl font-semibold mb-3">Description</h2>
                    <p class="text-gray-300 leading-relaxed">
                        {{ $movie->description ?: 'No description available.' }}
                    </p>
                    
                    @if($movie->genres->count() > 0)
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($movie->genres as $genre)
                        <a href="{{ route('movies.genre', $genre->slug) }}" 
                           class="bg-purple-500/20 hover:bg-purple-500/30 text-purple-400 px-3 py-1 rounded-full text-sm transition">
                            {{ $genre->name }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Movie Poster --}}
                <div class="bg-gray-800 rounded-lg overflow-hidden">
                    <img src="{{ $movie->poster_url }}" 
                         alt="{{ $movie->title }}"
                         class="w-full">
                </div>

                {{-- Movie Details --}}
                <div class="bg-gray-800 rounded-lg p-4">
                    <h3 class="font-semibold mb-3">Movie Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Year:</dt>
                            <dd>{{ $movie->year }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Duration:</dt>
                            <dd>{{ $movie->getFormattedDuration() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Best Quality:</dt>
                            <dd>{{ $bestQuality ?? 'HD' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Rating:</dt>
                            <dd>⭐ {{ number_format($movie->rating, 1) }}/10</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-400">Views:</dt>
                            <dd>{{ number_format($movie->view_count) }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Related Movies --}}
                @if(isset($relatedMovies) && $relatedMovies->count() > 0)
                <div class="bg-gray-800 rounded-lg p-4">
                    <h3 class="font-semibold mb-3">You May Also Like</h3>
                    <div class="space-y-2">
                        @foreach($relatedMovies as $related)
                        <a href="{{ route('movies.show', $related->slug) }}" 
                           class="flex items-center gap-3 hover:bg-gray-700 rounded p-2 transition">
                            <img src="{{ $related->poster_url }}" 
                                 alt="{{ $related->title }}"
                                 class="w-12 h-16 object-cover rounded">
                            <div class="flex-1">
                                <p class="text-sm font-medium line-clamp-1">{{ $related->title }}</p>
                                <p class="text-xs text-gray-400">{{ $related->year }}</p>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Report Issue Modal --}}
<div id="reportModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-800 rounded-lg max-w-md w-full p-6">
            <h3 class="text-xl font-semibold mb-4">Report an Issue</h3>
            
            <form id="reportForm">
                <input type="hidden" id="sourceId" value="{{ $currentSource->id ?? '' }}">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">
                            What's the problem?
                        </label>
                        <select id="issueType" required
                                class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <option value="">Select issue...</option>
                            <option value="not_loading">Video Not Loading</option>
                            <option value="wrong_movie">Wrong Movie</option>
                            <option value="poor_quality">Poor Quality</option>
                            <option value="no_audio">No Audio</option>
                            <option value="no_subtitle">No Subtitle</option>
                            <option value="buffering">Constant Buffering</option>
                            <option value="other">Other Issue</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-2">
                            Description (Optional)
                        </label>
                        <textarea id="issueDescription" rows="3"
                                  class="w-full bg-gray-700 text-white px-4 py-2 rounded-lg"
                                  placeholder="Describe the issue..."></textarea>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="submit"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition">
                            Submit Report
                        </button>
                        <button type="button" onclick="closeReportModal()"
                                class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Cinema Mode Overlay --}}
<div id="cinemaOverlay" class="fixed inset-0 bg-black bg-opacity-95 hidden z-40" onclick="toggleLights()"></div>

@endsection

@push('scripts')
<script>
// Track view after 10 seconds
let viewTracked = false;
setTimeout(() => {
    if (!viewTracked) {
        fetch('{{ route("movies.track-view", $movie) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              if (data.success && data.counted) {
                  console.log('View counted');
              }
          })
          .catch(error => console.error('Error tracking view:', error));
        viewTracked = true;
    }
}, 10000);

// Switch source
function switchSource(sourceId) {
    window.location.href = `{{ route('movies.play', $movie->slug) }}?source=${sourceId}`;
}

// Filter by quality
function filterQuality(quality) {
    const allItems = document.querySelectorAll('.source-item');
    const allBtns = document.querySelectorAll('.quality-btn');
    
    // Update button states
    allBtns.forEach(btn => {
        if (btn.dataset.quality === quality) {
            btn.classList.add('bg-green-500', 'text-white');
            btn.classList.remove('bg-gray-700', 'text-gray-300');
        } else {
            btn.classList.remove('bg-green-500', 'text-white');
            btn.classList.add('bg-gray-700', 'text-gray-300');
        }
    });
    
    // Show/hide sources
    allItems.forEach(item => {
        if (quality === 'all' || item.dataset.quality === quality) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

// Toggle cinema mode
function toggleLights() {
    const overlay = document.getElementById('cinemaOverlay');
    overlay.classList.toggle('hidden');
    document.body.classList.toggle('overflow-hidden');
}

// Reload player
function reloadPlayer() {
    const iframe = document.getElementById('moviePlayer');
    const src = iframe.src;
    iframe.src = '';
    setTimeout(() => {
        iframe.src = src;
    }, 100);
}

// Report modal
function openReportModal() {
    document.getElementById('reportModal').classList.remove('hidden');
}

function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportForm').reset();
}

// Submit report
document.getElementById('reportForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const data = {
        source_id: document.getElementById('sourceId').value,
        issue_type: document.getElementById('issueType').value,
        description: document.getElementById('issueDescription').value
    };
    
    fetch('{{ route("movies.report", $movie) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            closeReportModal();
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
});

// Close modals on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReportModal();
        if (!document.getElementById('cinemaOverlay').classList.contains('hidden')) {
            toggleLights();
        }
    }
});
</script>
@endpush
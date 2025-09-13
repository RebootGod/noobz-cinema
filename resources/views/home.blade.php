{{-- ======================================== --}}
{{-- ENHANCED HOME PAGE WITH ADVANCED SEARCH --}}
{{-- ======================================== --}}
{{-- File: resources/views/home.blade.php --}}

@extends('layouts.app')

@section('title', 'Home - Noobz Cinema')

{{-- Add Alpine.js for Live Search --}}
@push('styles')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    
    {{-- ======================================== --}}
    {{-- ADVANCED SEARCH WITH LIVE AUTOCOMPLETE --}}
    {{-- ======================================== --}}
    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 mb-8 border border-gray-700">
        
        {{-- Search Header --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Search Movies
            </h2>
            <button onclick="resetFilters()" class="text-gray-400 hover:text-white transition-colors text-sm">
                Reset All
            </button>
        </div>

        {{-- Main Search Bar with Live Autocomplete --}}
        <form method="GET" action="{{ route('home') }}" id="searchForm">
            
            {{-- Live Search Component --}}
            <div x-data="searchAutocomplete()" class="relative mb-6">
                <input 
                    type="text" 
                    name="search" 
                    id="searchInput"
                    x-model="searchQuery"
                    @input.debounce.300ms="performSearch"
                    @focus="showResults = true"
                    @keydown.escape="showResults = false"
                    @keydown.enter="submitSearch"
                    @keydown.arrow-down.prevent="selectNext"
                    @keydown.arrow-up.prevent="selectPrevious"
                    value="{{ request('search') }}"
                    placeholder="Search by title, actor, director..."
                    class="w-full bg-gray-900/50 text-white px-6 py-4 pr-12 rounded-xl border border-gray-700 focus:border-green-400 focus:outline-none transition-all text-lg placeholder-gray-500"
                    autocomplete="off"
                >
                
                {{-- Search Button/Loading Spinner --}}
                <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-500 hover:bg-green-600 text-black px-4 py-2 rounded-lg transition-colors">
                    <svg x-show="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <svg x-show="isLoading" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
                
                {{-- Search Results Dropdown --}}
                <div 
                    x-show="showResults && (searchResults.length > 0 || recentSearches.length > 0 || searchQuery.length > 0)"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    @click.away="showResults = false"
                    class="absolute z-50 w-full mt-2 bg-gray-800 rounded-xl shadow-2xl border border-gray-700 overflow-hidden"
                >
                    {{-- Recent Searches (shown when input is empty) --}}
                    <div x-show="searchQuery.length === 0 && recentSearches.length > 0" class="p-4">
                        <h3 class="text-gray-400 text-sm mb-3">Recent Searches</h3>
                        <div class="space-y-2">
                            <template x-for="recent in recentSearches" :key="recent">
                                <button 
                                    @click="searchQuery = recent; performSearch()"
                                    type="button"
                                    class="flex items-center gap-2 w-full text-left px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors"
                                >
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-gray-300" x-text="recent"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    
                    {{-- Search Results --}}
                    <div x-show="searchResults.length > 0" class="max-h-96 overflow-y-auto">
                        <template x-for="(result, index) in searchResults" :key="result.id">
                            <a 
                                :href="'/movie/' + result.slug"
                                @mouseenter="selectedIndex = index"
                                :class="{'bg-gray-700': selectedIndex === index}"
                                class="flex items-center gap-4 p-3 hover:bg-gray-700 transition-colors border-b border-gray-700/50 last:border-0"
                            >
                                {{-- Movie Poster Thumbnail --}}
                                <img 
                                    :src="result.poster_url || '/placeholder-poster.jpg'" 
                                    :alt="result.title"
                                    class="w-12 h-16 object-cover rounded"
                                >
                                
                                {{-- Movie Info --}}
                                <div class="flex-1">
                                    <h4 class="text-white font-medium" x-html="highlightMatch(result.title)"></h4>
                                    <div class="flex items-center gap-3 text-sm text-gray-400 mt-1">
                                        <span x-text="result.year"></span>
                                        <span x-show="result.rating" class="flex items-center gap-1">
                                            <svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            <span x-text="result.rating"></span>
                                        </span>
                                        <span x-show="result.quality" class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded text-xs" x-text="result.quality"></span>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                    
                    {{-- No Results --}}
                    <div x-show="searchQuery.length > 2 && searchResults.length === 0 && !isLoading" class="p-8 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-400">No movies found for "<span x-text="searchQuery" class="text-white"></span>"</p>
                        <p class="text-gray-500 text-sm mt-2">Try different keywords</p>
                    </div>
                    
                    {{-- View All Results --}}
                    <div x-show="searchResults.length > 0" class="p-3 bg-gray-900/50 border-t border-gray-700">
                        <button 
                            @click="submitSearch"
                            type="button"
                            class="w-full text-center text-green-400 hover:text-green-300 transition-colors text-sm"
                        >
                            View all results for "<span x-text="searchQuery"></span>"
                        </button>
                    </div>
                </div>
            </div>

            {{-- Advanced Filters --}}
            <div class="space-y-4">
                
                {{-- Filter Row 1: Genre, Year, Quality --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    {{-- Genre Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Genre</label>
                        <select name="genre" class="w-full bg-gray-900/50 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-green-400 focus:outline-none transition-all">
                            <option value="">All Genres</option>
                            @foreach(['Action', 'Adventure', 'Animation', 'Comedy', 'Crime', 'Documentary', 'Drama', 'Family', 'Fantasy', 'History', 'Horror', 'Music', 'Mystery', 'Romance', 'Sci-Fi', 'Thriller', 'War', 'Western'] as $genre)
                            <option value="{{ strtolower($genre) }}" {{ request('genre') == strtolower($genre) ? 'selected' : '' }}>
                                {{ $genre }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Year</label>
                        <select name="year" class="w-full bg-gray-900/50 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-green-400 focus:outline-none transition-all">
                            <option value="">All Years</option>
                            <option value="2024" {{ request('year') == '2024' ? 'selected' : '' }}>2024</option>
                            <option value="2023" {{ request('year') == '2023' ? 'selected' : '' }}>2023</option>
                            <option value="2022" {{ request('year') == '2022' ? 'selected' : '' }}>2022</option>
                            <option value="2021" {{ request('year') == '2021' ? 'selected' : '' }}>2021</option>
                            <option value="2020" {{ request('year') == '2020' ? 'selected' : '' }}>2020</option>
                            <option value="2010s" {{ request('year') == '2010s' ? 'selected' : '' }}>2010-2019</option>
                            <option value="2000s" {{ request('year') == '2000s' ? 'selected' : '' }}>2000-2009</option>
                            <option value="90s" {{ request('year') == '90s' ? 'selected' : '' }}>90s</option>
                            <option value="80s" {{ request('year') == '80s' ? 'selected' : '' }}>80s</option>
                            <option value="older" {{ request('year') == 'older' ? 'selected' : '' }}>Older</option>
                        </select>
                    </div>

                    {{-- Quality Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Quality</label>
                        <select name="quality" class="w-full bg-gray-900/50 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-green-400 focus:outline-none transition-all">
                            <option value="">All Quality</option>
                            <option value="4k" {{ request('quality') == '4k' ? 'selected' : '' }}>4K Ultra HD</option>
                            <option value="1080p" {{ request('quality') == '1080p' ? 'selected' : '' }}>1080p Full HD</option>
                            <option value="720p" {{ request('quality') == '720p' ? 'selected' : '' }}>720p HD</option>
                            <option value="cam" {{ request('quality') == 'cam' ? 'selected' : '' }}>CAM</option>
                        </select>
                    </div>
                </div>

                {{-- Filter Row 2: Rating, Sort, Language --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    {{-- Rating Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Min. Rating</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="range" 
                                name="rating" 
                                min="0" 
                                max="10" 
                                step="0.5"
                                value="{{ request('rating', 0) }}"
                                class="flex-1"
                                oninput="updateRatingDisplay(this.value)"
                            >
                            <span id="ratingDisplay" class="text-green-400 font-bold min-w-[3rem]">
                                {{ request('rating', 0) }}+
                            </span>
                        </div>
                    </div>

                    {{-- Sort Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Sort By</label>
                        <select name="sort" class="w-full bg-gray-900/50 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-green-400 focus:outline-none transition-all">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest Added</option>
                            <option value="release" {{ request('sort') == 'release' ? 'selected' : '' }}>Release Date</option>
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rating</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            <option value="views" {{ request('sort') == 'views' ? 'selected' : '' }}>Most Viewed</option>
                            <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Title (A-Z)</option>
                        </select>
                    </div>

                    {{-- Language Filter --}}
                    <div>
                        <label class="text-gray-400 text-sm mb-2 block">Language</label>
                        <select name="language" class="w-full bg-gray-900/50 text-white px-4 py-3 rounded-lg border border-gray-700 focus:border-green-400 focus:outline-none transition-all">
                            <option value="">All Languages</option>
                            <option value="english" {{ request('language') == 'english' ? 'selected' : '' }}>English</option>
                            <option value="indonesian" {{ request('language') == 'indonesian' ? 'selected' : '' }}>Indonesian</option>
                            <option value="korean" {{ request('language') == 'korean' ? 'selected' : '' }}>Korean</option>
                            <option value="japanese" {{ request('language') == 'japanese' ? 'selected' : '' }}>Japanese</option>
                            <option value="chinese" {{ request('language') == 'chinese' ? 'selected' : '' }}>Chinese</option>
                        </select>
                    </div>
                </div>

                {{-- Additional Filters --}}
                <div class="flex flex-wrap gap-4 pt-2">
                    <label class="flex items-center gap-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="subtitle" value="1" {{ request('subtitle') ? 'checked' : '' }} class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                        <span>With Subtitle</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="dubbed" value="1" {{ request('dubbed') ? 'checked' : '' }} class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                        <span>Dubbed</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="trending" value="1" {{ request('trending') ? 'checked' : '' }} class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                        <span>Trending Now</span>
                    </label>
                    <label class="flex items-center gap-2 text-gray-300 cursor-pointer">
                        <input type="checkbox" name="new" value="1" {{ request('new') ? 'checked' : '' }} class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                        <span>New Releases</span>
                    </label>
                </div>
            </div>

            {{-- Apply Filters Button --}}
            <div class="flex justify-center mt-6">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-black font-bold px-8 py-3 rounded-lg transition-all transform hover:scale-105">
                    Apply Filters
                </button>
            </div>
        </form>
    </div>

    {{-- ======================================== --}}
    {{-- ACTIVE FILTERS DISPLAY --}}
    {{-- ======================================== --}}
    @if(request()->hasAny(['search', 'genre', 'year', 'quality', 'rating', 'language', 'subtitle', 'dubbed', 'trending', 'new']))
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-gray-400">Active filters:</span>
                
                @if(request('search'))
                <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm flex items-center gap-1">
                    Search: "{{ request('search') }}"
                    <button onclick="removeFilter('search')" class="ml-1 hover:text-blue-300">×</button>
                </span>
                @endif
                
                @if(request('genre'))
                <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-sm flex items-center gap-1">
                    {{ ucfirst(request('genre')) }}
                    <button onclick="removeFilter('genre')" class="ml-1 hover:text-purple-300">×</button>
                </span>
                @endif
                
                @if(request('year'))
                <span class="px-3 py-1 bg-green-500/20 text-green-400 rounded-full text-sm flex items-center gap-1">
                    Year: {{ request('year') }}
                    <button onclick="removeFilter('year')" class="ml-1 hover:text-green-300">×</button>
                </span>
                @endif
                
                @if(request('quality'))
                <span class="px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded-full text-sm flex items-center gap-1">
                    {{ strtoupper(request('quality')) }}
                    <button onclick="removeFilter('quality')" class="ml-1 hover:text-yellow-300">×</button>
                </span>
                @endif
                
                @if(request('rating') > 0)
                <span class="px-3 py-1 bg-orange-500/20 text-orange-400 rounded-full text-sm flex items-center gap-1">
                    Rating: {{ request('rating') }}+
                    <button onclick="removeFilter('rating')" class="ml-1 hover:text-orange-300">×</button>
                </span>
                @endif
            </div>
            
            <button onclick="resetFilters()" class="text-red-400 hover:text-red-300 text-sm">
                Clear All ×
            </button>
        </div>
    </div>
    @endif

    {{-- ======================================== --}}
    {{-- SEARCH RESULTS COUNT --}}
    {{-- ======================================== --}}
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl text-white">
            @if(request()->hasAny(['search', 'genre', 'year', 'quality']))
                Found <span class="text-green-400 font-bold">{{ $movies->total() ?? '0' }}</span> movies
            @else
                All Movies
            @endif
        </h3>
        
        <div class="flex items-center gap-2 text-gray-400">
            <span>View:</span>
            <button onclick="setViewMode('grid')" class="p-2 hover:text-white transition-colors view-mode-btn" data-mode="grid">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM13 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2z"></path>
                </svg>
            </button>
            <button onclick="setViewMode('list')" class="p-2 hover:text-white transition-colors view-mode-btn" data-mode="list">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </div>

    {{-- ======================================== --}}
    {{-- MOVIES GRID/LIST --}}
    {{-- ======================================== --}}
    <div id="moviesContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
        @forelse($movies ?? [] as $movie)
        <div class="movie-card group relative overflow-hidden rounded-lg bg-gray-800 transition-all hover:scale-105 hover:z-10">
            <a href="{{ route('movie.show', $movie->id) }}">
                <div class="aspect-[2/3] relative">
                    <img 
                        src="{{ $movie->poster_url ?? '/placeholder-poster.jpg' }}" 
                        alt="{{ $movie->title }}"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    >
                    
                    {{-- Quality Badge --}}
                    @if($movie->quality)
                    <span class="absolute top-2 left-2 px-2 py-1 bg-green-500 text-black text-xs font-bold rounded">
                        {{ strtoupper($movie->quality) }}
                    </span>
                    @endif
                    
                    {{-- Rating --}}
                    @if($movie->rating)
                    <span class="absolute top-2 right-2 px-2 py-1 bg-black/70 text-yellow-400 text-xs font-bold rounded flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                        {{ number_format($movie->rating, 1) }}
                    </span>
                    @endif
                    
                    {{-- Hover Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3">
                        <div>
                            <h3 class="text-white font-bold text-sm line-clamp-2">{{ $movie->title }}</h3>
                            <p class="text-gray-300 text-xs">{{ $movie->year }}</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="w-24 h-24 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 16h4m10 0h4"></path>
            </svg>
            <p class="text-gray-400 text-lg">No movies found</p>
            <p class="text-gray-500 text-sm mt-2">Try adjusting your filters</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if(isset($movies) && $movies->hasPages())
    <div class="mt-8">
        {{ $movies->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- ======================================== --}}
{{-- JAVASCRIPT FOR FILTERS --}}
{{-- ======================================== --}}
<script>
// Quick search function
function quickSearchFor(term) {
    document.getElementById('searchInput').value = term;
    document.getElementById('searchForm').submit();
}

// Update rating display
function updateRatingDisplay(value) {
    document.getElementById('ratingDisplay').textContent = value + '+';
}

// Remove individual filter
function removeFilter(filterName) {
    const form = document.getElementById('searchForm');
    const input = form.elements[filterName];
    if (input) {
        if (input.type === 'checkbox') {
            input.checked = false;
        } else {
            input.value = '';
        }
        form.submit();
    }
}

// Reset all filters
function resetFilters() {
    window.location.href = '{{ route("home") }}';
}

// View mode toggle
function setViewMode(mode) {
    const container = document.getElementById('moviesContainer');
    
    // Update button states
    document.querySelectorAll('.view-mode-btn').forEach(btn => {
        btn.classList.toggle('text-white', btn.dataset.mode === mode);
        btn.classList.toggle('text-gray-400', btn.dataset.mode !== mode);
    });
    
    // Change grid layout based on mode
    if (mode === 'list') {
        container.className = 'space-y-4';
        // Would need to restructure movie cards for list view
    } else {
        container.className = 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4';
    }
    
    // Save preference
    localStorage.setItem('viewMode', mode);
}

// Load saved view mode
document.addEventListener('DOMContentLoaded', function() {
    const savedMode = localStorage.getItem('viewMode') || 'grid';
    setViewMode(savedMode);
});

// Live search dengan debounce
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    if (e.target.value.length > 2) {
        searchTimeout = setTimeout(() => {
            // Could implement AJAX live search here
            console.log('Searching for:', e.target.value);
        }, 500);
    }
});
</script>
@endsection

{{-- Alpine.js Component Script --}}
@push('scripts')
<script>
function searchAutocomplete() {
    return {
        searchQuery: '{{ request("search") ?? "" }}',
        searchResults: [],
        recentSearches: JSON.parse(localStorage.getItem('recentSearches') || '[]'),
        showResults: false,
        isLoading: false,
        selectedIndex: -1,
        searchTimeout: null,
        
        async performSearch() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            
            this.isLoading = true;
            
            try {
                const response = await fetch(`/api/movies/suggestions?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.searchResults = data;
                this.selectedIndex = -1;
            } catch (error) {
                console.error('Search error:', error);
                this.searchResults = [];
            } finally {
                this.isLoading = false;
            }
        },
        
        submitSearch() {
            if (this.searchQuery.trim()) {
                // Save to recent searches
                this.saveRecentSearch(this.searchQuery);
                
                // Submit the form
                document.getElementById('searchForm').submit();
            }
        },
        
        saveRecentSearch(query) {
            let recent = this.recentSearches.filter(s => s !== query);
            recent.unshift(query);
            recent = recent.slice(0, 5); // Keep only 5 recent searches
            this.recentSearches = recent;
            localStorage.setItem('recentSearches', JSON.stringify(recent));
        },
        
        selectNext() {
            if (this.selectedIndex < this.searchResults.length - 1) {
                this.selectedIndex++;
            }
        },
        
        selectPrevious() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
            }
        },
        
        highlightMatch(text) {
            if (!this.searchQuery) return text;
            
            const regex = new RegExp(`(${this.searchQuery})`, 'gi');
            return text.replace(regex, '<mark class="bg-yellow-400/30 text-yellow-300">$1</mark>');
        }
    }
}
</script>
@endpush
@endsection
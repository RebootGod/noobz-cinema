<?php

// ========================================
// MOVIE CONTROLLER
// ========================================
// File: app/Http/Controllers/MovieController.php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\MovieView;
use App\Models\MovieSource;
use App\Models\BrokenLinkReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $query = Movie::published()->with('genres');
        
        // Search by title/description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Filter by multiple genres
        if ($request->filled('genres')) {
            $genreIds = is_array($request->genres) ? $request->genres : [$request->genres];
            $query->whereHas('genres', function($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            });
        }
        
        // Filter by year range
        if ($request->filled('year_from')) {
            $query->where('year', '>=', $request->year_from);
        }
        if ($request->filled('year_to')) {
            $query->where('year', '<=', $request->year_to);
        }
        
        // Filter by rating range
        if ($request->filled('rating_min')) {
            $query->where('rating', '>=', $request->rating_min);
        }
        
        // Filter by quality
        if ($request->filled('quality')) {
            $qualities = is_array($request->quality) ? $request->quality : [$request->quality];
            $query->whereIn('quality', $qualities);
        }
        
        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            case 'year_desc':
                $query->orderBy('year', 'desc');
                break;
            case 'year_asc':
                $query->orderBy('year', 'asc');
                break;
            case 'rating_desc':
                $query->orderBy('rating', 'desc');
                break;
            case 'rating_asc':
                $query->orderBy('rating', 'asc');
                break;
            case 'views':
                $query->orderBy('view_count', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }
        
        // Get filter data for sidebar
        $genres = Genre::withCount('movies')->orderBy('name')->get();
        $years = Movie::published()
            ->selectRaw('DISTINCT year')
            ->whereNotNull('year')
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        // Stats for active filters
        $activeFiltersCount = 0;
        foreach (['search', 'genres', 'year_from', 'year_to', 'quality', 'rating_min'] as $filter) {
            if ($request->filled($filter)) {
                $activeFiltersCount++;
            }
        }
        
        $movies = $query->paginate(24)->withQueryString();
        
        return view('movies.index', compact('movies', 'genres', 'years', 'activeFiltersCount'));
    }
    
    public function search(Request $request)
    {
        $search = $request->get('search') ?: $request->get('search_alt');
        
        $movies = Movie::published()
            ->with('genres')
            ->search($search)
            ->paginate(20);
            
        $genres = Genre::orderBy('name')->get();
        
        return view('movies.search', compact('movies', 'genres', 'search'));
    }

    public function searchSuggestions(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $movies = Movie::published()
            ->where('title', 'like', "%{$query}%")
            ->select('id', 'title', 'year', 'poster_path', 'slug')
            ->limit(5)
            ->get();
        
        $results = $movies->map(function($movie) {
            return [
                'id' => $movie->id,
                'title' => $movie->title,
                'year' => $movie->year,
                'poster' => $movie->poster_url,
                'url' => route('movies.show', $movie->slug)
            ];
        });
        
        return response()->json($results);
    }

    public function popularSearches()
    {
        // You can implement search tracking table for real data
        // For now, return static popular searches
        $searches = [
            'Action Movies',
            'Comedy 2024', 
            'Marvel',
            'Horror',
            'Top Rated'
        ];
        
        return response()->json($searches);
    }
    
    public function show(Movie $movie)
    {
        // Only show published movies to non-admin users
        if (!$movie->isPublished() && (!Auth::check() || !Auth::user()->isAdmin())) {
            abort(404);
        }
        
        $movie->load('genres');
        
        // Increment view count
        $movie->incrementViews();
        
        // Get related movies
        $relatedMovies = Movie::published()
            ->where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($movie) {
                $query->whereIn('genres.id', $movie->genres->pluck('id'));
            })
            ->take(5)
            ->get();
        
        return view('movies.show', compact('movie', 'relatedMovies'));
    }
    
    public function genre(Genre $genre)
    {
        $movies = Movie::published()
            ->with('genres')
            ->byGenre($genre->slug)
            ->latest()
            ->paginate(20);
            
        $genres = Genre::orderBy('name')->get();
        
        return view('movies.genre', compact('movies', 'genre', 'genres'));
    }
    
    /**
     * Enhanced play method with multiple sources and quality selection
     */
    public function play(Request $request, Movie $movie)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to watch movies.');
        }
        
        // Only show published movies to non-admin
        if (!$movie->isPublished() && !Auth::user()->isAdmin()) {
            abort(404);
        }
        
        // Get all active sources ordered by priority and quality
        $sources = $movie->sources()
            ->active()
            ->ordered()
            ->get();
        
        // If no sources, try to use main embed_url
        if ($sources->isEmpty() && $movie->embed_url) {
            // Create temporary source object for backward compatibility
            $tempSource = new MovieSource([
                'movie_id' => $movie->id,
                'source_name' => 'Main Server',
                'embed_url' => $movie->embed_url,
                'quality' => $movie->quality,
                'is_active' => true,
                'priority' => 0
            ]);
            $sources = collect([$tempSource]);
        }
        
        // No sources available at all
        if ($sources->isEmpty()) {
            return redirect()->route('movies.show', $movie->slug)
                ->with('error', 'Sorry, no video sources available for this movie.');
        }
        
        // Get requested source or best available
        $sourceId = $request->get('source');
        $currentSource = null;
        
        if ($sourceId) {
            $currentSource = $sources->firstWhere('id', $sourceId);
        }
        
        // If no specific source requested or not found, get best quality
        if (!$currentSource) {
            // Sort by quality priority (4K > FHD > HD > TS > CAM)
            $qualityOrder = ['4K' => 5, 'FHD' => 4, 'HD' => 3, 'TS' => 2, 'CAM' => 1];
            $currentSource = $sources->sortByDesc(function ($source) use ($qualityOrder) {
                return $qualityOrder[$source->quality] ?? 0;
            })->first();
        }
        
        // Group sources by quality for selector
        $sourcesByQuality = $sources->groupBy('quality');
        
        // Get best available quality
        $bestQuality = $sources->pluck('quality')->unique()->sortByDesc(function ($quality) {
            $order = ['4K' => 5, 'FHD' => 4, 'HD' => 3, 'TS' => 2, 'CAM' => 1];
            return $order[$quality] ?? 0;
        })->first();
        
        // Get related movies
        $relatedMovies = Movie::published()
            ->where('id', '!=', $movie->id)
            ->whereHas('genres', function ($query) use ($movie) {
                $query->whereIn('genres.id', $movie->genres->pluck('id'));
            })
            ->inRandomOrder()
            ->limit(5)
            ->get();
        
        // Log view (will be done via AJAX after 10 seconds for accuracy)
        // This prevents counting if user immediately leaves
        
        return view('movies.player', compact(
            'movie',
            'sources',
            'currentSource',
            'sourcesByQuality',
            'bestQuality',
            'relatedMovies'
        ));
    }

    /**
     * Track movie view via AJAX (called after 10 seconds of watching)
     */
    public function trackView(Request $request, Movie $movie)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }
        
        // Check if user already viewed in last hour to prevent spam
        $recentView = MovieView::where('movie_id', $movie->id)
            ->where('user_id', Auth::id())
            ->where('watched_at', '>=', now()->subHour())
            ->first();
        
        if (!$recentView) {
            // Log new view
            MovieView::create([
                'movie_id' => $movie->id,
                'user_id' => Auth::id(),
                'watched_at' => now(),
                'ip_address' => $request->ip()
            ]);
            
            // Increment movie view counter
            $movie->incrementViews();
            
            return response()->json(['success' => true, 'counted' => true]);
        }
        
        return response()->json(['success' => true, 'counted' => false]);
    }

    /**
     * Report broken link or issue
     */
    public function reportIssue(Request $request, Movie $movie)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Please login to report issues.'], 401);
        }
        
        $validated = $request->validate([
            'source_id' => 'nullable|exists:movie_sources,id',
            'issue_type' => 'required|in:not_loading,wrong_movie,poor_quality,no_audio,no_subtitle,buffering,other',
            'description' => 'nullable|string|max:500'
        ]);
        
        // Check if user already reported this in last 24 hours
        $existingReport = BrokenLinkReport::where('movie_id', $movie->id)
            ->where('user_id', Auth::id())
            ->where('created_at', '>=', now()->subDay())
            ->first();
        
        if ($existingReport) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reported an issue for this movie recently.'
            ], 429);
        }
        
        // Create report
        $report = BrokenLinkReport::create([
            'movie_id' => $movie->id,
            'movie_source_id' => $validated['source_id'] ?? null,
            'user_id' => Auth::id(),
            'issue_type' => $validated['issue_type'],
            'description' => $validated['description'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => 'pending'
        ]);
        
        // Increment report count on source if specified
        if ($validated['source_id']) {
            $source = MovieSource::find($validated['source_id']);
            if ($source) {
                $source->increment('report_count');
                
                // Auto-disable if too many reports (10+)
                if ($source->report_count >= 10) {
                    $source->update([
                        'is_active' => false,
                        'notes' => 'Auto-disabled due to multiple reports at ' . now()
                    ]);
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for reporting! We will check this issue soon.'
        ]);
    }
}
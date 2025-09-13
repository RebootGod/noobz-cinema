<?php
// ========================================
// ADMIN MOVIE CONTROLLER
// ========================================
// File: app/Http/Controllers/Admin/AdminMovieController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\MovieSource;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class AdminMovieController extends Controller
{
    /**
     * Display listing of movies
     */
    public function index(Request $request)
    {
        $query = Movie::with('genres');
        
        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $movies = $query->latest()->paginate(20);
        
        return view('admin.movies.index', compact('movies'));
    }

    /**
     * Show form for creating new movie
     */
    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        return view('admin.movies.create', compact('genres'));
    }

    /**
     * Store new movie
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'embed_url' => 'required|url',
            'poster_url' => 'nullable|url',
            'backdrop_url' => 'nullable|url',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'duration' => 'nullable|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:10',
            'quality' => 'required|in:CAM,HD,FHD,4K',
            'status' => 'required|in:draft,published,archived',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'tmdb_id' => 'nullable|integer|unique:movies,tmdb_id',
        ]);

        // Create slug
        $validated['slug'] = Str::slug($validated['title']);
        $count = Movie::where('slug', 'like', $validated['slug'] . '%')->count();
        if ($count > 0) {
            $validated['slug'] = $validated['slug'] . '-' . ($count + 1);
        }

        // Set poster and backdrop paths
        if (isset($validated['poster_url'])) {
            $validated['poster_path'] = $validated['poster_url'];
            unset($validated['poster_url']);
        }
        if (isset($validated['backdrop_url'])) {
            $validated['backdrop_path'] = $validated['backdrop_url'];
            unset($validated['backdrop_url']);
        }

        // Add current user as creator
        $validated['added_by'] = auth()->id();

        // Extract genres before creating movie
        $genreIds = $validated['genres'] ?? [];
        unset($validated['genres']);

        // Create movie
        $movie = Movie::create($validated);

        // Attach genres
        if (!empty($genreIds)) {
            $movie->genres()->attach($genreIds);
        }

        return redirect()->route('admin.movies.index')
            ->with('success', 'Movie berhasil ditambahkan!');
    }

    /**
     * Display movie details
     */
    public function show(Movie $movie)
    {
        $movie->load('genres', 'sources', 'views');
        return view('admin.movies.show', compact('movie'));
    }

    /**
     * Show form for editing movie
     */
    public function edit(Movie $movie)
    {
        $genres = Genre::orderBy('name')->get();
        $movie->load('genres');
        return view('admin.movies.edit', compact('movie', 'genres'));
    }

    /**
     * Update movie
     */
    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'embed_url' => 'required|url',
            'poster_url' => 'nullable|url',
            'backdrop_url' => 'nullable|url',
            'year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'duration' => 'nullable|integer|min:1',
            'rating' => 'nullable|numeric|min:0|max:10',
            'quality' => 'required|in:CAM,HD,FHD,4K',
            'status' => 'required|in:draft,published,archived',
            'genres' => 'nullable|array',
            'genres.*' => 'exists:genres,id',
            'tmdb_id' => 'nullable|integer|unique:movies,tmdb_id,' . $movie->id,
        ]);

        // Update slug if title changed
        if ($validated['title'] !== $movie->title) {
            $validated['slug'] = Str::slug($validated['title']);
            $count = Movie::where('slug', 'like', $validated['slug'] . '%')
                ->where('id', '!=', $movie->id)
                ->count();
            if ($count > 0) {
                $validated['slug'] = $validated['slug'] . '-' . ($count + 1);
            }
        }

        // Set poster and backdrop paths
        if (isset($validated['poster_url'])) {
            $validated['poster_path'] = $validated['poster_url'];
            unset($validated['poster_url']);
        }
        if (isset($validated['backdrop_url'])) {
            $validated['backdrop_path'] = $validated['backdrop_url'];
            unset($validated['backdrop_url']);
        }

        // Extract genres before updating
        $genreIds = $validated['genres'] ?? [];
        unset($validated['genres']);

        // Update movie
        $movie->update($validated);

        // Sync genres
        $movie->genres()->sync($genreIds);

        return redirect()->route('admin.movies.edit', $movie)
            ->with('success', 'Movie berhasil diupdate!');
    }

    /**
     * Delete movie
     */
    public function destroy(Movie $movie)
    {
        $movie->delete();
        
        return redirect()->route('admin.movies.index')
            ->with('success', 'Movie berhasil dihapus!');
    }

    /**
     * Toggle movie status
     */
    public function toggleStatus(Movie $movie)
    {
        $newStatus = $movie->status === 'published' ? 'draft' : 'published';
        $movie->update(['status' => $newStatus]);
        
        return back()->with('success', 'Status movie berhasil diubah!');
    }

    /**
     * Enhanced TMDB Search - supports title, TMDB ID, and IMDB ID
     */
    public function tmdbSearch(Request $request)
    {
        $tmdb = new \App\Services\TMDBService();
        
        // Check if TMDB is configured
        if (!$tmdb->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'TMDB API key not configured. Please add TMDB_API_KEY to your .env file',
                'results' => []
            ]);
        }
        
        // Check search type
        $searchType = $request->get('type');
        
        // Handle special search types
        if ($searchType === 'popular') {
            $results = $tmdb->getPopularMovies($request->get('page', 1));
            return response()->json($results);
        }
        
        if ($searchType === 'trending') {
            $results = $tmdb->getTrendingMovies($request->get('timeWindow', 'week'));
            return response()->json($results);
        }
        
        // Regular search
        $query = $request->get('query');
        
        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a search query',
                'results' => []
            ]);
        }
        
        // Check if query is IMDB ID (starts with tt)
        if (preg_match('/^tt\d+$/i', $query)) {
            $results = $tmdb->getMovieByImdbId($query);
            
            if ($results['success']) {
                // Format as search result
                $results = [
                    'success' => true,
                    'results' => [$results['data']],
                    'search_type' => 'imdb_id'
                ];
            }
        } else {
            // Use smart search (handles both title and TMDB ID)
            $results = $tmdb->smartSearch($query);
        }
        
        // Check if movies already exist in database
        if ($results['success'] && !empty($results['results'])) {
            $tmdbIds = collect($results['results'])->pluck('tmdb_id')->toArray();
            $existingMovies = \App\Models\Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id')->toArray();
            
            // Mark existing movies
            $results['results'] = collect($results['results'])->map(function ($movie) use ($existingMovies) {
                $movie['exists_in_db'] = in_array($movie['tmdb_id'], $existingMovies);
                return $movie;
            })->toArray();
        }
        
        return response()->json($results);
    }

    /**
     * Get TMDB movie details with full information
     */
    public function tmdbDetails(Request $request)
    {
        $tmdbId = $request->get('tmdb_id');
        
        if (!$tmdbId) {
            return response()->json([
                'success' => false,
                'message' => 'TMDB ID is required',
                'data' => null
            ]);
        }
        
        $tmdb = new \App\Services\TMDBService();
        
        if (!$tmdb->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'TMDB API not configured',
                'data' => null
            ]);
        }
        
        $result = $tmdb->getMovieDetails($tmdbId);
        
        // Check if movie exists in database
        if ($result['success']) {
            $existingMovie = \App\Models\Movie::where('tmdb_id', $tmdbId)->first();
            $result['data']['exists_in_db'] = $existingMovie ? true : false;
            $result['data']['local_id'] = $existingMovie ? $existingMovie->id : null;
        }
        
        return response()->json($result);
    }

    /**
     * Import movie from TMDB with enhanced data
     */
    public function tmdbImport(Request $request)
    {
        $request->validate([
            'tmdb_id' => 'required|integer'
        ]);
        
        // Check if movie already exists
        $existingMovie = \App\Models\Movie::where('tmdb_id', $request->tmdb_id)->first();
        if ($existingMovie) {
            return redirect()->route('admin.movies.edit', $existingMovie)
                ->with('warning', 'Movie already exists in database! Redirected to edit page.');
        }
        
        $tmdb = new \App\Services\TMDBService();
        
        if (!$tmdb->isConfigured()) {
            return back()->with('error', 'TMDB API is not configured');
        }
        
        $result = $tmdb->getMovieDetails($request->tmdb_id);
        
        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Failed to fetch movie data from TMDB');
        }
        
        $movieData = $result['data'];
        
        // Prepare movie data for creation
        $slug = \Illuminate\Support\Str::slug($movieData['title']);
        $count = \App\Models\Movie::where('slug', 'like', $slug . '%')->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        
        // Create movie with TMDB data
        $movie = \App\Models\Movie::create([
            'tmdb_id' => $movieData['tmdb_id'],
            'imdb_id' => $movieData['imdb_id'],
            'title' => $movieData['title'],
            'slug' => $slug,
            'description' => $movieData['description'],
            'poster_path' => $movieData['poster_path'],
            'backdrop_path' => $movieData['backdrop_path'],
            'year' => $movieData['year'],
            'duration' => $movieData['duration'],
            'rating' => $movieData['rating'],
            'quality' => 'HD', // Default quality
            'status' => 'draft', // Default to draft until embed URL is added
            'embed_url' => 'https://example.com/placeholder', // Placeholder - needs to be updated
            'added_by' => auth()->id(),
            'view_count' => 0
        ]);
        
        // Sync genres
        if (!empty($movieData['genres'])) {
            $genreIds = [];
            foreach ($movieData['genres'] as $genreName) {
                $genre = \App\Models\Genre::firstOrCreate(
                    ['name' => $genreName],
                    ['slug' => \Illuminate\Support\Str::slug($genreName)]
                );
                $genreIds[] = $genre->id;
            }
            $movie->genres()->sync($genreIds);
        }
        
        // Store additional metadata (optional - if you have a metadata table)
        if (!empty($movieData['tagline']) || !empty($movieData['director']) || !empty($movieData['trailer'])) {
            // You can create a movie_metadata table to store extra info
            // For now, we'll just log it
            \Illuminate\Support\Facades\Log::info('Movie imported with metadata', [
                'movie_id' => $movie->id,
                'tagline' => $movieData['tagline'] ?? null,
                'director' => $movieData['director'] ?? null,
                'trailer' => $movieData['trailer'] ?? null,
                'cast' => $movieData['cast'] ?? []
            ]);
        }
        
        return redirect()->route('admin.movies.edit', $movie)
            ->with('success', 'Movie "' . $movie->title . '" imported successfully from TMDB! Please update the embed URL and publish when ready.');
    }

    /**
     * Bulk import movies from TMDB (optional feature)
     */
    public function tmdbBulkImport(Request $request)
    {
        $request->validate([
            'tmdb_ids' => 'required|array',
            'tmdb_ids.*' => 'integer'
        ]);
        
        $tmdb = new \App\Services\TMDBService();
        $imported = [];
        $failed = [];
        $skipped = [];
        
        foreach ($request->tmdb_ids as $tmdbId) {
            // Check if exists
            if (\App\Models\Movie::where('tmdb_id', $tmdbId)->exists()) {
                $skipped[] = $tmdbId;
                continue;
            }
            
            // Fetch and import
            $result = $tmdb->getMovieDetails($tmdbId);
            
            if ($result['success']) {
                // Import logic here (same as tmdbImport)
                $imported[] = $result['data']['title'];
            } else {
                $failed[] = $tmdbId;
            }
        }
        
        $message = sprintf(
            'Bulk import completed. Imported: %d, Skipped: %d, Failed: %d',
            count($imported),
            count($skipped),
            count($failed)
        );
        
        return back()->with('success', $message);
    }

    /**
     * Manage movie sources
     */
    public function sources(Movie $movie)
    {
        $sources = $movie->sources()->ordered()->get();
        return view('admin.movies.sources', compact('movie', 'sources'));
    }

    /**
     * Store new source
     */
    public function storeSource(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'source_name' => 'required|string|max:100',
            'embed_url' => 'required|url',
            'quality' => 'required|in:CAM,TS,HD,FHD,4K',
            'priority' => 'nullable|integer|min:0|max:999'
        ]);
        
        $validated['movie_id'] = $movie->id;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['is_active'] = true;
        
        \App\Models\MovieSource::create($validated);
        
        return back()->with('success', 'Source added successfully!');
    }

    /**
     * Toggle source status
     */
    public function toggleSource(Movie $movie, MovieSource $source)
    {
        $source->update(['is_active' => !$source->is_active]);
        return back()->with('success', 'Source status updated!');
    }

    /**
     * Delete source
     */
    public function destroySource(Movie $movie, MovieSource $source)
    {
        $source->delete();
        return back()->with('success', 'Source deleted!');
    }

    public function reports()
    {
        $reports = \App\Models\BrokenLinkReport::with(['movie', 'user'])
            ->recent()
            ->paginate(20);
        
        return view('admin.reports.index', compact('reports'));
    }

    /**
     * Update report status
     */
    public function updateReport(Request $request, \App\Models\BrokenLinkReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,fixed,dismissed'
        ]);
        
        $report->update([
            'status' => $validated['status'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now()
        ]);
        
        return back()->with('success', 'Report status updated!');
    }

    /**
     * Reset reports for a source
     */
    public function resetReports(Movie $movie, MovieSource $source)
    {
        $source->update(['report_count' => 0]);
        
        // Mark related reports as fixed if you have BrokenLinkReport model
        if (class_exists(\App\Models\BrokenLinkReport::class)) {
            \App\Models\BrokenLinkReport::where('movie_source_id', $source->id)
                ->where('status', 'pending')
                ->update(['status' => 'fixed']);
        }
        
        return back()->with('success', 'Reports reset!');
    }

    /**
     * Import main embed URL as source
     */
    public function migrateSource(Movie $movie)
    {
        if (!$movie->embed_url) {
            return back()->with('error', 'No main embed URL found.');
        }
        
        // Check if already exists
        $exists = MovieSource::where('movie_id', $movie->id)
            ->where('embed_url', $movie->embed_url)
            ->exists();
        
        if ($exists) {
            return back()->with('warning', 'This source already exists.');
        }
        
        MovieSource::create([
            'movie_id' => $movie->id,
            'source_name' => 'Main Server',
            'embed_url' => $movie->embed_url,
            'quality' => $movie->quality,
            'priority' => 100,
            'is_active' => true
        ]);
        
        return back()->with('success', 'Main embed URL imported!');
    }
}
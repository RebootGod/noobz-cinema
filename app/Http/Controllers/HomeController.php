<?php
// ========================================
// HOME CONTROLLER WITH SEARCH & FILTERS
// ========================================
// File: app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Display homepage with movies and filters
     */
    public function index(Request $request)
    {
        // Start query
        $query = Movie::query();
        
        // ========================================
        // SEARCH FUNCTIONALITY
        // ========================================
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('original_title', 'LIKE', '%' . $search . '%')
                  ->orWhere('overview', 'LIKE', '%' . $search . '%')
                  ->orWhere('cast', 'LIKE', '%' . $search . '%')
                  ->orWhere('director', 'LIKE', '%' . $search . '%');
            });
        }
        
        // ========================================
        // GENRE FILTER
        // ========================================
        if ($genre = $request->input('genre')) {
            $query->whereHas('genres', function($q) use ($genre) {
                $q->where('slug', $genre)
                  ->orWhere('name', 'LIKE', '%' . $genre . '%');
            });
        }
        
        // ========================================
        // YEAR FILTER
        // ========================================
        if ($year = $request->input('year')) {
            switch($year) {
                case '2010s':
                    $query->whereBetween('year', [2010, 2019]);
                    break;
                case '2000s':
                    $query->whereBetween('year', [2000, 2009]);
                    break;
                case '90s':
                    $query->whereBetween('year', [1990, 1999]);
                    break;
                case '80s':
                    $query->whereBetween('year', [1980, 1989]);
                    break;
                case 'older':
                    $query->where('year', '<', 1980);
                    break;
                default:
                    if (is_numeric($year)) {
                        $query->where('year', $year);
                    }
                    break;
            }
        }
        
        // ========================================
        // QUALITY FILTER
        // ========================================
        if ($quality = $request->input('quality')) {
            $query->where('quality', $quality);
        }
        
        // ========================================
        // RATING FILTER
        // ========================================
        if ($rating = $request->input('rating')) {
            $query->where('rating', '>=', $rating);
        }
        
        // ========================================
        // LANGUAGE FILTER
        // ========================================
        if ($language = $request->input('language')) {
            $query->where('language', $language);
        }
        
        // ========================================
        // CHECKBOX FILTERS
        // ========================================
        if ($request->input('subtitle')) {
            $query->where('has_subtitle', true);
        }
        
        if ($request->input('dubbed')) {
            $query->where('is_dubbed', true);
        }
        
        if ($request->input('trending')) {
            // Trending = most viewed in last 7 days
            $query->whereHas('views', function($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            })
            ->withCount(['views' => function($q) {
                $q->where('created_at', '>=', now()->subDays(7));
            }])
            ->having('views_count', '>', 100);
        }
        
        if ($request->input('new')) {
            // New releases = added in last 30 days
            $query->where('created_at', '>=', now()->subDays(30));
        }
        
        // ========================================
        // SORTING
        // ========================================
        $sort = $request->input('sort', 'latest');
        switch($sort) {
            case 'release':
                $query->orderBy('release_date', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('popularity', 'desc');
                break;
            case 'views':
                $query->withCount('views')
                      ->orderBy('views_count', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'latest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }
        
        // ========================================
        // EXECUTE QUERY WITH PAGINATION
        // ========================================
        $movies = $query->with(['genres']) // Eager load relationships
                       ->where('is_active', true) // Only active movies
                       ->paginate(24) // 24 movies per page
                       ->withQueryString(); // Preserve query parameters
        
        // Get genres for filter dropdown
        $genres = Genre::orderBy('name')->get();
        
        // View data
        $data = [
            'movies' => $movies,
            'genres' => $genres,
            'searchTerm' => $search,
            'activeFilters' => $this->getActiveFilters($request)
        ];
        
        return view('home', $data);
    }
    
    /**
     * Get list of active filters for display
     */
    private function getActiveFilters(Request $request)
    {
        $filters = [];
        
        $filterParams = [
            'search', 'genre', 'year', 'quality', 
            'rating', 'language', 'subtitle', 
            'dubbed', 'trending', 'new'
        ];
        
        foreach ($filterParams as $param) {
            if ($value = $request->input($param)) {
                $filters[$param] = $value;
            }
        }
        
        return $filters;
    }
    
    /**
     * Search suggestions for autocomplete
     */
    public function searchSuggestions(Request $request)
    {
        $query = $request->input('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $movies = Movie::where('title', 'LIKE', '%' . $query . '%')
                      ->orWhere('original_title', 'LIKE', '%' . $query . '%')
                      ->select('id', 'title', 'year', 'poster_url')
                      ->limit(10)
                      ->get();
        
        return response()->json($movies);
    }
    
    /**
     * Get popular searches
     */
    public function popularSearches()
    {
        // Track search queries in database for analytics
        $popular = DB::table('search_logs')
                    ->select('query', DB::raw('COUNT(*) as count'))
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('query')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get();
        
        return response()->json($popular);
    }
}
<?php
// ========================================
// ENHANCED TMDB SERVICE
// ========================================
// File: app/Services/TMDBService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TMDBService
{
    protected $apiKey;
    protected $baseUrl;
    protected $imageUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key', env('TMDB_API_KEY'));
        $this->baseUrl = config('services.tmdb.base_url', 'https://api.themoviedb.org/3');
        $this->imageUrl = config('services.tmdb.image_url', 'https://image.tmdb.org/t/p');
    }

    /**
     * Smart search - detects if input is ID or title
     */
    public function smartSearch($query)
    {
        // Check if query is numeric (TMDB ID)
        if (is_numeric($query)) {
            // Try to get movie by ID first
            $movieDetails = $this->getMovieDetails($query);
            
            if ($movieDetails['success']) {
                // Format as search result array
                return [
                    'success' => true,
                    'results' => [$movieDetails['data']],
                    'total_results' => 1,
                    'search_type' => 'id'
                ];
            }
        }
        
        // If not numeric or ID search failed, search by title
        return $this->searchMovies($query);
    }

    /**
     * Search movies by title
     */
    public function searchMovies($query, $page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/search/movie", [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
                'language' => 'en-US',
                'include_adult' => false
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $results = collect($data['results'])->map(function ($movie) {
                    return $this->formatMovieData($movie);
                })->toArray();

                return [
                    'success' => true,
                    'results' => $results,
                    'total_pages' => $data['total_pages'] ?? 1,
                    'total_results' => $data['total_results'] ?? 0,
                    'current_page' => $data['page'] ?? 1,
                    'search_type' => 'title'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to search movies',
                'results' => []
            ];

        } catch (\Exception $e) {
            Log::error('TMDB Search Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error searching TMDB: ' . $e->getMessage(),
                'results' => []
            ];
        }
    }

    /**
     * Get movie details by TMDB ID
     */
    public function getMovieDetails($tmdbId)
    {
        try {
            // Main movie details
            $response = Http::get("{$this->baseUrl}/movie/{$tmdbId}", [
                'api_key' => $this->apiKey,
                'language' => 'en-US',
                'append_to_response' => 'credits,videos,images,external_ids'
            ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => 'Movie not found with ID: ' . $tmdbId
                ];
            }

            $movie = $response->json();

            // Get additional details
            $credits = $movie['credits'] ?? [];
            $videos = $movie['videos']['results'] ?? [];
            $externalIds = $movie['external_ids'] ?? [];

            return [
                'success' => true,
                'data' => [
                    'tmdb_id' => $movie['id'],
                    'imdb_id' => $externalIds['imdb_id'] ?? $movie['imdb_id'] ?? null,
                    'title' => $movie['title'],
                    'original_title' => $movie['original_title'] ?? $movie['title'],
                    'description' => $movie['overview'],
                    'poster_path' => $movie['poster_path'] ? $this->imageUrl . '/w500' . $movie['poster_path'] : null,
                    'backdrop_path' => $movie['backdrop_path'] ? $this->imageUrl . '/original' . $movie['backdrop_path'] : null,
                    'year' => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : null,
                    'release_date' => $movie['release_date'] ?? null,
                    'duration' => $movie['runtime'] ?? null,
                    'rating' => $movie['vote_average'] ?? 0,
                    'vote_count' => $movie['vote_count'] ?? 0,
                    'popularity' => $movie['popularity'] ?? 0,
                    'genres' => collect($movie['genres'] ?? [])->pluck('name')->toArray(),
                    'genre_ids' => collect($movie['genres'] ?? [])->pluck('id')->toArray(),
                    'tagline' => $movie['tagline'] ?? null,
                    'status' => $movie['status'] ?? null,
                    'budget' => $movie['budget'] ?? 0,
                    'revenue' => $movie['revenue'] ?? 0,
                    'production_companies' => collect($movie['production_companies'] ?? [])->pluck('name')->toArray(),
                    'production_countries' => collect($movie['production_countries'] ?? [])->pluck('name')->toArray(),
                    'spoken_languages' => collect($movie['spoken_languages'] ?? [])->pluck('english_name')->toArray(),
                    'director' => $this->getDirector($credits),
                    'cast' => $this->getMainCast($credits, 10),
                    'trailer' => $this->getTrailer($videos),
                    'homepage' => $movie['homepage'] ?? null,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('TMDB Get Details Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error fetching movie details: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get movie by IMDB ID
     */
    public function getMovieByImdbId($imdbId)
    {
        try {
            $response = Http::get("{$this->baseUrl}/find/{$imdbId}", [
                'api_key' => $this->apiKey,
                'external_source' => 'imdb_id'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['movie_results'])) {
                    $movie = $data['movie_results'][0];
                    return $this->getMovieDetails($movie['id']);
                }
            }

            return [
                'success' => false,
                'message' => 'Movie not found with IMDB ID: ' . $imdbId
            ];

        } catch (\Exception $e) {
            Log::error('TMDB IMDB Search Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error searching by IMDB ID: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get popular movies
     */
    public function getPopularMovies($page = 1)
    {
        try {
            $response = Http::get("{$this->baseUrl}/movie/popular", [
                'api_key' => $this->apiKey,
                'page' => $page,
                'language' => 'en-US'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $results = collect($data['results'])->map(function ($movie) {
                    return $this->formatMovieData($movie);
                })->toArray();

                return [
                    'success' => true,
                    'results' => $results,
                    'total_pages' => $data['total_pages'] ?? 1,
                    'current_page' => $data['page'] ?? 1
                ];
            }

            return [
                'success' => false,
                'results' => []
            ];

        } catch (\Exception $e) {
            Log::error('TMDB Popular Movies Error: ' . $e->getMessage());
            return [
                'success' => false,
                'results' => []
            ];
        }
    }

    /**
     * Get trending movies
     */
    public function getTrendingMovies($timeWindow = 'week')
    {
        try {
            $response = Http::get("{$this->baseUrl}/trending/movie/{$timeWindow}", [
                'api_key' => $this->apiKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $results = collect($data['results'])->map(function ($movie) {
                    return $this->formatMovieData($movie);
                })->toArray();

                return [
                    'success' => true,
                    'results' => $results
                ];
            }

            return [
                'success' => false,
                'results' => []
            ];

        } catch (\Exception $e) {
            Log::error('TMDB Trending Movies Error: ' . $e->getMessage());
            return [
                'success' => false,
                'results' => []
            ];
        }
    }

    /**
     * Format movie data consistently
     */
    protected function formatMovieData($movie)
    {
        return [
            'tmdb_id' => $movie['id'],
            'title' => $movie['title'],
            'original_title' => $movie['original_title'] ?? $movie['title'],
            'description' => $movie['overview'] ?? '',
            'poster_path' => $movie['poster_path'] ? $this->imageUrl . '/w500' . $movie['poster_path'] : null,
            'backdrop_path' => $movie['backdrop_path'] ? $this->imageUrl . '/original' . $movie['backdrop_path'] : null,
            'year' => isset($movie['release_date']) ? substr($movie['release_date'], 0, 4) : null,
            'release_date' => $movie['release_date'] ?? null,
            'rating' => $movie['vote_average'] ?? 0,
            'vote_count' => $movie['vote_count'] ?? 0,
            'popularity' => $movie['popularity'] ?? 0,
            'genre_ids' => $movie['genre_ids'] ?? [],
        ];
    }

    /**
     * Get director from credits
     */
    protected function getDirector($credits)
    {
        $crew = $credits['crew'] ?? [];
        $director = collect($crew)->firstWhere('job', 'Director');
        
        return $director ? $director['name'] : null;
    }

    /**
     * Get main cast
     */
    protected function getMainCast($credits, $limit = 10)
    {
        $cast = $credits['cast'] ?? [];
        
        return collect($cast)
            ->take($limit)
            ->map(function ($actor) {
                return [
                    'name' => $actor['name'],
                    'character' => $actor['character'] ?? null,
                    'profile_path' => $actor['profile_path'] ? $this->imageUrl . '/w200' . $actor['profile_path'] : null
                ];
            })
            ->toArray();
    }

    /**
     * Get trailer URL
     */
    protected function getTrailer($videos)
    {
        $trailer = collect($videos)
            ->where('type', 'Trailer')
            ->where('site', 'YouTube')
            ->first();
        
        if ($trailer) {
            return 'https://www.youtube.com/watch?v=' . $trailer['key'];
        }
        
        // Fallback to any YouTube video
        $video = collect($videos)
            ->where('site', 'YouTube')
            ->first();
        
        return $video ? 'https://www.youtube.com/watch?v=' . $video['key'] : null;
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured()
    {
        return !empty($this->apiKey);
    }
}
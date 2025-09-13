<?php
// ========================================
// ENHANCED MOVIE MODEL
// ========================================
// File: app/Models/Movie.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'tmdb_id',
        'title',
        'original_title',
        'slug',
        'overview',
        'poster_url',
        'backdrop_url',
        'trailer_url',
        'embed_url',
        'runtime',
        'release_date',
        'year',
        'rating',
        'vote_count',
        'popularity',
        'language',
        'quality',
        'has_subtitle',
        'is_dubbed',
        'cast',
        'director',
        'is_featured',
        'is_active',
        'view_count'
    ];

    protected $casts = [
        'release_date' => 'date',
        'year' => 'integer',
        'runtime' => 'integer',
        'rating' => 'float',
        'vote_count' => 'integer',
        'popularity' => 'float',
        'has_subtitle' => 'boolean',
        'is_dubbed' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'view_count' => 'integer',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genres');
    }
    
    public function views()
    {
        return $this->hasMany(MovieView::class);
    }
    
    public function watchHistory()
    {
        return $this->hasMany(WatchHistory::class);
    }
    
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // ========================================
    // SEARCH SCOPES
    // ========================================
    
    /**
     * Scope for searching movies
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function($q) use ($search) {
            $searchTerms = explode(' ', $search);
            
            foreach ($searchTerms as $term) {
                $q->where(function($subQuery) use ($term) {
                    $subQuery->where('title', 'LIKE', '%' . $term . '%')
                            ->orWhere('original_title', 'LIKE', '%' . $term . '%')
                            ->orWhere('overview', 'LIKE', '%' . $term . '%')
                            ->orWhere('cast', 'LIKE', '%' . $term . '%')
                            ->orWhere('director', 'LIKE', '%' . $term . '%');
                });
            }
        });
    }
    
    /**
     * Scope for filtering by genre
     */
    public function scopeByGenre(Builder $query, $genre): Builder
    {
        return $query->whereHas('genres', function($q) use ($genre) {
            if (is_numeric($genre)) {
                $q->where('genres.id', $genre);
            } else {
                $q->where('slug', $genre)
                  ->orWhere('name', 'LIKE', '%' . $genre . '%');
            }
        });
    }
    
    /**
     * Scope for filtering by year range
     */
    public function scopeByYearRange(Builder $query, $startYear, $endYear = null): Builder
    {
        if ($endYear) {
            return $query->whereBetween('year', [$startYear, $endYear]);
        }
        return $query->where('year', $startYear);
    }
    
    /**
     * Scope for filtering by quality
     */
    public function scopeByQuality(Builder $query, string $quality): Builder
    {
        return $query->where('quality', $quality);
    }
    
    /**
     * Scope for filtering by minimum rating
     */
    public function scopeMinRating(Builder $query, float $rating): Builder
    {
        return $query->where('rating', '>=', $rating);
    }
    
    /**
     * Scope for featured movies
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
    
    /**
     * Scope for active movies
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
    
    /**
     * Scope for trending movies (most viewed in last N days)
     */
    public function scopeTrending(Builder $query, int $days = 7): Builder
    {
        return $query->whereHas('views', function($q) use ($days) {
            $q->where('created_at', '>=', now()->subDays($days));
        })
        ->withCount(['views' => function($q) use ($days) {
            $q->where('created_at', '>=', now()->subDays($days));
        }])
        ->orderBy('views_count', 'desc');
    }
    
    /**
     * Scope for new releases
     */
    public function scopeNewReleases(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    /**
     * Scope for movies with subtitles
     */
    public function scopeWithSubtitles(Builder $query): Builder
    {
        return $query->where('has_subtitle', true);
    }
    
    /**
     * Scope for dubbed movies
     */
    public function scopeDubbed(Builder $query): Builder
    {
        return $query->where('is_dubbed', true);
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Get formatted runtime
     */
    public function getFormattedRuntimeAttribute(): string
    {
        $hours = floor($this->runtime / 60);
        $minutes = $this->runtime % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }
    
    /**
     * Get quality badge color
     */
    public function getQualityColorAttribute(): string
    {
        return match($this->quality) {
            '4k' => 'purple',
            '1080p' => 'green',
            '720p' => 'blue',
            'cam' => 'red',
            default => 'gray'
        };
    }
    
    /**
     * Check if movie is new (added within last 7 days)
     */
    public function getIsNewAttribute(): bool
    {
        return $this->created_at >= now()->subDays(7);
    }
    
    /**
     * Get embed URL (decrypted)
     */
    public function getDecryptedEmbedUrlAttribute(): ?string
    {
        if (!$this->embed_url) {
            return null;
        }
        
        try {
            return decrypt($this->embed_url);
        } catch (\Exception $e) {
            return $this->embed_url; // Return as-is if not encrypted
        }
    }
    
    /**
     * Set embed URL (encrypted)
     */
    public function setEmbedUrlAttribute($value): void
    {
        $this->attributes['embed_url'] = $value ? encrypt($value) : null;
    }
    
    /**
     * Increment view count
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
        
        // Also log in views table for analytics
        if (auth()->check()) {
            $this->views()->create([
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
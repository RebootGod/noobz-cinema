<?php
// ========================================
// USER PROFILE CONTROLLER
// ========================================
// File: app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Movie;
use App\Models\MovieView;
use App\Models\Watchlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get statistics
        $stats = [
            'total_watched' => MovieView::where('user_id', $user->id)->count(),
            'watchlist_count' => Watchlist::where('user_id', $user->id)->count(),
            'member_since' => $user->created_at->diffForHumans(),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never'
        ];
        
        return view('profile.index', compact('user', 'stats'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    /**
     * Update username
     */
    public function updateUsername(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users')->ignore($user->id)
            ]
        ], [
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'username.unique' => 'This username is already taken.'
        ]);
        
        $user->update(['username' => $validated['username']]);
        
        return back()->with('success', 'Username updated successfully!');
    }

    /**
     * Update email
     */
    public function updateEmail(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'current_password' => 'required'
        ]);
        
        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        $user->update(['email' => $validated['email']]);
        
        return back()->with('success', 'Email updated successfully!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.confirmed' => 'New password confirmation does not match.'
        ]);
        
        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }
        
        $user->update(['password' => Hash::make($validated['password'])]);
        
        return back()->with('success', 'Password changed successfully!');
    }

    /**
     * Show watchlist
     */
    public function watchlist()
    {
        $user = Auth::user();
        
        $watchlist = Watchlist::where('user_id', $user->id)
            ->with('movie.genres')
            ->latest()
            ->paginate(20);
        
        return view('profile.watchlist', compact('watchlist'));
    }

    /**
     * Add to watchlist
     */
    public function addToWatchlist(Movie $movie)
    {
        $user = Auth::user();
        
        // Check if already in watchlist
        $exists = Watchlist::where('user_id', $user->id)
            ->where('movie_id', $movie->id)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Movie already in watchlist'
            ]);
        }
        
        Watchlist::create([
            'user_id' => $user->id,
            'movie_id' => $movie->id
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Added to watchlist!'
        ]);
    }

    /**
     * Remove from watchlist
     */
    public function removeFromWatchlist(Movie $movie)
    {
        $user = Auth::user();
        
        Watchlist::where('user_id', $user->id)
            ->where('movie_id', $movie->id)
            ->delete();
        
        return back()->with('success', 'Removed from watchlist!');
    }

    /**
     * Show watch history
     */
    public function history()
    {
        $user = Auth::user();
        
        $history = MovieView::where('user_id', $user->id)
            ->with('movie.genres')
            ->latest('watched_at')
            ->paginate(20);
        
        // Group by date
        $groupedHistory = $history->groupBy(function ($item) {
            return $item->watched_at->format('Y-m-d');
        });
        
        return view('profile.history', compact('history', 'groupedHistory'));
    }

    /**
     * Clear watch history
     */
    public function clearHistory()
    {
        $user = Auth::user();
        
        MovieView::where('user_id', $user->id)->delete();
        
        return back()->with('success', 'Watch history cleared!');
    }
}
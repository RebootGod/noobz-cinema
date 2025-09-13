<?php
// ========================================
// ADMIN USER CONTROLLER - COMPLETE
// ========================================
// File: app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\InviteCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Display listing of users with search and filters
     */
    public function index(Request $request)
    {
        $query = User::with(['registration.inviteCode', 'movieViews']);
        
        // Search by username or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Sort options
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        
        if ($sortBy === 'last_login') {
            $query->orderBy('last_login_at', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }
        
        $users = $query->paginate(20)->withQueryString();
        
        // Get statistics for dashboard
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'banned' => User::where('status', 'banned')->count(),
        ];
        
        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Display user details
     */
    public function show(User $user)
    {
        // Load relationships
        $user->load([
            'registration.inviteCode.creator',
            'movieViews.movie',
            'inviteCodes'
        ]);
        
        // Get user statistics
        $stats = [
            'total_views' => $user->movieViews()->count(),
            'unique_movies' => $user->movieViews()->distinct('movie_id')->count('movie_id'),
            'total_watch_time' => $user->movieViews()->sum('watch_duration'),
            'invite_codes_created' => $user->inviteCodes()->count(),
        ];
        
        // Get recent activity
        $recentViews = $user->movieViews()
            ->with('movie')
            ->latest('watched_at')
            ->limit(10)
            ->get();
        
        return view('admin.users.show', compact('user', 'stats', 'recentViews'));
    }

    /**
     * Show form for editing user
     */
    public function edit(User $user)
    {
        // Prevent editing super admin unless you are super admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit a super admin account!');
        }
        
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user information
     */
    public function update(Request $request, User $user)
    {
        // Prevent editing super admin unless you are super admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot edit a super admin account!');
        }
        
        $validated = $request->validate([
            'username' => 'required|string|min:3|max:20|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:member,admin,super_admin',
            'status' => 'required|in:active,inactive,banned',
        ]);
        
        // Only super admin can set super_admin role
        if ($validated['role'] === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            $validated['role'] = 'admin';
        }
        
        // Prevent self-demotion for admin
        if ($user->id === auth()->id() && $validated['role'] !== auth()->user()->role) {
            return back()->with('error', 'You cannot change your own role!');
        }
        
        // Prevent self-ban
        if ($user->id === auth()->id() && $validated['status'] === 'banned') {
            return back()->with('error', 'You cannot ban yourself!');
        }
        
        $user->update($validated);
        
        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        return back()->with('success', 'Password reset successfully! New password: ' . $request->password);
    }

    /**
     * Generate random password
     */
    public function generatePassword()
    {
        $password = Str::random(12);
        
        return response()->json([
            'password' => $password
        ]);
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        // Prevent toggling own status
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change your own status!');
        }
        
        // Prevent toggling super admin unless you are super admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You cannot change super admin status!');
        }
        
        // Toggle between active and inactive (not banned)
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);
        
        return back()->with('success', 'User status updated to ' . $newStatus . '!');
    }

    /**
     * Ban or unban user
     */
    public function toggleBan(User $user)
    {
        // Same checks as toggleStatus
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot ban yourself!');
        }
        
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You cannot ban super admin!');
        }
        
        $newStatus = $user->status === 'banned' ? 'active' : 'banned';
        $user->update(['status' => $newStatus]);
        
        $message = $newStatus === 'banned' ? 'User has been banned!' : 'User ban has been lifted!';
        return back()->with('success', $message);
    }

    /**
     * Delete user account
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }
        
        // Prevent deleting super admin unless you are super admin
        if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'You cannot delete super admin account!');
        }
        
        // Store username for message
        $username = $user->username;
        
        // Delete user (will cascade delete related records)
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', "User {$username} has been deleted!");
    }

    /**
     * Export users to CSV
     */
    public function export()
    {
        $users = User::with('registration.inviteCode')->get();
        
        $csv = "ID,Username,Email,Role,Status,Registered,Last Login,Invite Code\n";
        
        foreach ($users as $user) {
            $inviteCode = $user->registration ? $user->registration->inviteCode->code : 'N/A';
            $lastLogin = $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : 'Never';
            
            $csv .= "{$user->id},{$user->username},{$user->email},{$user->role},{$user->status},";
            $csv .= "{$user->created_at->format('Y-m-d H:i')},{$lastLogin},{$inviteCode}\n";
        }
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="users_' . date('Y-m-d') . '.csv"');
    }
}
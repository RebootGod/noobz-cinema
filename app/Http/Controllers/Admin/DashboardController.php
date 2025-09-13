<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\User;
use App\Models\InviteCode;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_movies' => Movie::count(),
            'total_users' => User::count(),
            'total_invite_codes' => InviteCode::count(),
            'active_users' => User::where('status', 'active')->count(),
            'pending_reports' => \App\Models\BrokenLinkReport::pending()->count(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }
}
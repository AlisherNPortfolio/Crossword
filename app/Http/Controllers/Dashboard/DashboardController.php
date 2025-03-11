<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Crossword;
use App\Models\User;
use App\Models\UserSolution;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'checkRole:administrator,creator',
            'checkPermission:dashboard.access'
        ];
    }

    public function index()
    {
        // Get statistics for dashboard
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('active', true)->count(),
            'total_crosswords' => Crossword::count(),
            'published_crosswords' => Crossword::where('published', true)->count(),
            'total_competitions' => Competition::count(),
            'active_competitions' => Competition::where('is_active', true)->count(),
            'total_solutions' => UserSolution::count(),
            'completed_solutions' => UserSolution::where('completed', true)->count(),
        ];

        // Get recent activity
        $recentCrosswords = Crossword::with('creator:id,name')
            ->latest()
            ->take(5)
            ->get();

        $recentCompetitions = Competition::with('crossword:id,title')
            ->latest()
            ->take(5)
            ->get();

        // If creator, show only their content
        if (Auth::user()->isCreator() && !Auth::user()->isAdmin()) {
            $stats['total_crosswords'] = Crossword::where('created_by', Auth::id())->count();
            $stats['published_crosswords'] = Crossword::where('created_by', Auth::id())
                ->where('published', true)
                ->count();

            $recentCrosswords = Crossword::where('created_by', Auth::id())
                ->with('creator:id,name')
                ->latest()
                ->take(5)
                ->get();

            $competitionIdsFromCreatorCrosswords = Crossword::where('created_by', Auth::id())
                ->pluck('id')
                ->toArray();

            $recentCompetitions = Competition::whereIn('crossword_id', $competitionIdsFromCreatorCrosswords)
                ->with('crossword:id,title')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard.index', [
            'stats' => $stats,
            'recentCrosswords' => $recentCrosswords,
            'recentCompetitions' => $recentCompetitions
        ]);
    }
}

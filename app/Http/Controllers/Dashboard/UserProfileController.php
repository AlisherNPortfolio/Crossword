<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardUserProfilePasswordUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'auth'
        ];
    }

    public function show()
    {
        $user = Auth::user();
        $user->load(['profile', 'solutions', 'competitionResults']);

        // Get user stats
        $stats = [
            'crosswords_solved' => $user->solutions()->where('completed', true)->count(),
            'competitions_participated' => $user->competitionResults()->count(),
            'competitions_won' => $user->competitionResults()->where('ranking', 1)->count(),
            'total_points' => $user->solutions()->sum('score') + $user->competitionResults()->sum('score'),
        ];

        $recentSolutions = $user->solutions()
            ->with('crossword:id,title')
            ->latest()
            ->take(5)
            ->get();

        $recentCompetitions = $user->competitionResults()
            ->with('competition:id,title')
            ->latest()
            ->take(5)
            ->get();

        return view('profile.show', [
            'user' => $user,
            'stats' => $stats,
            'recentSolutions' => $recentSolutions,
            'recentCompetitions' => $recentCompetitions
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        $user->load('profile');

        return view('profile.edit', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'profile_photo' => 'nullable|image|max:1024', // Max 1MB
            'bio' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:100',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            $userData['profile_photo'] = $path;
        }

        $user->update($userData);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'bio' => $request->bio,
                'country' => $request->country,
                'language' => $request->language,
            ]
        );

        return redirect()->route('profile.show')
            ->with('success', 'Profil o\'zgartirildi!');
    }

    public function updatePassword(DashboardUserProfilePasswordUpdateRequest $request)
    {
        $request->validated();

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Joriy parol xato kiritildi!']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Parol yangilandi!');
    }

    public function solutionsHistory()
    {
        $user = Auth::user();

        $solutions = $user->solutions()
            ->with('crossword:id,title,created_by')
            ->latest()
            ->paginate(15);

        return view('profile.solutions', [
            'solutions' => $solutions
        ]);
    }

    public function competitionsHistory()
    {
        $user = auth()->user();

        $competitionResults = $user->competitionResults()
            ->with(['competition:id,title,start_time,end_time', 'competition.crossword:id,title'])
            ->latest()
            ->paginate(15);

        return view('profile.competitions', [
            'competitionResults' => $competitionResults
        ]);
    }
}

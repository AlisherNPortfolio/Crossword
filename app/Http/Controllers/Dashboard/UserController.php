<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'checkRole:administrator',
            new Middleware('checkPermission:users.view', only: ['index', 'show']),
            new Middleware('checkPermission:users.create', only: ['create', 'store']),
            new Middleware('checkPermission:users.edit', only: ['edit', 'update']),
            new Middleware('checkPermission:users.delete', only: ['destroy'])
        ];
    }

    public function index()
    {
        $users = User::with('roles')->paginate(15);

        return view('dashboard.users.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $roles = Role::all();

        return view('dashboard.users.create', [
            'roles' => $roles
        ]);
    }

    public function store(DashboardUserRequest $request)
    {
        $request->validated();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => $request->has('active'),
        ]);

        $user->roles()->attach($request->roles);

        UserProfile::create([
            'user_id' => $user->id
        ]);

        return redirect()->route('dashboard.users.index')
            ->with('success', 'Foydali foydalanuvchi yaratildi.');
    }

    public function show(User $user)
    {
        $user->load(['roles', 'profile', 'solutions', 'competitionResults', 'crosswords']);

        $stats = [
            'crosswords_created' => $user->crosswords()->count(),
            'crosswords_published' => $user->crosswords()->where('published', true)->count(),
            'crosswords_solved' => $user->solutions()->where('completed', true)->count(),
            'competitions_participated' => $user->competitionResults()->count(),
            'competitions_won' => $user->competitionResults()->where('ranking', 1)->count(),
        ];

        return view('dashboard.users.show', [
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userRoles = $user->roles->pluck('id')->toArray();

        return view('dashboard.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,id',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'active' => $request->has('active'),
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);

            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        $user->roles()->sync($request->roles);

        return redirect()->route('dashboard.users.index')
            ->with('success', 'Foydalanuvchi ma\'lumotlari o\'zgartirildi!');
    }

    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return redirect()->route('dashboard.users.index')
                ->with('error', 'Akkauntingizni o\'chira olmaysiz!');
        }

        $user->delete();

        return redirect()->route('dashboard.users.index')
            ->with('success', 'Foydalanuvchi o\'chirildi!');
    }
}

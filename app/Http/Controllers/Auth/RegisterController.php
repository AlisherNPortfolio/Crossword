<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $request->validated();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $solverRole = Role::where('slug', 'solver')->first();

        if ($solverRole) {
            $user->roles()->attach($solverRole->id);
        }

        UserProfile::create([
            'user_id' => $user->id
        ]);

        return redirect()->route('home');

        // if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
        //     $request->session()->regenerate();

        //     return redirect()->intended('home');
        // }

        // return back()->withErrors([
        //     'error' => 'Tizimga kirishda xatolik.',
        // ])->onlyInput('email');
    }
}

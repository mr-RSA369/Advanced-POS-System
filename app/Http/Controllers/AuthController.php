<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        sleep(1);

        // validation
        $feilds = $request->validate([
            'avatar' => ['image', 'nullable', 'max:16000'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        if ($request->hasFile("avatar")) {
            $feilds['avatar'] = Storage::disk("public")->put('avatars', $request->avatar);
        }


        $user = User::create($feilds);

        Auth::login($user);

        return redirect()->route('dashboard');

    }

    public function login(Request $request)
    {
        $feilds = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (Auth::attempt($feilds, $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('home');
        }

        return back()->withErrors([
            'email' => "Invalid credentials!",

        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerate();

        return Inertia::location(route("login"));
    }
}
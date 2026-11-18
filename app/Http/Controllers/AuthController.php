<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Notifier;
use App\Support\Security;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Kept in storage/logs/laravel.log so repeated guessing can be spotted.
            Log::warning('Failed login', ['email' => $credentials['email'], 'ip' => $request->ip()]);

            // The same message whether the email or the password was wrong.
            return back()
                ->withErrors(['email' => 'Those details do not match our records.'])
                ->onlyInput('email');
        }

        // A new session id after logging in stops session fixation.
        $request->session()->regenerate();

        $firstName = Str::before($request->user()->name, ' ');

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Welcome back, '.$firstName.'. Thank you for using MindStack Task Tracker.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Security::passwordRule()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        // Thank-you notification under the bell, and the same by email.
        Notifier::send(
            $user,
            'Thank you for signing up',
            'Welcome to MindStack Task Tracker, '.Str::before($user->name, ' ').'. Create a project and invite your team to get started.',
            route('projects.index'),
        );

        // Sends someone who arrived through an invite link on to that project.
        return redirect()->intended(route('dashboard'))
            ->with('status', 'Thank you for signing up, '.Str::before($user->name, ' ').'! Your account is ready.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

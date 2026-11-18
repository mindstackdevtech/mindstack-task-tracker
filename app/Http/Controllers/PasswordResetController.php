<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Notifier;
use App\Support\Security;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetController extends Controller
{
    /** "Forgot your password?" page. */
    public function showForgot()
    {
        return view('auth.forgot-password');
    }

    /** Emails a reset link. The link works for 60 minutes. */
    public function sendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['email' => 'The email could not be sent. Check the mail settings in .env.'])
                ->onlyInput('email');
        }

        // The same answer whether or not the email has an account,
        // so nobody can use this form to find out who is registered.
        return back()->with('status', 'If an account uses that email, a password reset link has been sent to it.');
    }

    /** Page opened from the link in the email. */
    public function showReset(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Security::passwordRule()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // A new remember token signs out "Keep me logged in" on other devices.
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Notifier::send(
                    $user,
                    'Your password was changed',
                    'Your MindStack Task Tracker password was reset using a link sent to this email. If this was not you, reset it again now.',
                    route('password.request'),
                );
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)])->onlyInput('email');
        }

        return redirect()->route('login')->with('status', 'Your password has been changed. Log in with the new one.');
    }
}

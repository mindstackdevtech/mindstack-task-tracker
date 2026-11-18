<?php

namespace App\Http\Controllers;

use App\Support\Notifier;
use App\Support\Security;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function edit(Request $request)
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $oldEmail = $user->email;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ];

        // Changing the email needs the password, because password reset
        // links go to that address.
        if ($request->input('email') !== $oldEmail) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules, [
            'current_password.required' => 'Enter your password to change your email.',
            'current_password.current_password' => 'That is not your current password.',
        ]);

        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        if ($data['email'] !== $oldEmail) {
            // Warn the old address in case the change was not made by its owner.
            Notifier::mailTo(
                $oldEmail,
                'Your email address was changed',
                'The email on your MindStack Task Tracker account was changed to '.$data['email'].'. If this was not you, log in and change your password now.',
                route('login'),
                'Log in',
            );
        }

        return back()->with('status', 'Your details were saved.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Security::passwordRule()],
        ], [
            'current_password.current_password' => 'That is not your current password.',
            'password.different' => 'The new password must be different from the current one.',
        ]);

        $user = $request->user();

        // A new remember token signs out "Keep me logged in" on other devices.
        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
        ])->save();

        Notifier::send(
            $user,
            'Your password was changed',
            'Your MindStack Task Tracker password was changed from your account page. If this was not you, reset your password now.',
            route('account.edit'),
        );

        return back()->with('status', 'Your password was changed.');
    }
}

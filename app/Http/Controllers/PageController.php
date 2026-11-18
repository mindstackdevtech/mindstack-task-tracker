<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    /**
     * Emails the contact form to the app's own address
     * (CONTACT_EMAIL in .env, or MAIL_FROM_ADDRESS if that is not set).
     */
    public function sendContact(Request $request)
    {
        $data = $request->validate([
            // No line breaks allowed in the short fields.
            'name' => ['required', 'string', 'max:100', 'regex:/^[^\r\n]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:150', 'regex:/^[^\r\n]+$/'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $thanks = 'Thank you. Your message was sent and we will reply by email.';

        // The hidden "website" field is only ever filled in by bots.
        // They get the normal answer, but nothing is sent.
        if ($request->filled('website')) {
            return back()->with('status', $thanks);
        }

        $to = env('CONTACT_EMAIL') ?: config('mail.from.address');

        $text = "New message from the MindStack Task Tracker contact form.\n\n"
            ."Name: {$data['name']}\n"
            ."Email: {$data['email']}\n"
            ."Subject: {$data['subject']}\n\n"
            ."Message:\n{$data['message']}\n";

        try {
            // Plain text only, so nothing typed in the form can run or
            // disguise a link in your inbox. Reply goes to the sender.
            Mail::raw($text, function ($mail) use ($to, $data) {
                $mail->to($to)
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Contact form: '.$data['subject']);
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withErrors(['message' => 'Your message could not be sent right now. Please try again later.'])
                ->withInput();
        }

        return back()->with('status', $thanks);
    }
}

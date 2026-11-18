<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds browser security headers to every page of the app.
 * It is switched on for all routes at the top of routes/web.php.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // The app cannot be shown inside a frame on another site (stops clickjacking).
            'X-Frame-Options' => 'DENY',
            // The browser must not guess file types.
            'X-Content-Type-Options' => 'nosniff',
            // Other sites only learn the app's address, not the page you were on.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // The app never needs these device features.
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            // Only load scripts, styles, fonts and images from this app
            // (plus Google Fonts), and only submit forms back to this app.
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data:",
                "connect-src 'self'",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
            ]),
        ];

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        // Pages behind the login are not kept by the browser, so the Back
        // button shows nothing private after logging out.
        if ($request->user() !== null) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        // On HTTPS, tell the browser to always use HTTPS for this site.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}

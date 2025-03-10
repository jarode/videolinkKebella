<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('user_email')) {
            return redirect()->route('login')
                ->withErrors(['login' => 'Musisz się zalogować, aby uzyskać dostęp.']);
        }

        // Sprawdź czy sesja nie wygasła (24h)
        if (session('login_time') && now()->diffInHours(session('login_time')) > 24) {
            session()->flush();
            return redirect()->route('login')
                ->withErrors(['login' => 'Sesja wygasła. Zaloguj się ponownie.']);
        }

        // Zapisz czas ostatniego dostępu
        session(['last_activity' => now()]);

        return $next($request);
    }
}

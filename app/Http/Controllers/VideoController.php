<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoAccess;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Firebase\JWT\Key;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::all();
        return view('videos.index', compact('videos'));
    }

    public function show(Video $video)
    {
        $user_email = session('user_email');
        
        // Sprawdź czy istnieje dostęp dla tego użytkownika i filmu
        $access = VideoAccess::where('user_email', $user_email)
            ->where('video_id', $video->id)
            ->first();

        // Jeśli dostęp istnieje i wygasł, zwróć błąd
        if ($access && $access->isExpired()) {
            abort(403, 'Twój dostęp do tego filmu wygasł');
        }

        // Jeśli nie ma dostępu, utwórz nowy
        if (!$access) {
            $access = VideoAccess::create([
                'user_email' => $user_email,
                'video_id' => $video->id,
                'token_id' => Str::random(32),
                'views_count' => 0,
                'ip_address' => request()->ip(),
                'last_viewed_at' => now(),
                'expires_at' => now()->addDay()
            ]);
        }

        // Sprawdź czy przekroczono limit odtworzeń
        if ($access->views_count >= 3) {
            abort(403, 'Przekroczono limit odtworzeń dla tego filmu');
        }
        
        $token = $this->generateToken($access);

        return view('videos.show', [
            'video' => $video,
            'token' => $token,
            'access' => $access,
            'remaining_views' => 3 - $access->views_count
        ]);
    }

    private function generateToken(VideoAccess $access)
    {
        $payload = [
            'jti' => $access->token_id,
            'email' => $access->user_email,
            'video_id' => $access->video_id,
            'exp' => $access->expires_at->timestamp,
            'max_views' => 3
        ];

        return JWT::encode($payload, env('JWT_SECRET'), 'HS256');
    }
}

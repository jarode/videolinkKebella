<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoAccess;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::all();
        return view('videos.index', compact('videos'));
    }

    public function show(Video $video, Request $request)
    {
        $token_id = Str::random(32);
        $user_email = session('user_email');
        
        // Tworzenie lub aktualizacja dostępu
        $access = VideoAccess::create([
            'user_email' => $user_email,
            'video_id' => $video->id,
            'token_id' => $token_id,
            'views_count' => 0,
            'ip_address' => $request->ip(),
            'last_viewed_at' => now(),
            'expires_at' => now()->addDay()
        ]);

        $payload = [
            'jti' => $token_id,
            'email' => $user_email,
            'video_id' => $video->id,
            'exp' => $access->expires_at->timestamp,
            'max_views' => 3
        ];

        $token = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        return view('videos.show', compact('video', 'token'));
    }
}

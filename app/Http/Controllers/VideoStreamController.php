<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoAccess;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoStreamController extends Controller
{
    public function stream($token)
    {
        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            
            // Pobierz dostęp z bazy
            $access = VideoAccess::where('token_id', $payload->jti)
                ->where('user_email', $payload->email)
                ->firstOrFail();

            // Sprawdź czy token nie wygasł
            if ($access->isExpired()) {
                abort(403, 'Token wygasł');
            }

            // Sprawdź limit odtworzeń
            if ($access->hasReachedViewLimit()) {
                abort(403, 'Przekroczono limit odtworzeń');
            }

            // Sprawdź czy użytkownik jest zalogowany i czy to ten sam użytkownik
            if (!session('user_email') || session('user_email') !== $payload->email) {
                abort(403, 'Nieprawidłowy użytkownik');
            }

            $video = Video::findOrFail($payload->video_id);
            
            // Sprawdź czy plik istnieje
            if (!Storage::exists("videos/{$video->file_path}")) {
                abort(404, 'Plik wideo nie został znaleziony');
            }

            // Zwiększ licznik odtworzeń
            $access->incrementViews();

            return response()->streamDownload(function() use ($video) {
                $stream = fopen(storage_path("app/videos/{$video->file_path}"), 'rb');
                while (!feof($stream)) {
                    echo fread($stream, 8192);
                    flush();
                }
                fclose($stream);
            }, null, [
                'Content-Type' => 'video/mp4',
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Accept-Ranges' => 'bytes',
                'X-Content-Type-Options' => 'nosniff',
                'Content-Security-Policy' => "default-src 'self'",
                'X-Frame-Options' => 'SAMEORIGIN'
            ]);
        } catch (\Exception $e) {
            abort(403, 'Nieprawidłowy token');
        }
    }
}

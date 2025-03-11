<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoAccess;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264;

class VideoStreamController extends Controller
{
    private $ffmpeg;

    public function __construct()
    {
        $this->ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => '/usr/bin/ffmpeg',
            'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ]);
    }

    private function addWatermark($inputPath, $outputPath, $email)
    {
        $video = $this->ffmpeg->open($inputPath);
        
        // Dodaj znak wodny z adresem email bezpośrednio na wideo
        $video->filters()
            ->custom("drawtext=text='{$email}':x=w-tw-10:y=h-th-10:fontsize=24:fontcolor=white@0.5:shadowcolor=black@0.5:shadowx=2:shadowy=2");
        
        // Utwórz format wideo
        $format = new X264();
        $format->setKiloBitrate(2000);
        
        // Zapisz przetworzone wideo z odpowiednim formatem
        $video->save($format, $outputPath);
        
        return $outputPath;
    }

    public function stream($token, Request $request)
    {
        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            Log::info('Token payload:', ['payload' => $payload]);
            
            // Pobierz dostęp z bazy
            $access = VideoAccess::where('user_email', $payload->email)
                ->where('video_id', $payload->video_id)
                ->firstOrFail();

            Log::info('Znaleziono dostęp:', [
                'id' => $access->id,
                'user_email' => $access->user_email,
                'video_id' => $access->video_id,
                'views_count' => $access->views_count,
                'expires_at' => $access->expires_at
            ]);

            // Sprawdź czy token nie wygasł
            if ($access->isExpired()) {
                Log::warning('Token wygasł');
                abort(403, 'Token wygasł');
            }

            // Sprawdź czy przekroczono limit odtworzeń
            if ($access->views_count >= 3) {
                Log::warning('Przekroczono limit odtworzeń', ['views_count' => $access->views_count]);
                abort(403, 'Przekroczono limit odtworzeń');
            }

            // Sprawdź czy użytkownik jest zalogowany i czy to ten sam użytkownik
            if (!session('user_email') || session('user_email') !== $payload->email) {
                Log::warning('Nieprawidłowy użytkownik', [
                    'session_email' => session('user_email'),
                    'token_email' => $payload->email
                ]);
                abort(403, 'Nieprawidłowy użytkownik');
            }

            $video = Video::findOrFail($payload->video_id);
            $path = storage_path("app/private/videos/{$video->file_path}");
            
            if (!file_exists($path)) {
                Log::error('Plik wideo nie został znaleziony', ['path' => $path]);
                abort(404, 'Plik wideo nie został znaleziony');
            }

            // Generuj unikalną nazwę dla przetworzonego pliku
            $processedPath = storage_path("app/private/processed/" . md5($payload->email . $video->id) . ".mp4");
            
            // Jeśli przetworzony plik nie istnieje, dodaj znak wodny
            if (!file_exists($processedPath)) {
                $this->addWatermark($path, $processedPath, $payload->email);
            }

            // Używaj przetworzonego pliku zamiast oryginalnego
            $path = $processedPath;
            
            // Sprawdź czy to nowe odtworzenie czy kontynuacja
            $lastViewedAt = $access->last_viewed_at;
            $currentTime = now();
            
            // Jeśli minęło więcej niż 5 minut od ostatniego odtworzenia, traktuj jako nowe
            if (!$lastViewedAt || $currentTime->diffInMinutes($lastViewedAt) > 5) {
                Log::info('Zwiększanie licznika odtworzeń - nowe odtworzenie', [
                    'before_views' => $access->views_count,
                    'video_id' => $payload->video_id,
                    'last_viewed_at' => $lastViewedAt,
                    'current_time' => $currentTime
                ]);
                
                $access->increment('views_count');
                $access->update(['last_viewed_at' => $currentTime]);
                
                Log::info('Licznik odtworzeń zwiększony', [
                    'after_views' => $access->views_count
                ]);
            } else {
                Log::info('Pomijanie zwiększania licznika - kontynuacja odtwarzania', [
                    'video_id' => $payload->video_id,
                    'last_viewed_at' => $lastViewedAt,
                    'current_time' => $currentTime
                ]);
            }

            $fileSize = filesize($path);
            $file = fopen($path, 'rb');

            $headers = [
                'Content-Type' => 'video/mp4',
                'Content-Disposition' => 'inline; filename="stream.mp4"',
                'Accept-Ranges' => 'bytes',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'SAMEORIGIN',
                'Content-Security-Policy' => "default-src 'self'; media-src 'self'",
                'Cross-Origin-Resource-Policy' => 'same-origin'
            ];

            if ($request->header('Range')) {
                $range = explode('=', $request->header('Range'))[1];
                list($start, $end) = explode('-', $range);
                
                $end = empty($end) ? $fileSize - 1 : min(abs(intval($end)), $fileSize - 1);
                $start = empty($start) ? 0 : max(abs(intval($start)), 0);

                if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
                    return response('', 416, ['Content-Range' => 'bytes */' . $fileSize]);
                }

                $length = $end - $start + 1;
                fseek($file, $start);
                
                $headers['Content-Length'] = $length;
                $headers['Content-Range'] = sprintf('bytes %d-%d/%d', $start, $end, $fileSize);
                
                return response()->stream(function() use($file, $length) {
                    echo fread($file, $length);
                    fclose($file);
                }, 206, $headers);
            }

            $headers['Content-Length'] = $fileSize;
            
            return response()->stream(function() use($file, $fileSize) {
                echo fread($file, $fileSize);
                fclose($file);
            }, 200, $headers);
            
        } catch (\Exception $e) {
            abort(403, 'Nieprawidłowy token');
        }
    }
}

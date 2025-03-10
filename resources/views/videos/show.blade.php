<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->title }} - Odtwarzacz</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .video-container {
            position: relative;
            width: 100%;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        video {
            width: 100%;
            display: block;
        }
        .watermark {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            opacity: 0.7;
            pointer-events: none;
            text-shadow: 1px 1px 2px #000;
            font-size: 14px;
            padding: 4px 8px;
            background: rgba(0,0,0,0.5);
            border-radius: 4px;
        }
        .video-info {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        h1 {
            margin: 0 0 20px 0;
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="{{ route('videos.index') }}" class="back-link">← Powrót do listy</a>
        
        <div class="video-info">
            <h1>{{ $video->title }}</h1>
            
            <div class="video-container">
                <video controls controlsList="nodownload">
                    <source src="{{ route('videos.stream', ['token' => $token]) }}" type="video/mp4">
                    Twoja przeglądarka nie obsługuje odtwarzania wideo.
                </video>
                <div class="watermark">{{ session('user_email') }}</div>
            </div>
        </div>
    </div>
</body>
</html>

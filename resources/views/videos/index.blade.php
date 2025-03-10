<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista filmów</title>
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
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .logout-form {
            margin: 0;
        }
        .logout-button {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-button:hover {
            background: #dc2626;
        }
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .video-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .video-card:hover {
            transform: translateY(-2px);
        }
        .video-thumbnail {
            width: 100%;
            height: 169px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .video-info {
            padding: 16px;
        }
        .video-title {
            margin: 0;
            font-size: 16px;
            color: #1f2937;
            text-decoration: none;
        }
        .video-title:hover {
            color: #2563eb;
        }
        h1 {
            margin: 0;
            color: #1f2937;
        }
        .user-email {
            color: #6b7280;
            font-size: 14px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Lista filmów</h1>
                <div class="user-email">Zalogowano jako: {{ session('user_email') }}</div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-button">Wyloguj się</button>
            </form>
        </div>

        <div class="videos-grid">
            @foreach($videos as $video)
                <div class="video-card">
                    <div class="video-thumbnail">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" color="#9ca3af">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="video-info">
                        <a href="{{ route('videos.show', $video) }}" class="video-title">
                            {{ $video->title }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</body>
</html>

<?php

namespace Database\Seeders;

use App\Models\VideoAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VideoAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Wygasłe dostępy
        $expiredAccesses = [
            [
                'video_id' => 1,
                'user_email' => 'test@example.com',
                'token_id' => Str::random(32),
                'views_count' => 3,
                'ip_address' => '127.0.0.1',
                'last_viewed_at' => now()->subDays(2),
                'expires_at' => now()->subDay(),
            ],
            [
                'video_id' => 2,
                'user_email' => 'test@example.com',
                'token_id' => Str::random(32),
                'views_count' => 2,
                'ip_address' => '127.0.0.1',
                'last_viewed_at' => now()->subDays(3),
                'expires_at' => now()->subDays(2),
            ],
            [
                'video_id' => 3,
                'user_email' => 'test@example.com',
                'token_id' => Str::random(32),
                'views_count' => 1,
                'ip_address' => '127.0.0.1',
                'last_viewed_at' => now()->subHours(25),
                'expires_at' => now()->subHour(),
            ]
        ];

        foreach ($expiredAccesses as $access) {
            VideoAccess::create($access);
        }
    }
} 
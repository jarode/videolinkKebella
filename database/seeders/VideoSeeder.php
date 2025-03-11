<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'title' => 'Big Buck Bunny',
                'file_path' => 'Big_Buck_Bunny_720_10s_5MB.mp4'
            ],
            [
                'title' => 'Przykładowy Film 2',
                'file_path' => 'sample2.mp4'
            ],
            [
                'title' => 'Przykładowy Film 3',
                'file_path' => 'sample3.mp4'
            ],
            [
                'title' => 'Przykładowy Film 4',
                'file_path' => 'sample4.mp4'
            ]
        ];

        foreach ($videos as $video) {
            Video::create($video);
        }
    }
}

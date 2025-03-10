<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAccess extends Model
{
    protected $fillable = [
        'user_email',
        'video_id',
        'token_id',
        'views_count',
        'ip_address',
        'last_viewed_at',
        'expires_at'
    ];

    protected $casts = [
        'last_viewed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function hasReachedViewLimit(): bool
    {
        return $this->views_count >= 3;
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
        $this->update(['last_viewed_at' => now()]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}

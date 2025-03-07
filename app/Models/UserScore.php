<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'puzzle_id',
        'score',
        'completion_time',
        'completed',
        'progress_data',
    ];

    protected $casts = [
        'score' => 'integer',
        'completion_time' => 'integer',
        'completed' => 'boolean',
        'progress_data' => 'array',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }

    public function getCurrentPosition(): int
    {
        return UserScore::where('puzzle_id', $this->puzzle_id)
            ->where(function ($query) {
                $query->where('score', '>', $this->score)
                    ->orWhere(function ($q) {
                        $q->where('score', '=', $this->score)
                            ->where('completion_time', '<', $this->completion_time);
                    });
            })
            ->count() + 1;
    }
}

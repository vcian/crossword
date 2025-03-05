<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'puzzle_id',
        'question',
        'answer',
        'direction',
        'start_position_x',
        'start_position_y',
        'number',
    ];

    protected $casts = [
        'start_position_x' => 'integer',
        'start_position_y' => 'integer',
        'number' => 'integer',
    ];

    public function puzzle(): BelongsTo
    {
        return $this->belongsTo(Puzzle::class);
    }
}

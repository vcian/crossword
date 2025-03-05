<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Puzzle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'grid_size',
        'time_limit',
        'is_active',
        'grid_data',
    ];

    protected $casts = [
        'grid_data' => 'array',
        'is_active' => 'boolean',
        'time_limit' => 'integer',
        'grid_size' => 'integer',
    ];

    public function clues(): HasMany
    {
        return $this->hasMany(Clue::class);
    }

    public function userScores(): HasMany
    {
        return $this->hasMany(UserScore::class);
    }
}

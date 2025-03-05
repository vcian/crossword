<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserScore;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function index()
    {
        $topScores = UserScore::with(['user', 'puzzle'])
            ->where('completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->paginate(20);

        return view('leaderboard.index', compact('topScores'));
    }

    public function show(Puzzle $puzzle)
    {
        $puzzleScores = UserScore::with('user')
            ->where('puzzle_id', $puzzle->id)
            ->where('completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->paginate(20);

        return view('leaderboard.show', compact('puzzle', 'puzzleScores'));
    }
}

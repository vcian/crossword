<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LeaderboardController extends Controller
{
    public function index()
    {
        $topScores = UserScore::with(['user', 'puzzle'])
            ->where('completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->paginate(20);

        Log::info('Leaderboard Scores:', [
            'count' => $topScores->count(),
            'total' => $topScores->total(),
            'scores' => $topScores->items()
        ]);

        return view('leaderboard.index', compact('topScores'));
    }

    public function show($puzzleId)
    {
        $puzzle = Puzzle::findOrFail($puzzleId);
        
        $leaderboard = UserScore::where('puzzle_id', $puzzleId)
            ->with('user')
            ->where('completed', true)
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->take(100)
            ->get();

        return view('leaderboard.puzzle', [
            'puzzle' => $puzzle,
            'leaderboard' => $leaderboard
        ]);
    }
}

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
        $today = now()->startOfDay();
        
        $topScores = UserScore::with(['user:id,name,email', 'puzzle'])
            ->where('completed', true)
            ->whereDate('created_at', $today)
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->paginate(20);

        Log::info('Today\'s Leaderboard Scores:', [
            'date' => $today->format('Y-m-d'),
            'count' => $topScores->count(),
            'total' => $topScores->total(),
            'scores' => $topScores->items()
        ]);

        return view('leaderboard.index', compact('topScores'));
    }

    public function show($puzzleId)
    {
        $puzzle = Puzzle::findOrFail($puzzleId);
        $today = now()->startOfDay();
        
        $leaderboard = UserScore::where('puzzle_id', $puzzleId)
            ->with('user')
            ->where('completed', true)
            ->whereDate('created_at', $today)
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

<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    public function index()
    {
        $puzzles = Puzzle::where('is_active', true)
            ->latest()
            ->paginate(12);
            
        return view('game.index', compact('puzzles'));
    }

    public function show(Puzzle $puzzle)
    {
        $puzzle->load('clues');
        $userScore = UserScore::where('user_id', auth()->id())
            ->where('puzzle_id', $puzzle->id)
            ->first();

        return view('game.show', compact('puzzle', 'userScore'));
    }

    public function validateAnswer(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'clue_id' => 'required|exists:clues,id',
            'answer' => 'required|string',
        ]);

        $clue = $puzzle->clues()->findOrFail($validated['clue_id']);
        $isCorrect = strtolower($clue->answer) === strtolower($validated['answer']);

        return response()->json([
            'correct' => $isCorrect,
        ]);
    }

    public function submit(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'completion_time' => 'required|integer',
            'answers' => 'required|array',
        ]);

        try {
            // Calculate score based on completion time and correct answers
            $totalClues = $puzzle->clues()->count();
            $correctAnswers = 0;

            foreach ($validated['answers'] as $clueId => $answer) {
                $clue = $puzzle->clues()->findOrFail($clueId);
                if (strtolower($clue->answer) === strtolower($answer)) {
                    $correctAnswers++;
                }
            }

            $score = ($correctAnswers / $totalClues) * 100;

            // Save or update user score
            UserScore::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'puzzle_id' => $puzzle->id,
                ],
                [
                    'score' => $score,
                    'completion_time' => $validated['completion_time'],
                    'completed' => true,
                    'progress_data' => $validated['answers'],
                ]
            );

            return response()->json([
                'success' => true,
                'score' => $score,
                'message' => 'Puzzle completed successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit puzzle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit puzzle. Please try again.',
            ], 500);
        }
    }
}

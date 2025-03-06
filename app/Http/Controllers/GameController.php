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
            ->with(['clues'])
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

        $remainingTime = null;
        if ($puzzle->time_limit) {
            if ($userScore && $userScore->progress_data && isset($userScore->progress_data['start_time'])) {
                $elapsedTime = time() - $userScore->progress_data['start_time'];
                
                $remainingTime = max(0, ($puzzle->time_limit * 60) - $elapsedTime);
            } else {
                // If no progress data, create new progress
                $userScore = UserScore::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'puzzle_id' => $puzzle->id,
                    ],
                    [
                        'score' => 0,
                        'completion_time' => 0,
                        'completed' => false,
                        'progress_data' => [
                            'start_time' => time(),
                            'letters' => []
                        ]
                    ]
                );
                $remainingTime = $puzzle->time_limit * 60;
            }
        }

        return view('game.show', compact('puzzle', 'userScore', 'remainingTime'));
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

    public function completion(Puzzle $puzzle)
    {
        $userScore = UserScore::where('user_id', auth()->id())
            ->where('puzzle_id', $puzzle->id)
            ->firstOrFail();

        $winnerDeclarationTime = now()->setTime(18, 0, 0);
        if ($winnerDeclarationTime->isPast()) {
            $winnerDeclarationTime = $winnerDeclarationTime->addDay();
        }
        $timeRemaining = now()->diffInSeconds($winnerDeclarationTime, false);

        return view('game.completion', compact('puzzle', 'userScore', 'winnerDeclarationTime', 'timeRemaining'));
    }

    public function submit(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'answers' => 'required|array',
            'completion_time' => 'required|integer'
        ]);

        try {
            // Calculate score based on completion time and correct answers
            $totalClues = $puzzle->clues()->count();
            $correctAnswers = 0;

            // First, organize the submitted letters by their positions
            $gridLetters = [];
            foreach ($validated['answers'] as $position => $letter) {
                list($x, $y) = explode('-', $position);
                $gridLetters[$y][$x] = $letter;
            }

            // Check each clue
            foreach ($puzzle->clues as $clue) {
                $submittedAnswer = '';
                $length = strlen($clue->answer);

                // Reconstruct the answer based on direction and starting position
                for ($i = 0; $i < $length; $i++) {
                    if ($clue->direction === 'across') {
                        $x = $clue->start_position_x + $i;
                        $y = $clue->start_position_y;
                    } else {
                        $x = $clue->start_position_x;
                        $y = $clue->start_position_y + $i;
                    }
                    
                    $submittedAnswer .= $gridLetters[$y][$x] ?? '';
                }

                if (strtoupper(trim($submittedAnswer)) === strtoupper($clue->answer)) {
                    $correctAnswers++;
                }
            }

            $score = round(($correctAnswers / $totalClues) * 100, 1);

            // Check if time limit is exceeded
            if ($puzzle->time_limit && $validated['completion_time'] > ($puzzle->time_limit * 60)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time limit exceeded! Please try again.',
                ]);
            }

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
                'message' => "Puzzle completed! Your score: {$score}%",
                'redirect' => route('puzzles.completion', $puzzle),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to submit puzzle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit puzzle. Please try again.',
            ], 500);
        }
    }

    public function updateGameState(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'letters' => 'required|array',
            'completed' => 'required|boolean'
        ]);

        $userScore = UserScore::where('user_id', auth()->id())
            ->where('puzzle_id', $puzzle->id)
            ->first();

        if (!$userScore) {
            $userScore = UserScore::create([
                'user_id' => auth()->id(),
                'puzzle_id' => $puzzle->id,
                'score' => 0,
                'completion_time' => 0,
                'completed' => false,
                'progress_data' => [
                    'start_time' => time(),
                    'letters' => $validated['letters']
                ]
            ]);
        } else {
            $progressData = $userScore->progress_data ?? [];
            $progressData['letters'] = $validated['letters'];
            if (!isset($progressData['start_time'])) {
                $progressData['start_time'] = time();
            }
            $userScore->progress_data = $progressData;
            $userScore->save();
        }

        return response()->json([
            'success' => true,
            'remaining_time' => $puzzle->time_limit ? max(0, ($puzzle->time_limit * 60) - (time() - $userScore->progress_data['start_time'])) : null
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserScore;
use App\Models\Score;
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

        // Get current time and today's declaration time (5:30 PM)
        $now = now();
        $declarationTime = today()->setTime(17, 30, 0);
        $midnight = today()->addDay()->startOfDay(); // Next day midnight

        // Calculate remaining time based on current time
        if ($now->lt($declarationTime)) {
            // Before 5:30 PM - countdown to 5:30 PM
            $timeRemaining = $now->diffInSeconds($declarationTime);
            $countdownCompleted = false;
        } elseif ($now->lt($midnight)) {
            // After 5:30 PM but before midnight - show position
            $timeRemaining = 0;
            $countdownCompleted = true;
        } else {
            // After midnight - redirect to leaderboard or home
            return redirect()->route('leaderboard.puzzle', $puzzle);
        }

        // For debugging
        Log::info('Time Debug', [
            'current_time' => $now->format('Y-m-d H:i:s'),
            'declaration_time' => $declarationTime->format('Y-m-d H:i:s'),
            'midnight' => $midnight->format('Y-m-d H:i:s'),
            'remaining_seconds' => $timeRemaining,
            'countdown_completed' => $countdownCompleted
        ]);

        return view('game.completion', compact(
            'puzzle', 
            'userScore', 
            'timeRemaining',
            'countdownCompleted'
        ));
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

    public function getLeaderboard(Puzzle $puzzle)
    {
        $leaderboard = Score::where('puzzle_id', $puzzle->id)
            ->with('user:id,name')
            ->orderBy('score', 'desc')
            ->orderBy('completion_time', 'asc')
            ->take(10)
            ->get()
            ->map(function ($score) {
                return [
                    'user_id' => $score->user_id,
                    'user_name' => $score->user->name,
                    'score' => number_format($score->score, 1),
                    'completion_time' => $score->completion_time
                ];
            });

        return response()->json($leaderboard);
    }
}

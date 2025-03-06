<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Puzzle;
use App\Models\Clue;
use App\Services\CrosswordGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PuzzleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $puzzles = Puzzle::withCount('clues')->latest()->paginate(10);
        return view('admin.puzzles.index', compact('puzzles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.puzzles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grid_size' => 'required|integer|min:5|max:20',
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'clues' => 'required|array|min:1',
            'clues.*.question' => 'required|string|max:255',
            'clues.*.answer' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (strlen($value) > $request->grid_size) {
                        $fail("The answer length cannot exceed the grid size ({$request->grid_size}).");
                    }
                },
            ],
        ]);

        try {
            DB::beginTransaction();

            // Create puzzle
            $puzzle = Puzzle::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'grid_size' => $validated['grid_size'],
                'time_limit' => $validated['time_limit'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Generate crossword layout
            $generator = new CrosswordGenerator($validated['grid_size']);
            foreach ($validated['clues'] as $clue) {
                $generator->addWord($clue['question'], $clue['answer']);
            }
            
            $result = $generator->generate();

            // Store grid data
            $puzzle->grid_data = $result['grid'];
            $puzzle->save();

            // Create clues
            foreach ($result['clues'] as $clue) {
                Clue::create([
                    'puzzle_id' => $puzzle->id,
                    'question' => $clue['question'],
                    'answer' => $clue['answer'],
                    'direction' => $clue['direction'],
                    'start_position_x' => $clue['start_x'],
                    'start_position_y' => $clue['start_y'],
                    'number' => $clue['number'],
                ]);
            }

            DB::commit();
            return redirect()->route('admin.puzzles.index')
                ->with('success', 'Puzzle created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create puzzle: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to create puzzle. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Puzzle $puzzle)
    {
        $puzzle->load('clues');
        return view('admin.puzzles.edit', compact('puzzle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'grid_size' => [
                'required',
                'integer',
                'min:5',
                'max:20',
            ],
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'clues' => 'nullable|array',
            'clues.*.question' => 'required_with:clues|string|max:255',
            'clues.*.answer' => [
                'required_with:clues',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && strlen($value) > $request->grid_size) {
                        $fail("The answer length cannot exceed the grid size ({$request->grid_size}).");
                    }
                },
            ],
        ]);

        try {
            DB::beginTransaction();

            // Convert is_active to boolean if it's not already
            $isActive = isset($validated['is_active']) ? (bool)$validated['is_active'] : false;
            
            // Handle time_limit - if empty string convert to null
            $timeLimit = !empty($validated['time_limit']) ? (int)$validated['time_limit'] : null;

            // Update puzzle with explicit values
            $puzzle->title = $validated['title'];
            $puzzle->description = $validated['description'];
            $puzzle->grid_size = $validated['grid_size'];
            $puzzle->time_limit = $timeLimit;
            $puzzle->is_active = $isActive;
            
            // Save the changes
            if (!$puzzle->save()) {
                throw new \Exception('Failed to update puzzle data');
            }

            // Update clues if provided
            if (!empty($validated['clues'])) {
                // Delete existing clues
                $puzzle->clues()->delete();

                // Generate new crossword layout
                $generator = new CrosswordGenerator($validated['grid_size']);

                foreach ($validated['clues'] as $clue) {
                    $generator->addWord($clue['question'], $clue['answer']);
                }
                
                $result = $generator->generate();

                // Store grid data
                $puzzle->grid_data = $result['grid'];
                $puzzle->save();

                // Create new clues
                foreach ($result['clues'] as $clue) {
                    Clue::create([
                        'puzzle_id' => $puzzle->id,
                        'question' => $clue['question'],
                        'answer' => $clue['answer'],
                        'direction' => $clue['direction'],
                        'start_position_x' => $clue['start_x'],
                        'start_position_y' => $clue['start_y'],
                        'number' => $clue['number'],
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.puzzles.edit', $puzzle)
                ->with('success', 'Puzzle updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update puzzle: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Failed to update puzzle. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Puzzle $puzzle)
    {
        try {
            $puzzle->delete();
            return redirect()->route('admin.puzzles.index')
                ->with('success', 'Puzzle deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete puzzle: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete puzzle. Please try again.');
        }
    }
}

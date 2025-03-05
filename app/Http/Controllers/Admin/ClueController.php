<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clue;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $puzzleId = $request->query('puzzle_id');
        $clues = Clue::when($puzzleId, function ($query) use ($puzzleId) {
            return $query->where('puzzle_id', $puzzleId);
        })->with('puzzle')->latest()->paginate(15);

        return view('admin.clues.index', compact('clues'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $puzzles = Puzzle::pluck('title', 'id');
        return view('admin.clues.create', compact('puzzles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'puzzle_id' => 'required|exists:puzzles,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'direction' => 'required|in:across,down',
            'start_position_x' => 'required|integer|min:0',
            'start_position_y' => 'required|integer|min:0',
            'number' => 'required|integer|min:1',
        ]);

        try {
            $clue = Clue::create($validated);
            return redirect()->route('admin.clues.edit', $clue)
                ->with('success', 'Clue created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create clue: ' . $e->getMessage());
            return back()->with('error', 'Failed to create clue. Please try again.');
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
    public function edit(Clue $clue)
    {
        $puzzles = Puzzle::pluck('title', 'id');
        return view('admin.clues.edit', compact('clue', 'puzzles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clue $clue)
    {
        $validated = $request->validate([
            'puzzle_id' => 'required|exists:puzzles,id',
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'direction' => 'required|in:across,down',
            'start_position_x' => 'required|integer|min:0',
            'start_position_y' => 'required|integer|min:0',
            'number' => 'required|integer|min:1',
        ]);

        try {
            $clue->update($validated);
            return redirect()->route('admin.clues.edit', $clue)
                ->with('success', 'Clue updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update clue: ' . $e->getMessage());
            return back()->with('error', 'Failed to update clue. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clue $clue)
    {
        try {
            $clue->delete();
            return redirect()->route('admin.clues.index')
                ->with('success', 'Clue deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete clue: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete clue. Please try again.');
        }
    }
}

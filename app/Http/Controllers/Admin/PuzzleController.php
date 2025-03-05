<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Puzzle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PuzzleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $puzzles = Puzzle::latest()->paginate(10);
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
        ]);

        try {
            $puzzle = Puzzle::create($validated);
            return redirect()->route('admin.puzzles.edit', $puzzle)
                ->with('success', 'Puzzle created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create puzzle: ' . $e->getMessage());
            return back()->with('error', 'Failed to create puzzle. Please try again.');
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
            'grid_size' => 'required|integer|min:5|max:20',
            'time_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'grid_data' => 'nullable|array',
        ]);

        try {
            $puzzle->update($validated);
            return redirect()->route('admin.puzzles.edit', $puzzle)
                ->with('success', 'Puzzle updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update puzzle: ' . $e->getMessage());
            return back()->with('error', 'Failed to update puzzle. Please try again.');
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

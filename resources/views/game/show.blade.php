@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="mb-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $puzzle->title }}</h1>
                            <p class="mt-2 text-gray-600">{{ $puzzle->description }}</p>
                        </div>
                        @if($puzzle->time_limit)
                            <div class="text-xl font-bold" id="timer" data-remaining-time="{{ $remainingTime }}">
                                <span id="minutes">{{ str_pad(floor($remainingTime / 60), 2, '0', STR_PAD_LEFT) }}</span>:<span id="seconds">{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Crossword Grid -->
                    <div>
                        <div id="crossword-grid" 
                             class="grid gap-0.5 bg-gray-200 p-0.5 w-fit"
                             data-size="{{ $puzzle->grid_size }}"
                             data-puzzle-id="{{ $puzzle->id }}"
                             data-clues="{{ json_encode($puzzle->clues) }}"
                             style="grid-template-columns: repeat({{ $puzzle->grid_size }}, minmax(0, 1fr));">
                            @for ($y = 0; $y < $puzzle->grid_size; $y++)
                                @for ($x = 0; $x < $puzzle->grid_size; $x++)
                                    @php
                                        $isActive = false;
                                        $number = null;
                                        foreach ($puzzle->clues as $clue) {
                                            if ($clue->start_position_x === $x && $clue->start_position_y === $y) {
                                                $isActive = true;
                                                $number = $clue->number;
                                                break;
                                            }
                                            // Check if this cell is part of any word
                                            if ($clue->direction === 'across' && 
                                                $clue->start_position_y === $y && 
                                                $x >= $clue->start_position_x && 
                                                $x < $clue->start_position_x + strlen($clue->answer)) {
                                                $isActive = true;
                                            }
                                            if ($clue->direction === 'down' && 
                                                $clue->start_position_x === $x && 
                                                $y >= $clue->start_position_y && 
                                                $y < $clue->start_position_y + strlen($clue->answer)) {
                                                $isActive = true;
                                            }
                                        }
                                    @endphp
                                    <div class="crossword-cell relative {{ $isActive ? 'bg-white' : 'bg-gray-800' }}"
                                         data-cell="{{ $x }}-{{ $y }}"
                                         style="aspect-ratio: 1;">
                                        @if($isActive)
                                            @if($number)
                                                <div class="absolute top-0.5 left-0.5 text-xs text-gray-500">{{ $number }}</div>
                                            @endif
                                            <input type="text" 
                                                   maxlength="1" 
                                                   class="w-full h-full text-center uppercase font-bold text-lg bg-transparent focus:outline-none focus:bg-blue-50"
                                                   {{ $userScore && $userScore->completed ? 'disabled' : '' }}>
                                        @endif
                                    </div>
                                @endfor
                            @endfor
                        </div>
                    </div>

                    <!-- Clues -->
                    <div class="space-y-6">
                        <!-- Across Clues -->
                        <div>
                            <h3 class="text-lg font-semibold mb-3">Across</h3>
                            <div class="space-y-2">
                                @foreach ($puzzle->clues->where('direction', 'across')->sortBy('number') as $clue)
                                    <div class="p-2 hover:bg-gray-50 cursor-pointer rounded" 
                                         data-clue="{{ $clue->id }}">
                                        <span class="font-medium">{{ $clue->number }}.</span>
                                        {{ $clue->question }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Down Clues -->
                        <div>
                            <h3 class="text-lg font-semibold mb-3">Down</h3>
                            <div class="space-y-2">
                                @foreach ($puzzle->clues->where('direction', 'down')->sortBy('number') as $clue)
                                    <div class="p-2 hover:bg-gray-50 cursor-pointer rounded"
                                         data-clue="{{ $clue->id }}">
                                        <span class="font-medium">{{ $clue->number }}.</span>
                                        {{ $clue->question }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if(!$userScore || !$userScore->completed)
                            <div class="mt-6">
                                <button id="submit-puzzle" 
                                        class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    Submit Puzzle
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
class CrosswordGrid {
    constructor(size, clues) {
        this.size = size;
        this.clues = clues;
        this.selectedCell = null;
        this.direction = 'across';
    }

    initialize() {
        this.selectedCell = null;
    }

    selectCell(x, y) {
        this.selectedCell = { x, y };
        
        // Find the clue that starts at this position
        const clue = this.clues.find(c => 
            c.start_position_x === x && 
            c.start_position_y === y &&
            c.direction === this.direction
        );

        // If no clue found in current direction, try the other direction
        if (!clue) {
            const otherDirection = this.direction === 'across' ? 'down' : 'across';
            const alternateClue = this.clues.find(c => 
                c.start_position_x === x && 
                c.start_position_y === y &&
                c.direction === otherDirection
            );
            if (alternateClue) {
                this.direction = otherDirection;
                return alternateClue;
            }
        }

        // Highlight the selected cell
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('bg-blue-50');
        });
        const selectedElement = document.querySelector(`[data-cell="${x}-${y}"]`);
        if (selectedElement) {
            selectedElement.classList.add('bg-blue-50');
            const input = selectedElement.querySelector('input');
            if (input) input.focus();
        }

        return clue;
    }

    moveToNextCell() {
        if (!this.selectedCell) return;

        let { x, y } = this.selectedCell;
        
        if (this.direction === 'across') {
            x = Math.min(x + 1, this.size - 1);
        } else {
            y = Math.min(y + 1, this.size - 1);
        }

        const nextCell = document.querySelector(`[data-cell="${x}-${y}"] input`);
        if (nextCell) {
            this.selectCell(x, y);
        }
    }

    moveToPreviousCell() {
        if (!this.selectedCell) return;

        let { x, y } = this.selectedCell;
        
        if (this.direction === 'across') {
            x = Math.max(x - 1, 0);
        } else {
            y = Math.max(y - 1, 0);
        }

        const prevCell = document.querySelector(`[data-cell="${x}-${y}"] input`);
        if (prevCell) {
            this.selectCell(x, y);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const gridElement = document.getElementById('crossword-grid');
    if (!gridElement) return;

    const gridSize = parseInt(gridElement.dataset.size);
    const cluesData = JSON.parse(gridElement.dataset.clues);
    
    // Initialize timer with remaining time from server
    const timerElement = document.getElementById('timer');
    let remainingTime = timerElement ? parseInt(timerElement.dataset.remainingTime) : null;
    let timerInterval;

    function updateTimer() {
        if (remainingTime === null) return;
        
        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            toastr.warning('Time\'s up! Submitting your answers...');
            document.querySelectorAll('.crossword-cell input').forEach(input => {
                input.disabled = true;
            });
            const submitButton = document.getElementById('submit-puzzle');
            if (submitButton) {
                submitButton.disabled = true;
            }
            // Automatically submit the puzzle
            submitPuzzle();
            return;
        }

        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        
        remainingTime--;
    }

    // Extract submit logic into a separate function
    async function submitPuzzle() {
        const answers = {};
        document.querySelectorAll('.crossword-cell input').forEach(input => {
            const cell = input.parentElement.dataset.cell;
            if (input.value) {
                answers[cell] = input.value;
            }
        });

        try {
            const response = await fetch(`/puzzles/${gridElement.dataset.puzzleId}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    answers: answers,
                    completion_time: Math.floor((Date.now() - startTime) / 1000)
                })
            });

            const result = await response.json();
            
            if (result.success) {
                toastr.success(result.message);
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2000);
            } else {
                toastr.error(result.message);
            }
        } catch (error) {
            console.error('Error submitting puzzle:', error);
            toastr.error('An error occurred while submitting the puzzle. Please try again.');
        }
    }

    // Handle puzzle submission
    const submitButton = document.getElementById('submit-puzzle');
    if (submitButton) {
        submitButton.addEventListener('click', submitPuzzle);
    }

    if (remainingTime !== null) {
        updateTimer();
        timerInterval = setInterval(updateTimer, 1000);
    }

    // Save game state periodically
    function saveGameState() {
        const letters = {};
        document.querySelectorAll('.crossword-cell input').forEach(input => {
            const cell = input.parentElement.dataset.cell;
            if (input.value) {
                letters[cell] = input.value;
            }
        });

        fetch(`/puzzles/${gridElement.dataset.puzzleId}/state`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                letters: letters,
                completed: false
            })
        }).then(response => response.json())
        .then(data => {
            if (data.remaining_time !== null) {
                remainingTime = data.remaining_time;
            }
        });
    }

    // Save state every 30 seconds
    setInterval(saveGameState, 30000);

    // Also save state when user enters a letter
    let saveTimeout;
    function debouncedSaveState() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveGameState, 1000);
    }

    const crossword = new CrosswordGrid(gridSize, cluesData);
    crossword.initialize();

    // Handle cell selection
    gridElement.addEventListener('click', function(e) {
        const cell = e.target.closest('.crossword-cell');
        if (!cell) return;

        const [x, y] = cell.dataset.cell.split('-').map(Number);
        const selectedClue = crossword.selectCell(x, y);

        if (selectedClue) {
            // Highlight the selected clue in the clues list
            document.querySelectorAll('[data-clue]').forEach(clueElement => {
                clueElement.classList.remove('bg-blue-100');
                if (clueElement.dataset.clue === selectedClue.id.toString()) {
                    clueElement.classList.add('bg-blue-100');
                }
            });
        }
    });

    // Handle clue selection
    document.querySelectorAll('[data-clue]').forEach(clueElement => {
        clueElement.addEventListener('click', function() {
            const clueId = parseInt(this.dataset.clue);
            const clue = cluesData.find(c => c.id === clueId);
            if (clue) {
                crossword.direction = clue.direction;
                crossword.selectCell(clue.start_position_x, clue.start_position_y);
                const input = document.querySelector(`[data-cell="${clue.start_position_x}-${clue.start_position_y}"] input`);
                if (input) input.focus();
            }
        });
    });

    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (!crossword.selectedCell) return;

        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            e.preventDefault();
            crossword.direction = 'across';
            if (e.key === 'ArrowRight') crossword.moveToNextCell();
            else crossword.moveToPreviousCell();
        } else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            crossword.direction = 'down';
            if (e.key === 'ArrowDown') crossword.moveToNextCell();
            else crossword.moveToPreviousCell();
        }
    });

    // Handle input
    const cells = document.querySelectorAll('.crossword-cell input');
    
    cells.forEach(input => {
        // Handle input changes
        input.addEventListener('input', function(e) {
            e.preventDefault(); // Prevent default behavior
            
            // Clear the input first
            this.value = '';
            
            // Only take the first character and convert to uppercase
            const char = e.data ? e.data[0].toUpperCase() : '';
            if (char && char.match(/[A-Z]/i)) {
                this.value = char;
                
                // Update the grid data
                const [x, y] = this.closest('.crossword-cell').dataset.cell.split('-');
                crossword.grid[y][x].letter = char;
                
                // Move to next cell only after valid input
                crossword.moveToNextCell();
            }
        });

        // Prevent key press default behavior
        input.addEventListener('keypress', function(e) {
            if (e.key.match(/[a-zA-Z]/)) {
                e.preventDefault();
            }
        });

        // Handle backspace
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value === '') {
                e.preventDefault();
                crossword.moveToPreviousCell();
            }
        });

        // Handle cell selection
        input.addEventListener('focus', function() {
            const cell = this.closest('.crossword-cell');
            const [x, y] = cell.dataset.cell.split('-');
            crossword.selectCell(parseInt(x), parseInt(y));
        });
    });
});
</script>
@endpush

@endsection
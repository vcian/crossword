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
                            <!-- <p class="mt-2 text-gray-600">{{ $puzzle->description }}</p> -->
                        </div>
                        @if($puzzle->time_limit)
                            <div class="text-xl font-bold" id="timer" data-remaining-time="{{ $remainingTime }}">
                                <span id="minutes">{{ str_pad(floor($remainingTime / 60), 2, '0', STR_PAD_LEFT) }}</span>:<span id="seconds">{{ str_pad($remainingTime % 60, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Current Question Display -->
                <div id="current-question-display" class="mb-4 p-4 bg-blue-50 rounded-lg shadow-sm hidden">
                    <div class="flex items-start space-x-2">
                        <span class="font-bold text-blue-800" id="current-clue-number"></span>
                        <div>
                            <p class="font-medium text-blue-800" id="current-clue-direction"></p>
                            <p class="text-blue-600" id="current-clue-text"></p>
                        </div>
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
@endsection
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

    findClueForCell(x, y) {
        // First try to find a clue in the current direction
        let clue = this.clues.find(c => {
            if (c.direction !== this.direction) return false;
            
            if (this.direction === 'across') {
                return y === c.start_position_y &&
                       x >= c.start_position_x &&
                       x < c.start_position_x + c.answer.length;
            } else {
                return x === c.start_position_x &&
                       y >= c.start_position_y &&
                       y < c.start_position_y + c.answer.length;
            }
        });

        // If no clue found in current direction, try the other direction
        if (!clue) {
            const otherDirection = this.direction === 'across' ? 'down' : 'across';
            clue = this.clues.find(c => {
                if (c.direction !== otherDirection) return false;
                
                if (otherDirection === 'across') {
                    return y === c.start_position_y &&
                           x >= c.start_position_x &&
                           x < c.start_position_x + c.answer.length;
                } else {
                    return x === c.start_position_x &&
                           y >= c.start_position_y &&
                           y < c.start_position_y + c.answer.length;
                }
            });

            // If found a clue in the other direction, switch to it
            if (clue) {
                this.direction = otherDirection;
            }
        }

        return clue;
    }

    selectCell(x, y) {
        this.selectedCell = { x, y };
        
        // Find the clue for this cell
        const clue = this.findClueForCell(x, y);
        
        // Update visual states
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('bg-blue-50');
        });
        
        const selectedElement = document.querySelector(`[data-cell="${x}-${y}"]`);
        if (selectedElement) {
            selectedElement.classList.add('bg-blue-50');
            const input = selectedElement.querySelector('input');
            if (input) input.focus();
        }

        // Highlight the current word
        this.highlightCurrentWord(clue);
        
        // Update the question display
        this.updateCurrentQuestion(clue);

        return clue;
    }

    highlightCurrentWord(clue) {
        // Remove previous highlights
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('bg-blue-100');
        });

        if (!clue) return;

        // Add highlights for current word
        if (clue.direction === 'across') {
            for (let i = 0; i < clue.answer.length; i++) {
                const cell = document.querySelector(`[data-cell="${clue.start_position_x + i}-${clue.start_position_y}"]`);
                if (cell) cell.classList.add('bg-blue-100');
            }
        } else {
            for (let i = 0; i < clue.answer.length; i++) {
                const cell = document.querySelector(`[data-cell="${clue.start_position_x}-${clue.start_position_y + i}"]`);
                if (cell) cell.classList.add('bg-blue-100');
            }
        }

        // Highlight the clue in the list
        document.querySelectorAll('[data-clue]').forEach(clueElement => {
            clueElement.classList.remove('bg-blue-100');
            if (clueElement.dataset.clue === clue.id.toString()) {
                clueElement.classList.add('bg-blue-100');
            }
        });
    }

    updateCurrentQuestion(clue) {
        const display = document.getElementById('current-question-display');
        const numberElement = document.getElementById('current-clue-number');
        const directionElement = document.getElementById('current-clue-direction');
        const textElement = document.getElementById('current-clue-text');

        if (clue) {
            numberElement.textContent = clue.number + '.';
            directionElement.textContent = clue.direction.charAt(0).toUpperCase() + clue.direction.slice(1);
            textElement.textContent = clue.question;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    }

    toggleDirection() {
        this.direction = this.direction === 'across' ? 'down' : 'across';
        if (this.selectedCell) {
            const clue = this.findClueForCell(this.selectedCell.x, this.selectedCell.y);
            if (clue) {
                this.highlightCurrentWord(clue);
                this.updateCurrentQuestion(clue);
            }
        }
    }

    moveToNextCell() {
        if (!this.selectedCell) return;

        let { x, y } = this.selectedCell;
        let nextCell = null;
        
        if (this.direction === 'across') {
            // Try the immediate next cell first
            nextCell = document.querySelector(`[data-cell="${x + 1}-${y}"] input`);
            if (nextCell) {
                this.selectCell(x + 1, y);
                nextCell.focus();
                nextCell.select();
            }
        } else {
            // Vertical movement remains the same
            let nextY = y + 1;
            while (nextY < this.size) {
                nextCell = document.querySelector(`[data-cell="${x}-${nextY}"] input`);
                if (nextCell) break;
                nextY++;
            }
            
            if (nextCell) {
                this.selectCell(x, nextY);
                nextCell.focus();
                nextCell.select();
            }
        }
    }

    moveToPreviousCell() {
        if (!this.selectedCell) return;

        let { x, y } = this.selectedCell;
        let prevCell = null;
        let prevX = x;
        let prevY = y;
        
        if (this.direction === 'across') {
            prevX = x - 1;
            while (prevX >= 0) {
                prevCell = document.querySelector(`[data-cell="${prevX}-${y}"] input`);
                if (prevCell) break;
                prevX--;
            }
        } else {
            prevY = y - 1;
            while (prevY >= 0) {
                prevCell = document.querySelector(`[data-cell="${x}-${prevY}"] input`);
                if (prevCell) break;
                prevY--;
            }
        }

        if (prevCell) {
            this.selectCell(prevX, prevY);
            prevCell.focus();
            prevCell.select();
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
            // toastr.error('An error occurred while submitting the puzzle. Please try again.');
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
        
        // If clicking the same cell, toggle direction
        if (crossword.selectedCell && 
            crossword.selectedCell.x === x && 
            crossword.selectedCell.y === y) {
            crossword.toggleDirection();
        } else {
            crossword.selectCell(x, y);
        }
    });

    // Handle clue selection from the list
    document.querySelectorAll('[data-clue]').forEach(clueElement => {
        clueElement.addEventListener('click', function() {
            const clueId = parseInt(this.dataset.clue);
            const clue = cluesData.find(c => c.id === clueId);
            if (clue) {
                crossword.direction = clue.direction;
                crossword.selectCell(clue.start_position_x, clue.start_position_y);
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
        } else if (e.code === 'Space') {
            e.preventDefault();
            crossword.toggleDirection();
        }
    });

    // Handle input
    const cells = document.querySelectorAll('.crossword-cell input');
    cells.forEach(input => {
        input.addEventListener('keydown', function(e) {
            const cell = this.closest('.crossword-cell');
            const [x, y] = cell.dataset.cell.split('-').map(Number);

            switch (e.key) {
                case 'Backspace':
                    e.preventDefault();
                    if (this.value) {
                        this.value = '';
                    } else {
                        crossword.moveToPreviousCell();
                    }
                    break;

                case 'ArrowLeft':
                case 'ArrowRight':
                case 'ArrowUp':
                case 'ArrowDown':
                case 'Space':
                    // Let the document keydown handler manage navigation
                    break;

                default:
                    if (e.key.match(/^[a-zA-Z]$/)) {
                        e.preventDefault();
                        this.value = e.key.toUpperCase();
                        // Remove the automatic movement to next cell
                    } else {
                        e.preventDefault();
                    }
                    break;
            }
        });

        input.addEventListener('input', function(e) {
            // Prevent any input event handling as we're handling it in keydown
            e.preventDefault();
            this.value = this.value.slice(-1).toUpperCase();
        });

        // Keep the focus handler
        input.addEventListener('focus', function() {
            const cell = this.closest('.crossword-cell');
            const [x, y] = cell.dataset.cell.split('-').map(Number);
            crossword.selectCell(x, y);
            this.select();
        });
    });

    // Select first active cell on load
    const firstCell = document.querySelector('.crossword-cell.bg-white');
    if (firstCell) {
        const [x, y] = firstCell.dataset.cell.split('-').map(Number);
        crossword.selectCell(x, y);
    }
});
</script>
@endpush

@section('title', 'Crossword Puzzle')

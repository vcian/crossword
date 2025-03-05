<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $puzzle->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Crossword Grid -->
                        <div class="lg:col-span-2">
                            <div id="crossword-grid" class="grid gap-0.5 bg-gray-200 p-0.5" 
                                 style="grid-template-columns: repeat({{ $puzzle->grid_size }}, minmax(0, 1fr));">
                                @for ($i = 0; $i < $puzzle->grid_size; $i++)
                                    @for ($j = 0; $j < $puzzle->grid_size; $j++)
                                        <div class="aspect-square bg-white relative" 
                                             data-x="{{ $j }}" 
                                             data-y="{{ $i }}">
                                            <input type="text" 
                                                   maxlength="1" 
                                                   class="w-full h-full text-center uppercase font-bold text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   data-cell="{{ $j }}-{{ $i }}">
                                            <span class="absolute top-0 left-0 text-xs pl-0.5"></span>
                                        </div>
                                    @endfor
                                @endfor
                            </div>
                        </div>

                        <!-- Clues Panel -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="mb-6">
                                <h3 class="font-semibold text-lg mb-2">Across</h3>
                                <ul class="space-y-2">
                                    @foreach ($puzzle->clues->where('direction', 'across') as $clue)
                                        <li>
                                            <button class="text-left w-full hover:bg-gray-100 p-1 rounded"
                                                    data-clue="{{ $clue->id }}"
                                                    data-number="{{ $clue->number }}"
                                                    data-direction="across"
                                                    data-x="{{ $clue->start_position_x }}"
                                                    data-y="{{ $clue->start_position_y }}">
                                                <span class="font-medium">{{ $clue->number }}.</span>
                                                {{ $clue->question }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div>
                                <h3 class="font-semibold text-lg mb-2">Down</h3>
                                <ul class="space-y-2">
                                    @foreach ($puzzle->clues->where('direction', 'down') as $clue)
                                        <li>
                                            <button class="text-left w-full hover:bg-gray-100 p-1 rounded"
                                                    data-clue="{{ $clue->id }}"
                                                    data-number="{{ $clue->number }}"
                                                    data-direction="down"
                                                    data-x="{{ $clue->start_position_x }}"
                                                    data-y="{{ $clue->start_position_y }}">
                                                <span class="font-medium">{{ $clue->number }}.</span>
                                                {{ $clue->question }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <!-- Timer and Controls -->
                            <div class="mt-6 pt-6 border-t">
                                <div class="text-center mb-4">
                                    <div id="timer" class="text-2xl font-bold">00:00</div>
                                </div>
                                <button id="submit-puzzle"
                                        class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Submit Puzzle
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const grid = document.getElementById('crossword-grid');
            const submitButton = document.getElementById('submit-puzzle');
            const timerElement = document.getElementById('timer');
            let startTime = Date.now();
            let selectedClue = null;
            let answers = {};

            // Timer function
            function updateTimer() {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const seconds = (elapsed % 60).toString().padStart(2, '0');
                timerElement.textContent = `${minutes}:${seconds}`;
            }

            setInterval(updateTimer, 1000);

            // Handle clue selection
            document.querySelectorAll('[data-clue]').forEach(clueElement => {
                clueElement.addEventListener('click', () => {
                    const clueId = clueElement.dataset.clue;
                    const direction = clueElement.dataset.direction;
                    const x = parseInt(clueElement.dataset.x);
                    const y = parseInt(clueElement.dataset.y);

                    // Highlight selected clue
                    document.querySelectorAll('[data-clue]').forEach(el => {
                        el.classList.remove('bg-blue-100');
                    });
                    clueElement.classList.add('bg-blue-100');

                    // Focus on first cell of the clue
                    const firstCell = grid.querySelector(`[data-cell="${x}-${y}"]`);
                    if (firstCell) {
                        firstCell.focus();
                    }

                    selectedClue = {
                        id: clueId,
                        direction: direction,
                        x: x,
                        y: y
                    };
                });
            });

            // Handle cell input
            grid.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', (e) => {
                    if (e.target.value) {
                        e.target.value = e.target.value.toUpperCase();
                        const [x, y] = e.target.dataset.cell.split('-').map(Number);
                        
                        // Move to next cell based on direction
                        if (selectedClue) {
                            const nextCell = selectedClue.direction === 'across'
                                ? grid.querySelector(`[data-cell="${x + 1}-${y}"]`)
                                : grid.querySelector(`[data-cell="${x}-${y + 1}"]`);
                            if (nextCell) {
                                nextCell.focus();
                            }
                        }
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value) {
                        const [x, y] = e.target.dataset.cell.split('-').map(Number);
                        
                        // Move to previous cell based on direction
                        if (selectedClue) {
                            const prevCell = selectedClue.direction === 'across'
                                ? grid.querySelector(`[data-cell="${x - 1}-${y}"]`)
                                : grid.querySelector(`[data-cell="${x}-${y - 1}"]`);
                            if (prevCell) {
                                prevCell.focus();
                            }
                        }
                    }
                });
            });

            // Handle puzzle submission
            submitButton.addEventListener('click', async () => {
                const completionTime = Math.floor((Date.now() - startTime) / 1000);
                
                // Collect answers
                document.querySelectorAll('[data-clue]').forEach(clueElement => {
                    const clueId = clueElement.dataset.clue;
                    const x = parseInt(clueElement.dataset.x);
                    const y = parseInt(clueElement.dataset.y);
                    const direction = clueElement.dataset.direction;
                    
                    let answer = '';
                    let currentX = x;
                    let currentY = y;
                    
                    while (true) {
                        const cell = grid.querySelector(`[data-cell="${currentX}-${currentY}"]`);
                        if (!cell) break;
                        
                        const input = cell.querySelector('input');
                        if (!input || !input.value) break;
                        
                        answer += input.value;
                        
                        if (direction === 'across') {
                            currentX++;
                        } else {
                            currentY++;
                        }
                    }
                    
                    if (answer) {
                        answers[clueId] = answer;
                    }
                });

                try {
                    const response = await fetch(`/puzzles/{{ $puzzle->id }}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            completion_time: completionTime,
                            answers: answers
                        })
                    });

                    const result = await response.json();
                    
                    if (result.success) {
                        alert(`Congratulations! Your score: ${result.score}%`);
                        window.location.href = '/leaderboard/{{ $puzzle->id }}';
                    } else {
                        alert(result.message);
                    }
                } catch (error) {
                    console.error('Error submitting puzzle:', error);
                    alert('Failed to submit puzzle. Please try again.');
                }
            });

            // Initialize clue numbers on the grid
            document.querySelectorAll('[data-clue]').forEach(clueElement => {
                const number = clueElement.dataset.number;
                const x = clueElement.dataset.x;
                const y = clueElement.dataset.y;
                const cell = grid.querySelector(`[data-cell="${x}-${y}"]`);
                if (cell) {
                    cell.querySelector('span').textContent = number;
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
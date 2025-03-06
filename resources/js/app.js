import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Initialize crossword grid when on the game page
document.addEventListener('DOMContentLoaded', function() {
    const gridElement = document.getElementById('crossword-grid');
    if (!gridElement) return;

    const gridSize = parseInt(gridElement.dataset.size);
    const cluesData = JSON.parse(gridElement.dataset.clues);
    const gameState = JSON.parse(gridElement.dataset.gameState || '{}');
    
    let selectedCell = null;
    let direction = 'across';
    let startTime = gameState.start_time ? gameState.start_time * 1000 : Date.now(); // Convert to milliseconds
    
    // Initialize timer
    const timerElement = document.getElementById('timer');
    let remainingTime = null;
    let timerInterval = null;

    if (timerElement) {
        remainingTime = parseInt(timerElement.dataset.remainingTime);
        updateTimer();
        startTimer();
    }

    // Restore saved letters
    if (gameState.letters) {
        Object.entries(gameState.letters).forEach(([cell, letter]) => {
            const [x, y] = cell.split('-').map(Number);
            const input = document.querySelector(`[data-cell="${x}-${y}"] input`);
            if (input) {
                input.value = letter;
            }
        });
    }

    function updateTimer() {
        if (!remainingTime) return;

        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        if (remainingTime <= 0) {
            clearInterval(timerInterval);
            alert('Time is up!');
            document.querySelectorAll('.crossword-cell input').forEach(input => {
                input.disabled = true;
            });
            document.getElementById('submit-puzzle').disabled = true;
        }
    }

    function startTimer() {
        if (!remainingTime) return;

        timerInterval = setInterval(() => {
            remainingTime--;
            updateTimer();
        }, 1000);
    }

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
        });
    }

    // Handle cell selection
    gridElement.addEventListener('click', function(e) {
        const cell = e.target.closest('.crossword-cell');
        if (!cell) return;

        const [x, y] = cell.dataset.cell.split('-').map(Number);
        selectCell(x, y);
    });

    // Handle keyboard input
    document.addEventListener('keydown', function(e) {
        if (!selectedCell) return;

        const { x, y } = selectedCell;
        const input = document.querySelector(`[data-cell="${x}-${y}"] input`);

        if (e.key === 'Backspace') {
            input.value = '';
            moveToPreviousCell();
            saveGameState();
        }
        else if (e.key === 'ArrowRight') {
            direction = 'across';
            moveToNextCell();
        }
        else if (e.key === 'ArrowLeft') {
            direction = 'across';
            moveToPreviousCell();
        }
        else if (e.key === 'ArrowDown') {
            direction = 'down';
            moveToNextCell();
        }
        else if (e.key === 'ArrowUp') {
            direction = 'down';
            moveToPreviousCell();
        }
        else if (e.key.length === 1 && e.key.match(/[a-zA-Z]/)) {
            input.value = e.key.toUpperCase();
            moveToNextCell();
            saveGameState();
        }
    });

    // Handle input events
    gridElement.addEventListener('input', function(e) {
        if (e.target.tagName !== 'INPUT') return;
        
        const cell = e.target.closest('.crossword-cell');
        if (!cell) return;

        e.target.value = e.target.value.toUpperCase();
        if (e.target.value) {
            moveToNextCell();
        }
        saveGameState();
    });

    // Handle puzzle submission
    const submitButton = document.getElementById('submit-puzzle');
    if (submitButton) {
        submitButton.addEventListener('click', async function() {
            const answers = {};
            document.querySelectorAll('.crossword-cell input').forEach(input => {
                const cell = input.parentElement.dataset.cell;
                answers[cell] = input.value || '';
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
                    window.location.href = result.redirect;
                } else {
                    toastr.error(result.message);
                }
            } catch (error) {
                console.error('Error submitting puzzle:', error);
                alert('Failed to submit puzzle. Please try again.');
            }
        });
    }

    // Helper functions for cell navigation and selection
    function selectCell(x, y) {
        selectedCell = { x, y };
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('ring-2', 'ring-blue-500');
        });
        const cell = document.querySelector(`[data-cell="${x}-${y}"]`);
        if (cell) {
            cell.classList.add('ring-2', 'ring-blue-500');
            const input = cell.querySelector('input');
            if (input) {
                input.focus();
            }
        }
        highlightCurrentWord();
    }

    function moveToNextCell() {
        if (!selectedCell) return;
        
        const { x, y } = selectedCell;
        if (direction === 'across' && x < gridSize - 1) {
            selectCell(x + 1, y);
        } else if (direction === 'down' && y < gridSize - 1) {
            selectCell(x, y + 1);
        }
    }

    function moveToPreviousCell() {
        if (!selectedCell) return;
        
        const { x, y } = selectedCell;
        if (direction === 'across' && x > 0) {
            selectCell(x - 1, y);
        } else if (direction === 'down' && y > 0) {
            selectCell(x, y - 1);
        }
    }

    function highlightCurrentWord() {
        if (!selectedCell) return;

        // Remove previous highlighting
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('bg-blue-100');
        });

        const { x, y } = selectedCell;
        const currentClue = findClueForCell(x, y, direction);

        if (currentClue) {
            // Highlight cells for current word
            if (direction === 'across') {
                for (let i = 0; i < currentClue.answer.length; i++) {
                    const cell = document.querySelector(`[data-cell="${currentClue.start_position_x + i}-${currentClue.start_position_y}"]`);
                    if (cell) {
                        cell.classList.add('bg-blue-100');
                    }
                }
            } else {
                for (let i = 0; i < currentClue.answer.length; i++) {
                    const cell = document.querySelector(`[data-cell="${currentClue.start_position_x}-${currentClue.start_position_y + i}"]`);
                    if (cell) {
                        cell.classList.add('bg-blue-100');
                    }
                }
            }

            // Highlight clue in the list
            document.querySelectorAll('[data-clue]').forEach(clueElement => {
                clueElement.classList.remove('bg-blue-100');
                if (clueElement.dataset.clue === currentClue.id.toString()) {
                    clueElement.classList.add('bg-blue-100');
                }
            });
        }
    }

    function findClueForCell(x, y, direction) {
        return cluesData.find(clue => {
            if (clue.direction !== direction) return false;

            if (direction === 'across') {
                return y === clue.start_position_y &&
                       x >= clue.start_position_x &&
                       x < clue.start_position_x + clue.answer.length;
            } else {
                return x === clue.start_position_x &&
                       y >= clue.start_position_y &&
                       y < clue.start_position_y + clue.answer.length;
            }
        });
    }

    // Select first cell on load
    selectCell(0, 0);
});

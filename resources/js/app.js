import './bootstrap';
import CrosswordGrid from './crossword';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Initialize crossword grid when on the game page
document.addEventListener('DOMContentLoaded', function() {
    const gridElement = document.getElementById('crossword-grid');
    if (!gridElement) return;

    const gridSize = parseInt(gridElement.dataset.size || 15);
    const cluesData = JSON.parse(gridElement.dataset.clues || '[]');
    
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
            if (!clue) return;

            crossword.direction = clue.direction;
            crossword.selectCell(clue.start_position_x, clue.start_position_y);
        });
    });

    // Handle keyboard input
    document.addEventListener('keydown', function(e) {
        if (!crossword.selectedCell) return;

        const { x, y } = crossword.selectedCell;
        const cell = crossword.grid[y][x];

        if (e.key === 'Backspace') {
            cell.letter = '';
            const input = document.querySelector(`[data-cell="${x}-${y}"] input`);
            if (input) {
                input.value = '';
            }
            crossword.moveToPreviousCell();
        }
        else if (e.key === 'ArrowRight') {
            crossword.direction = 'across';
            crossword.moveToNextCell();
        }
        else if (e.key === 'ArrowLeft') {
            crossword.direction = 'across';
            crossword.moveToPreviousCell();
        }
        else if (e.key === 'ArrowDown') {
            crossword.direction = 'down';
            crossword.moveToNextCell();
        }
        else if (e.key === 'ArrowUp') {
            crossword.direction = 'down';
            crossword.moveToPreviousCell();
        }
        else if (e.key.length === 1 && e.key.match(/[a-zA-Z]/)) {
            cell.letter = e.key.toUpperCase();
            const input = document.querySelector(`[data-cell="${x}-${y}"] input`);
            if (input) {
                input.value = cell.letter;
            }
            crossword.moveToNextCell();
        }
    });

    // Handle puzzle submission
    const submitButton = document.getElementById('submit-puzzle');
    if (submitButton) {
        submitButton.addEventListener('click', async function() {
            const answers = crossword.getCurrentAnswers();
            const completionTime = parseInt((Date.now() - startTime) / 1000);

            try {
                const response = await fetch(`/puzzles/${gridElement.dataset.puzzleId}/submit`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        answers,
                        completion_time: completionTime
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    alert(`Congratulations! Your score: ${result.score}%`);
                    window.location.href = `/leaderboard/${gridElement.dataset.puzzleId}`;
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error submitting puzzle:', error);
                alert('Failed to submit puzzle. Please try again.');
            }
        });
    }
});

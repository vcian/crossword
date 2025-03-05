class CrosswordGrid {
    constructor(gridSize, clues) {
        this.gridSize = gridSize;
        this.clues = clues;
        this.grid = Array(gridSize).fill().map(() => Array(gridSize).fill(null));
        this.selectedCell = null;
        this.selectedClue = null;
        this.direction = 'across';
    }

    initialize() {
        // Initialize grid with clues
        this.clues.forEach(clue => {
            const { start_position_x, start_position_y, answer, direction } = clue;
            const letters = answer.split('');
            
            letters.forEach((letter, index) => {
                const x = direction === 'across' ? start_position_x + index : start_position_x;
                const y = direction === 'down' ? start_position_y + index : start_position_y;
                
                if (x < this.gridSize && y < this.gridSize) {
                    if (!this.grid[y][x]) {
                        this.grid[y][x] = {
                            letter: '',
                            isActive: true,
                            number: index === 0 ? clue.number : null,
                            clues: []
                        };
                    }
                    this.grid[y][x].clues.push(clue);
                }
            });
        });

        // Mark inactive cells
        for (let y = 0; y < this.gridSize; y++) {
            for (let x = 0; x < this.gridSize; x++) {
                if (!this.grid[y][x]) {
                    this.grid[y][x] = {
                        letter: '',
                        isActive: false,
                        number: null,
                        clues: []
                    };
                }
            }
        }
    }

    selectCell(x, y) {
        if (!this.grid[y][x].isActive) return;

        this.selectedCell = { x, y };
        const cell = this.grid[y][x];

        // Find available clues for this cell
        const acrossClue = cell.clues.find(c => c.direction === 'across');
        const downClue = cell.clues.find(c => c.direction === 'down');

        // If cell has both directions available, toggle between them
        if (acrossClue && downClue) {
            this.direction = this.direction === 'across' ? 'down' : 'across';
        }
        // Otherwise use whatever direction is available
        else if (acrossClue) {
            this.direction = 'across';
        }
        else if (downClue) {
            this.direction = 'down';
        }

        this.selectedClue = this.direction === 'across' ? acrossClue : downClue;
        this.highlightCurrentWord();

        return this.selectedClue;
    }

    highlightCurrentWord() {
        if (!this.selectedClue) return;

        const { start_position_x, start_position_y, answer, direction } = this.selectedClue;
        const length = answer.length;

        // Remove all highlights
        document.querySelectorAll('.crossword-cell').forEach(cell => {
            cell.classList.remove('bg-blue-100', 'bg-yellow-100');
        });

        // Highlight current word
        for (let i = 0; i < length; i++) {
            const x = direction === 'across' ? start_position_x + i : start_position_x;
            const y = direction === 'down' ? start_position_y + i : start_position_y;
            
            if (x < this.gridSize && y < this.gridSize) {
                const cell = document.querySelector(`[data-cell="${x}-${y}"]`);
                if (cell) {
                    cell.classList.add('bg-blue-100');
                }
            }
        }

        // Highlight selected cell
        if (this.selectedCell) {
            const selectedElement = document.querySelector(
                `[data-cell="${this.selectedCell.x}-${this.selectedCell.y}"]`
            );
            if (selectedElement) {
                selectedElement.classList.add('bg-yellow-100');
            }
        }
    }

    moveToNextCell() {
        if (!this.selectedCell) return;

        const { x, y } = this.selectedCell;
        let nextX = x;
        let nextY = y;

        if (this.direction === 'across') {
            nextX = x + 1;
        } else {
            nextY = y + 1;
        }

        if (nextX < this.gridSize && nextY < this.gridSize && this.grid[nextY][nextX].isActive) {
            this.selectCell(nextX, nextY);
        }
    }

    moveToPreviousCell() {
        if (!this.selectedCell) return;

        const { x, y } = this.selectedCell;
        let prevX = x;
        let prevY = y;

        if (this.direction === 'across') {
            prevX = x - 1;
        } else {
            prevY = y - 1;
        }

        if (prevX >= 0 && prevY >= 0 && this.grid[prevY][prevX].isActive) {
            this.selectCell(prevX, prevY);
        }
    }

    validateAnswer(clueId, answer) {
        const clue = this.clues.find(c => c.id === clueId);
        return clue ? clue.answer.toLowerCase() === answer.toLowerCase() : false;
    }

    getCurrentAnswers() {
        const answers = {};
        
        this.clues.forEach(clue => {
            const { start_position_x, start_position_y, direction, id } = clue;
            let answer = '';
            
            for (let i = 0; i < clue.answer.length; i++) {
                const x = direction === 'across' ? start_position_x + i : start_position_x;
                const y = direction === 'down' ? start_position_y + i : start_position_y;
                
                if (x < this.gridSize && y < this.gridSize) {
                    const cell = this.grid[y][x];
                    answer += cell.letter || ' ';
                }
            }
            
            answers[id] = answer.trim();
        });
        
        return answers;
    }
}

export default CrosswordGrid; 
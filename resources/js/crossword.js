class CrosswordGrid {
    constructor(gridSize, clues) {
        this.gridSize = gridSize;
        this.clues = clues;
        this.grid = Array(gridSize).fill().map(() => 
            Array(gridSize).fill().map(() => ({ letter: '', isActive: false }))
        );
        this.selectedCell = null;
        this.direction = 'across';
        this.currentClue = null;
    }

    initialize() {
        // Mark active cells and set clue numbers
        this.clues.forEach(clue => {
            const { start_position_x: x, start_position_y: y, direction, answer } = clue;
            const length = answer.length;

            // Mark cells for this word
            for (let i = 0; i < length; i++) {
                const cellX = direction === 'across' ? x + i : x;
                const cellY = direction === 'down' ? y + i : y;
                
                if (cellX < this.gridSize && cellY < this.gridSize) {
                    this.grid[cellY][cellX].isActive = true;
                }
            }
        });
    }

    selectCell(x, y) {
        if (!this.grid[y][x].isActive) return null;

        this.selectedCell = { x, y };
        
        // Find the current clue based on selected cell and direction
        let foundClue = this.findClueForCell(x, y, this.direction);
        
        // If no clue found in current direction, try the other direction
        if (!foundClue) {
            this.direction = this.direction === 'across' ? 'down' : 'across';
            foundClue = this.findClueForCell(x, y, this.direction);
        }

        if (foundClue) {
            this.currentClue = foundClue;
            this.highlightCurrentWord();
        }

        return foundClue;
    }

    findClueForCell(x, y, direction) {
        return this.clues.find(clue => {
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

    highlightCurrentWord() {
        if (!this.currentClue) return;

        const { start_position_x, start_position_y, answer, direction } = this.currentClue;
        const length = answer.length;

        // Clear previous highlighting
        for (let y = 0; y < this.gridSize; y++) {
            for (let x = 0; x < this.gridSize; x++) {
                const cell = document.querySelector(`[data-cell="${x}-${y}"]`);
                if (cell) {
                    cell.classList.remove('bg-blue-100');
                }
            }
        }

        // Highlight current word
        for (let i = 0; i < length; i++) {
            const x = direction === 'across' ? start_position_x + i : start_position_x;
            const y = direction === 'down' ? start_position_y + i : start_position_y;
            const cell = document.querySelector(`[data-cell="${x}-${y}"]`);
            if (cell) {
                cell.classList.add('bg-blue-100');
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

        if (nextX < this.gridSize && nextY < this.gridSize) {
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

        if (prevX >= 0 && prevY >= 0) {
            this.selectCell(prevX, prevY);
        }
    }

    validateAnswer(clueId, answer) {
        const clue = this.clues.find(c => c.id === clueId);
        return clue && clue.answer.toLowerCase() === answer.toLowerCase();
    }

    getCurrentAnswers() {
        const answers = {};
        this.clues.forEach(clue => {
            let answer = '';
            for (let i = 0; i < clue.answer.length; i++) {
                const x = clue.direction === 'across' ? clue.start_position_x + i : clue.start_position_x;
                const y = clue.direction === 'down' ? clue.start_position_y + i : clue.start_position_y;
                answer += this.grid[y][x].letter || '';
            }
            answers[clue.id] = answer;
        });
        return answers;
    }
}

export default CrosswordGrid; 
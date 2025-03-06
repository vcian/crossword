<?php

namespace App\Services;

class CrosswordGenerator
{
    private int $gridSize;
    private array $grid = [];
    private array $words = [];
    private array $placedWords = [];
    private int $clueNumber = 1;

    public function __construct(int $gridSize)
    {
        $this->gridSize = $gridSize;
        $this->initializeGrid();
    }

    private function initializeGrid(): void
    {
        for ($i = 0; $i < $this->gridSize; $i++) {
            for ($j = 0; $j < $this->gridSize; $j++) {
                $this->grid[$i][$j] = null;
            }
        }
    }

    public function addWord(string $question, string $answer): void
    {
        $this->words[] = [
            'question' => $question,
            'answer' => strtoupper($answer),
            'length' => strlen($answer)
        ];
    }

    public function generate(): array
    {
        // Sort words by length (longest first) to optimize placement
        usort($this->words, function($a, $b) {
            return $b['length'] - $a['length'];
        });

        foreach ($this->words as $word) {
            $this->tryPlaceWord($word);
        }

        return [
            'grid' => $this->grid,
            'clues' => $this->placedWords
        ];
    }

    private function tryPlaceWord(array $word): bool
    {
        // Try horizontal placement
        for ($y = 0; $y < $this->gridSize; $y++) {
            for ($x = 0; $x <= $this->gridSize - $word['length']; $x++) {
                if ($this->canPlaceWordHorizontally($word['answer'], $x, $y)) {
                    $this->placeWordHorizontally($word, $x, $y);
                    return true;
                }
            }
        }

        // Try vertical placement
        for ($x = 0; $x < $this->gridSize; $x++) {
            for ($y = 0; $y <= $this->gridSize - $word['length']; $y++) {
                if ($this->canPlaceWordVertically($word['answer'], $x, $y)) {
                    $this->placeWordVertically($word, $x, $y);
                    return true;
                }
            }
        }

        return false;
    }

    private function canPlaceWordHorizontally(string $word, int $x, int $y): bool
    {
        // Check if the word fits and doesn't overlap incorrectly
        $hasIntersection = false;
        
        // Check the cell before the word
        if ($x > 0 && $this->grid[$y][$x - 1] !== null) {
            return false;
        }
        
        // Check the cell after the word
        if ($x + strlen($word) < $this->gridSize && $this->grid[$y][$x + strlen($word)] !== null) {
            return false;
        }

        for ($i = 0; $i < strlen($word); $i++) {
            // Check if cell is empty or has matching letter
            if ($this->grid[$y][$x + $i] !== null && $this->grid[$y][$x + $i] !== $word[$i]) {
                return false;
            }
            
            // Check cells above and below
            if ($y > 0 && $this->grid[$y - 1][$x + $i] !== null && $this->grid[$y][$x + $i] === null) {
                return false;
            }
            if ($y < $this->gridSize - 1 && $this->grid[$y + 1][$x + $i] !== null && $this->grid[$y][$x + $i] === null) {
                return false;
            }

            // Track intersections
            if ($this->grid[$y][$x + $i] === $word[$i]) {
                $hasIntersection = true;
            }
        }

        // If this isn't the first word, require at least one intersection
        return empty($this->placedWords) || $hasIntersection;
    }

    private function canPlaceWordVertically(string $word, int $x, int $y): bool
    {
        // Check if the word fits and doesn't overlap incorrectly
        $hasIntersection = false;
        
        // Check the cell before the word
        if ($y > 0 && $this->grid[$y - 1][$x] !== null) {
            return false;
        }
        
        // Check the cell after the word
        if ($y + strlen($word) < $this->gridSize && $this->grid[$y + strlen($word)][$x] !== null) {
            return false;
        }

        for ($i = 0; $i < strlen($word); $i++) {
            // Check if cell is empty or has matching letter
            if ($this->grid[$y + $i][$x] !== null && $this->grid[$y + $i][$x] !== $word[$i]) {
                return false;
            }
            
            // Check cells to the left and right
            if ($x > 0 && $this->grid[$y + $i][$x - 1] !== null && $this->grid[$y + $i][$x] === null) {
                return false;
            }
            if ($x < $this->gridSize - 1 && $this->grid[$y + $i][$x + 1] !== null && $this->grid[$y + $i][$x] === null) {
                return false;
            }

            // Track intersections
            if ($this->grid[$y + $i][$x] === $word[$i]) {
                $hasIntersection = true;
            }
        }

        // If this isn't the first word, require at least one intersection
        return empty($this->placedWords) || $hasIntersection;
    }

    private function placeWordHorizontally(array $word, int $x, int $y): void
    {
        for ($i = 0; $i < strlen($word['answer']); $i++) {
            $this->grid[$y][$x + $i] = $word['answer'][$i];
        }

        $this->placedWords[] = [
            'question' => $word['question'],
            'answer' => $word['answer'],
            'direction' => 'across',
            'start_x' => $x,
            'start_y' => $y,
            'number' => $this->clueNumber++
        ];
    }

    private function placeWordVertically(array $word, int $x, int $y): void
    {
        for ($i = 0; $i < strlen($word['answer']); $i++) {
            $this->grid[$y + $i][$x] = $word['answer'][$i];
        }

        $this->placedWords[] = [
            'question' => $word['question'],
            'answer' => $word['answer'],
            'direction' => 'down',
            'start_x' => $x,
            'start_y' => $y,
            'number' => $this->clueNumber++
        ];
    }
} 
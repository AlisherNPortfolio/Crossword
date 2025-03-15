<?php

namespace App\Services;

class CrosswordGenerator
{
    private $grid = [];
    private $words = [];
    private $gridSize;
    private $minGridSize = 10;
    private $placeWords = [];
    private $wordIndex = 1;

    public function __construct(int $initialSize = 10) {
        $this->gridSize = max($initialSize, $this->minGridSize);
        $this->initializeGrid();
    }

    private function initializeGrid()
    {
        $this->grid = [];
        for ($i = 0; $i < $this->gridSize; $i++) {
            for ($j = 0; $j < $this->gridSize; $j++) {
                $this->grid[$i][$j] = [
                    'letter' => null,
                    'wordIndex' => null,
                    'isHorizontalStart' => false,
                    'isVerticalStart' => false,
                ];
            }
        }
    }

    public function addWord($word, $clue)
    {
        $word = mb_strtoupper(trim($word));
        if (empty($word)) {
            return false;
        }

        $this->words[] = [
            'word' => $word,
            'clue' => $clue,
            'placed' => false,
            'orientation' => null,
            'startRow' => null,
            'startCol' => null,
            'index' => $this->wordIndex
        ];

        $this->resetPlacement();
        $result = $this->placeAllWords();

        if (!$result) {
            $this->expandGrid();
            $result = $this->placeAllWords();
        }

        if ($result) {
            $this->wordIndex++;
            return true;
        }

        array_pop($this->words);
        $this->resetPlacement();
        $this->placeAllWords();
        return false;
    }

    private function resetPlacement()
    {
        $this->initializeGrid();
        $this->placeWords = [];

        foreach ($this->words as $key => $word) {
            $this->words[$key]['placed'] = false;
            $this->words[$key]['orientation'] = null;
            $this->words[$key]['startRow'] = null;
            $this->words[$key]['startCol'] = null;
        }
    }

    private function placeAllWords()
    {
        if (empty($this->placeWords)) {
            $wordData = $this->words[0];
            $word = $wordData['word'];
            $length = mb_strlen($word);

            $centerRow = floor($this->gridSize / 2);
            $centerCol = floor($this->gridSize / 2) - floor($length / 2);

            if ($centerCol < 0 || $centerCol + $length > $this->gridSize) {
                $centerCol = 0;
            }

            for ($i = 0; $i < $length; $i++) {
                $this->grid[$centerRow][$centerCol + $i]['letter'] = mb_substr($word, $i, 1);
            }

            $this->grid[$centerRow][$centerCol]['isHorizontalStart'] = true;
            $this->grid[$centerRow][$centerCol]['wordIndex'] = $wordData['index'];

            $this->words[0]['placed'] = true;
            $this->words[0]['orientation'] = 'horizontal';
            $this->words[0]['startRow'] = $centerRow;
            $this->words[0]['startCol'] = $centerCol;

            $this->placeWords[] = 0;
        }

        $allPlaced = true;

        foreach ($this->words as $index => $wordData) {
            if ($wordData['placed']) {
                continue;
            }

            $placed = $this->tryPlaceWord($index);
            if (!$placed) {
                $allPlaced = false;
            }
        }

        return $allPlaced;
    }

    private function tryPlaceWord($wordIndex)
    {
        $wordData = $this->words[$wordIndex];
        $word = $wordData['word'];

        foreach ($this->placeWords as $placedWordIndex) {
            $placedWordData = $this->words[$placedWordIndex];
            $placedWord = $placedWordData['word'];

            for ($i = 0; $i < mb_strlen($word); $i++) {
                $letterToIntersect = mb_substr($word, $i, 1);

                for ($j = 0; $j < mb_strlen($placedWord); $j++) {
                    $placedLetter = mb_substr($placedWord, $j, 1);

                    if ($letterToIntersect === $placedLetter) {
                        if ($placedWordData['orientation'] === 'horizontal') {
                            $startRow = $placedWordData['startRow'] - $i;
                            $startCol = $placedWordData['startCol'] + $j;

                            if ($this->canPlaceWordVertically($word, $startRow, $startCol)) {
                                $this->placeWordVertically($word, $startRow, $startCol, $wordData['index']);
                                $this->words[$wordIndex]['placed'] = true;
                                $this->words[$wordIndex]['orientation'] = 'vertical';
                                $this->words[$wordIndex]['startRow'] = $startRow;
                                $this->words[$wordIndex]['startCol'] = $startCol;
                                $this->placeWords[] = $wordIndex;
                                return true;
                            }
                        } else {
                            $startRow = $placedWordData['startRow'] + $j;
                            $startCol = $placedWordData['startCol'] - $i;

                            if ($this->canPlaceWordHorizontally($word, $startRow, $startCol)) {
                                $this->placeWordHorizontally($word, $startRow, $startCol, $wordData['index']);
                                $this->words[$wordIndex]['placed'] = true;
                                $this->words[$wordIndex]['orientation'] = 'horizontal';
                                $this->words[$wordIndex]['startRow'] = $startRow;
                                $this->words[$wordIndex]['startCol'] = $startCol;
                                $this->placeWords[] = $wordIndex;
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    private function canPlaceWordHorizontally($word, $row, $col) {
        $length = mb_strlen($word);
        if ($row < 0 || $row >= $this->gridSize || $col < 0 || $col + $length > $this->gridSize) {
            return false;
        }

        if ($col > 0 && !is_null($this->grid[$row][$col - 1]['letter'])) {
            return false;
        }

        if ($col + $length < $this->gridSize && !is_null($this->grid[$row][$col + $length]['letter'])) {
            return false;
        }

        for ($i = 0; $i < $length; $i++) {
            $currentCol = $col + $i;
            $letter = mb_substr($word, $i, 1);

            if (!is_null($this->grid[$row][$currentCol]['letter']) && $this->grid[$row][$currentCol]['letter'] !== $letter) {
                return false;
            }

            if (($row > 0 && !is_null($this->grid[$row - 1][$currentCol]['letter']))) {
                $hasVerticalWord = false;

                if (!is_null($this->grid[$row][$currentCol]['letter'])) {
                    if (($row > 0 && !is_null($this->grid[$row - 1][$currentCol]['letter'])) ||
                    ($row < $this->gridSize - 1 && !is_null($this->grid[$row+1][$currentCol]['letter']))) {
                        $hasVerticalWord = true;
                    }
                }

                if (!$hasVerticalWord) {
                    return false;
                }
            }
        }

        return true;
    }

    private function canPlaceWordVertically($word, $row, $col)
    {
        $length = mb_strlen($word);

        if ($col < 0 || $col >= $this->gridSize || $row < 0 || $row + $length > $this->gridSize) {
            return false;
        }

        if ($row > 0 && !is_null($this->grid[$row - 1][$col]['letter'])) {
            return false;
        }

        if ($row + $length < $this->gridSize && !is_null($this->grid[$row + $length][$col]['letter'])) {
            return false;
        }

        for ($i = 0; $i < $length; $i++) {
            $currentRow = $row + $i;
            $letter = mb_substr($word, $i, 1);

            if (!is_null($this->grid[$currentRow][$col]['letter']) && $this->grid[$currentRow][$col]['letter'] !== $letter) {
                return false;
            }

            if (($col > 0 && !is_null($this->grid[$currentRow][$col - 1]['letter'])) ||
            ($col < $this->gridSize - 1 && !is_null($this->grid[$currentRow][$col + 1]['letter']))) {
                $hasHorizontalWord = false;

                if (!is_null($this->grid[$currentRow][$col]['letter'])) {
                    if (($col > 0 && !is_null($this->grid[$currentRow][$col - 1]['letter'])) ||
                    ($col < $this->gridSize - 1 && !is_null($this->grid[$currentRow][$col + 1]['letter']))) {
                        $hasHorizontalWord = true;
                    }
                }

                if (!$hasHorizontalWord) {
                    return false;
                }
            }
        }

        return true;
    }

    private function placeWordHorizontally($word, $row, $col, $wordIndex)
    {
        $length = mb_strlen($word);

        for ($i = 0; $i < $length; $i++) {
            $this->grid[$row][$col + $i]['letter'] = mb_substr($word, $i, 1);
        }

        $this->grid[$row][$col]['word_index'] = true;
        $this->grid[$row][$col]['wordIndex'] = $wordIndex;
    }

    private function placeWordVertically($word, $row, $col, $wordIndex)
    {
        $length = mb_strlen($word);

        for ($i = 0; $i < $length; $i++) {
            $this->grid[$row + $i][$col]['letter'] = mb_substr($word, $i, 1);
        }

        $this->grid[$row][$col]['isVerticalStart'] = true;
        $this->grid[$row][$col]['wordIndex'] = $wordIndex;
    }

    private function expandGrid() {
        $newSize = $this->gridSize * 2;
        $oldSize = $this->gridSize;
        $offsetRow = floor(($newSize - $oldSize) / 2);
        $offsetCol = floor(($newSize - $oldSize) / 2);

        $newGrid = [];
        for ($i = 0; $i < $newSize; $i++) {
            for ($j = 0; $j < $newSize; $j++) {
                $newGrid[$i][$j] = [
                    'letter' => null,
                    'wordIndex' => null,
                    'isHorizontalStart' => false,
                    'isVerticalStart' => false,
                ];
            }
        }

        for ($i = 0; $i < $oldSize; $i++) {
            for ($j = 0; $j < $oldSize; $j++) {
                $newGrid[$i + $offsetRow][$j + $offsetCol] = $this->grid[$i][$j];
            }
        }

        foreach ($this->words as $key => $word) {
            if ($word['placed']) {
                $this->words[$key]['startRow'] += $offsetRow;
                $this->words[$key]['startCol'] += $offsetCol;
            }
        }

        $this->grid = $newGrid;
        $this->gridSize = $newSize;
    }

    public function optimizeGrid()
    {
        $minRow = $this->gridSize;
        $maxRow = 0;
        $minCol = $this->gridSize;
        $maxCol = 0;

        for ($i = 0; $i < $this->gridSize; $i++) {
            for ($j = 0; $j < $this->gridSize; $j++) {
                if (!is_null($this->grid[$i][$j]['letter'])) {
                    $minRow = min($minRow, $i);
                    $maxRow = max($maxRow, $i);
                    $minCol = min($minCol, $j);
                    $maxCol = max($maxCol, $j);
                }
            }
        }

        $padding = 1;
        $minRow = max(0, $minRow - $padding);
        $minCol = max(0, $minCol - $padding);
        $maxRow = min($this->gridSize - 1, $maxRow + $padding);
        $maxCol = min($this->gridSize - 1, $maxCol + $padding);

        $newGridSize = max($maxRow - $minRow + 1, $maxCol - $minCol + 1);

        if ($newGridSize > $this->gridSize) {
            $newGridSize = $this->gridSize;
            $minRow = 0;
            $minCol = 0;
        }

        $newGrid = [];
        for ($i = 0; $i < $newGridSize; $i++) {
            for ($j = 0; $j < $newGridSize; $j++) {
                $sourceRow = $i + $minRow;
                $sourceCol = $j + $minCol;

                if ($sourceRow < $this->gridSize && $sourceCol < $this->gridSize) {
                    $newGrid[$i][$j] = $this->grid[$sourceRow][$sourceCol];
                } else {
                    $newGrid[$i][$j] = [
                        'letter' => null,
                        'wordIndex' => null,
                        'isHorizontalStart' => false,
                        'isVerticalStart' => false,
                    ];
                }
            }
        }

        foreach ($this->words as $key => $word) {
            if ($word['placed']) {
                $this->words[$key]['startRow'] -= $minRow;
                $this->words[$key]['startCol'] -= $minCol;
            }
        }

        $this->grid = $newGrid;
        $this->gridSize = $newGridSize;
    }

    public function getGrid() {
        return $this->grid;
    }

    public function getWords() {
        return $this->words;
    }

    public function getGridSize() {
        return $this->gridSize;
    }

    public function toJson() {
        $result = [
            'grid' => $this->grid,
            'words' => $this->words,
            'gridSize' => $this->gridSize,
        ];

        return json_encode($result);
    }
}

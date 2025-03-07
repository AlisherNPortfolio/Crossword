import { defineStore } from 'pinia';
import axios from 'axios';

export const useCrosswordStore = defineStore('crossword', {
  state: () => ({
    crosswordId: null,
    crosswordData: null,
    gridData: [],
    words: [],
    userSolution: {},
    loading: false,
    activeCell: { row: 0, col: 0 },
    activeDirection: 'horizontal',
    activeWordIndex: null,
    completed: false,
    startTime: null,
    timeTaken: 0,
    timer: null,
    saving: false,
    error: null,
    isCompetition: false,
    competitionId: null,
    competitionEndTime: null
  }),

  actions: {
    setCrosswordData(data, isCompetition = false, competitionId = null, endTime = null) {
      this.crosswordId = data.id;
      this.crosswordData = data;
      this.gridData = data.grid_data;
      this.words = data.words;
      this.isCompetition = isCompetition;
      this.competitionId = competitionId;
      this.competitionEndTime = endTime ? new Date(endTime) : null;

      // Initialize user solution grid
      this.initializeUserSolution();
    },

    initializeUserSolution(savedSolution = null) {
      this.userSolution = {};

      // Create empty solution grid
      for (let row = 0; row < this.gridData.length; row++) {
        if (!this.userSolution[row]) {
          this.userSolution[row] = {};
        }

        for (let col = 0; col < this.gridData[row].length; col++) {
          if (this.gridData[row][col].letter) {
            this.userSolution[row][col] = {
              letter: '',
              isCorrect: null
            };
          }
        }
      }

      // Load saved solution if available
      if (savedSolution) {
        for (const row in savedSolution) {
          for (const col in savedSolution[row]) {
            if (this.userSolution[row] && this.userSolution[row][col]) {
              this.userSolution[row][col] = savedSolution[row][col];
            }
          }
        }
      }
    },

    loadSavedSolution(solution) {
      if (solution && solution.solution_data) {
        this.initializeUserSolution(solution.solution_data);
        this.completed = solution.completed || false;
        this.timeTaken = solution.time_taken || 0;
      }
    },

    setActiveCell(row, col) {
      // Only allow selecting cells that have letters
      if (this.gridData[row] && this.gridData[row][col] && this.gridData[row][col].letter) {
        this.activeCell = { row, col };

        // Determine direction based on word starts
        const cell = this.gridData[row][col];
        if (cell.isHorizontalStart) {
          this.activeDirection = 'horizontal';
          this.activeWordIndex = cell.wordIndex;
        } else if (cell.isVerticalStart) {
          this.activeDirection = 'vertical';
          this.activeWordIndex = cell.wordIndex;
        } else {
          // Find which word this cell belongs to
          this.determineActiveWord();
        }
      }
    },

    determineActiveWord() {
      const { row, col } = this.activeCell;

      // Check if part of horizontal word
      let isHorizontal = false;
      let horizontalStart = col;

      // Look left
      for (let c = col; c >= 0; c--) {
        if (!this.gridData[row][c] || !this.gridData[row][c].letter) {
          break;
        }
        if (this.gridData[row][c].isHorizontalStart) {
          isHorizontal = true;
          horizontalStart = c;
          this.activeWordIndex = this.gridData[row][c].wordIndex;
          break;
        }
        horizontalStart = c;
      }

      // Check if part of vertical word
      let isVertical = false;
      let verticalStart = row;

      // Look up
      for (let r = row; r >= 0; r--) {
        if (!this.gridData[r] || !this.gridData[r][col] || !this.gridData[r][col].letter) {
          break;
        }
        if (this.gridData[r][col].isVerticalStart) {
          isVertical = true;
          verticalStart = r;
          if (!isHorizontal || this.activeDirection === 'vertical') {
            this.activeWordIndex = this.gridData[r][col].wordIndex;
          }
          break;
        }
        verticalStart = r;
      }

      // If both, prefer current direction, or horizontal by default
      if (isHorizontal && (this.activeDirection === 'horizontal' || !isVertical)) {
        this.activeDirection = 'horizontal';
      } else if (isVertical) {
        this.activeDirection = 'vertical';
      }
    },

    toggleDirection() {
      const { row, col } = this.activeCell;
      const cell = this.gridData[row][col];

      // Toggle direction only if the cell is part of both a horizontal and vertical word
      if (this.isPartOfHorizontalWord(row, col) && this.isPartOfVerticalWord(row, col)) {
        this.activeDirection = this.activeDirection === 'horizontal' ? 'vertical' : 'horizontal';
        this.determineActiveWord();
      }
    },

    isPartOfHorizontalWord(row, col) {
      // Check left
      if (col > 0 && this.gridData[row][col-1] && this.gridData[row][col-1].letter) {
        return true;
      }

      // Check right
      if (col < this.gridData[row].length - 1 &&
          this.gridData[row][col+1] &&
          this.gridData[row][col+1].letter) {
        return true;
      }

      return this.gridData[row][col].isHorizontalStart;
    },

    isPartOfVerticalWord(row, col) {
      // Check up
      if (row > 0 && this.gridData[row-1] &&
          this.gridData[row-1][col] &&
          this.gridData[row-1][col].letter) {
        return true;
      }

      // Check down
      if (row < this.gridData.length - 1 &&
          this.gridData[row+1] &&
          this.gridData[row+1][col] &&
          this.gridData[row+1][col].letter) {
        return true;
      }

      return this.gridData[row][col].isVerticalStart;
    },

    inputLetter(letter) {
      const { row, col } = this.activeCell;

      // Update the letter in user solution
      if (this.userSolution[row] && this.userSolution[row][col]) {
        this.userSolution[row][col].letter = letter.toUpperCase();

        // Start timer on first input if not already started
        if (!this.startTime) {
          this.startTimer();
        }

        // Move to next cell
        this.moveToNextCell();
      }
    },

    moveToNextCell() {
      const { row, col } = this.activeCell;

      if (this.activeDirection === 'horizontal') {
        // Find the next cell in the horizontal word
        let nextCol = col + 1;
        while (nextCol < this.gridData[row].length) {
          if (this.gridData[row][nextCol] && this.gridData[row][nextCol].letter) {
            this.setActiveCell(row, nextCol);
            return;
          }
          nextCol++;
        }
      } else {
        // Find the next cell in the vertical word
        let nextRow = row + 1;
        while (nextRow < this.gridData.length) {
          if (this.gridData[nextRow] && this.gridData[nextRow][col] && this.gridData[nextRow][col].letter) {
            this.setActiveCell(nextRow, col);
            return;
          }
          nextRow++;
        }
      }
    },

    moveToPrevCell() {
      const { row, col } = this.activeCell;

      if (this.activeDirection === 'horizontal') {
        // Find the previous cell in the horizontal word
        let prevCol = col - 1;
        while (prevCol >= 0) {
          if (this.gridData[row][prevCol] && this.gridData[row][prevCol].letter) {
            this.setActiveCell(row, prevCol);
            return;
          }
          prevCol--;
        }
      } else {
        // Find the previous cell in the vertical word
        let prevRow = row - 1;
        while (prevRow >= 0) {
          if (this.gridData[prevRow] && this.gridData[prevRow][col] && this.gridData[prevRow][col].letter) {
            this.setActiveCell(prevRow, col);
            return;
          }
          prevRow--;
        }
      }
    },

    startTimer() {
      this.startTime = new Date();
      this.timer = setInterval(() => {
        const now = new Date();
        this.timeTaken = Math.floor((now - this.startTime) / 1000);

        // Check if competition has ended
        if (this.isCompetition && this.competitionEndTime && now > this.competitionEndTime) {
          this.stopTimer();
          // Force save solution when competition ends
          this.saveSolution(true);
        }
      }, 1000);
    },

    stopTimer() {
      if (this.timer) {
        clearInterval(this.timer);
        this.timer = null;
      }
    },

    checkSolution() {
      let allCorrect = true;

      for (let row = 0; row < this.gridData.length; row++) {
        for (let col = 0; col < this.gridData[row].length; col++) {
          if (this.gridData[row][col].letter) {
            const userLetter = this.userSolution[row][col].letter;
            const correctLetter = this.gridData[row][col].letter;

            const isCorrect = userLetter.toUpperCase() === correctLetter.toUpperCase();
            this.userSolution[row][col].isCorrect = isCorrect;

            if (!isCorrect || !userLetter) {
              allCorrect = false;
            }
          }
        }
      }

      this.completed = allCorrect;

      if (allCorrect) {
        this.stopTimer();
      }

      return allCorrect;
    },

    async saveSolution(forceCompleted = false) {
      this.saving = true;
      this.error = null;

      try {
        // Check if solution is complete
        const isComplete = forceCompleted || this.checkSolution();

        let url, data;

        if (this.isCompetition) {
          url = `/api/competitions/${this.competitionId}/save-solution`;
          data = {
            solution_data: this.userSolution,
            completed: isComplete,
            time_taken: this.timeTaken
          };
        } else {
          url = `/api/crosswords/${this.crosswordId}/save-solution`;
          data = {
            solution_data: this.userSolution,
            completed: isComplete,
            time_taken: this.timeTaken
          };
        }

        const response = await axios.post(url, data);

        if (isComplete) {
          this.stopTimer();
        }

        return response.data;
      } catch (error) {
        this.error = 'Failed to save your solution. Please try again.';
        console.error('Save solution error:', error);
      } finally {
        this.saving = false;
      }
    },

    resetGame() {
      this.initializeUserSolution();
      this.completed = false;
      this.stopTimer();
      this.startTime = null;
      this.timeTaken = 0;
      this.activeCell = { row: 0, col: 0 };
      this.activeDirection = 'horizontal';
      this.error = null;
    }
  }
});

export const useCrosswordCreatorStore = defineStore('crosswordCreator', {
  state: () => ({
    title: '',
    words: [],
    currentWord: '',
    currentClue: '',
    crosswordGenerator: null,
    gridData: [],
    loading: false,
    error: null,
    publishing: false
  }),

  actions: {
    addWord() {
      if (this.currentWord.trim() && this.currentClue.trim()) {
        this.words.push({
          word: this.currentWord.trim(),
          clue: this.currentClue.trim()
        });

        this.currentWord = '';
        this.currentClue = '';
      }
    },

    removeWord(index) {
      this.words.splice(index, 1);
    },

    async generateCrossword() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/crosswords/generate-preview', {
          words: this.words
        });

        this.gridData = response.data.grid;
      } catch (error) {
        this.error = 'Failed to generate crossword preview. Please try again.';
        console.error('Generate crossword error:', error);
      } finally {
        this.loading = false;
      }
    },

    async saveCrossword(shouldPublish = false) {
      this.publishing = true;
      this.error = null;

      try {
        const response = await axios.post('/api/crosswords', {
          title: this.title,
          words: this.words,
          publish: shouldPublish
        });

        return response.data;
      } catch (error) {
        this.error = 'Failed to save crossword. Please try again.';
        console.error('Save crossword error:', error);
        return null;
      } finally {
        this.publishing = false;
      }
    },

    resetForm() {
      this.title = '';
      this.words = [];
      this.currentWord = '';
      this.currentClue = '';
      this.gridData = [];
      this.error = null;
    }
  }
});

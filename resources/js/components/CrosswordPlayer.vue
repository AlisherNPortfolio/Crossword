<!-- resources/js/components/CrosswordPlayer.vue -->
<template>
    <div class="crossword-player">
      <div v-if="loading" class="text-center my-5">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading crossword...</p>
      </div>

      <div v-else class="row">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="mb-0">{{ crosswordData?.title }}</h3>
              <div class="game-timer">
                Time: {{ formatTime(timeTaken) }}
              </div>
            </div>

            <div class="card-body">
              <div
                class="crossword-grid-container"
                :class="{ 'disabled': completed || (isCompetition && competitionEnded) }"
              >
                <div class="crossword-grid">
                  <div
                    v-for="(row, rowIndex) in gridData"
                    :key="'row-' + rowIndex"
                    class="crossword-row"
                  >
                    <div
                      v-for="(cell, colIndex) in row"
                      :key="'cell-' + rowIndex + '-' + colIndex"
                      class="crossword-cell"
                      :class="{
                        'has-letter': cell.letter !== null,
                        'active': isActiveCell(rowIndex, colIndex),
                        'same-word': isPartOfActiveWord(rowIndex, colIndex),
                        'correct': userSolution[rowIndex]?.[colIndex]?.isCorrect === true,
                        'incorrect': userSolution[rowIndex]?.[colIndex]?.isCorrect === false
                      }"
                      @click="setActiveCell(rowIndex, colIndex)"
                    >
                      <span v-if="cell.wordIndex !== null" class="word-index">{{ cell.wordIndex }}</span>
                      <input
                        v-if="cell.letter !== null"
                        type="text"
                        class="cell-input"
                        maxlength="1"
                        :value="getCellLetter(rowIndex, colIndex)"
                        @input="updateCellLetter($event, rowIndex, colIndex)"
                        :readonly="completed || (isCompetition && competitionEnded)"
                        @keydown="handleKeyDown($event, rowIndex, colIndex)"
                        @focus="setActiveCell(rowIndex, colIndex)"
                        :ref="el => { if (el) cellRefs[`${rowIndex}-${colIndex}`] = el }"
                        />
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-4 d-flex justify-content-between">
                <button class="btn btn-secondary" @click="resetGame">
                  Reset
                </button>
                <div>
                  <button
                    class="btn btn-info me-2"
                    @click="checkSolution"
                    :disabled="completed || (isCompetition && competitionEnded)"
                  >
                    Check Solution
                  </button>
                  <button
                    class="btn btn-primary"
                    @click="saveSolution"
                    :disabled="saving || (isCompetition && competitionEnded)"
                  >
                    {{ saving ? 'Saving...' : 'Save Progress' }}
                  </button>
                </div>
              </div>

              <div v-if="completed" class="alert alert-success mt-3">
                Congratulations! You've completed the crossword correctly!
              </div>

              <div v-if="error" class="alert alert-danger mt-3">
                {{ error }}
              </div>

              <div v-if="isCompetition && competitionEnded" class="alert alert-warning mt-3">
                This competition has ended. You can no longer modify your answers.
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card">
            <div class="card-header">
              <h4 class="mb-0">Clues</h4>
            </div>
            <div class="card-body">
              <div class="clues-container">
                <div class="horizontal-clues mb-4">
                  <h5>Across</h5>
                  <ul class="list-group">
                    <li
                      v-for="word in horizontalWords"
                      :key="'h-' + word.index"
                      class="list-group-item"
                      :class="{ 'active': activeWordIndex === word.index && activeDirection === 'horizontal' }"
                      @click="selectWord(word, 'horizontal')"
                    >
                      <strong>{{ word.index }}.</strong> {{ word.clue }}
                    </li>
                  </ul>
                </div>

                <div class="vertical-clues">
                  <h5>Down</h5>
                  <ul class="list-group">
                    <li
                      v-for="word in verticalWords"
                      :key="'v-' + word.index"
                      class="list-group-item"
                      :class="{ 'active': activeWordIndex === word.index && activeDirection === 'vertical' }"
                      @click="selectWord(word, 'vertical')"
                    >
                      <strong>{{ word.index }}.</strong> {{ word.clue }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
  import { useCrosswordStore } from '../store/crosswordStore';
  import { storeToRefs } from 'pinia';

  const props = defineProps({
    crosswordId: {
      type: [Number, String],
      required: true
    },
    savedSolution: {
      type: Object,
      default: () => null
    },
    isCompetition: {
      type: Boolean,
      default: false
    },
    competitionId: {
      type: [Number, String],
      default: null
    },
    competitionEndTime: {
      type: String,
      default: null
    }
  });

  const store = useCrosswordStore();
  const {
    crosswordData, gridData, words, userSolution, activeCell,
    activeDirection, activeWordIndex, completed, timeTaken,
    loading, saving, error
  } = storeToRefs(store);

  const cellRefs = ref({});
  const loadingData = ref(true);

  // Computed properties
  const horizontalWords = computed(() => {
    return words.value.filter(word => word.orientation === 'horizontal');
  });

  const verticalWords = computed(() => {
    return words.value.filter(word => word.orientation === 'vertical');
  });

  const competitionEnded = computed(() => {
    if (!props.isCompetition || !props.competitionEndTime) return false;
    return new Date() > new Date(props.competitionEndTime);
  });

  // Methods
  const getCellLetter = (row, col) => {
        if (userSolution.value[row] && userSolution.value[row][col]) {
            return userSolution.value[row][col].letter || '';
        }
        return '';
    };

    const updateCellLetter = (event, row, col) => {
        const letter = event.target.value;
        if (userSolution.value[row] && userSolution.value[row][col]) {
            userSolution.value[row][col].letter = letter;
            if (letter) {
            handleInput(event, row, col);
            }
        }
    };
  const loadCrossword = async () => {
    loadingData.value = true;

    try {
      const url = props.isCompetition
        ? `/api/competitions/${props.competitionId}/crossword`
        : `/api/crosswords/${props.crosswordId}`;

      const response = await fetch(url);
      const data = await response.json();

      store.setCrosswordData(
        data.crossword,
        props.isCompetition,
        props.competitionId,
        props.competitionEndTime
      );

      if (props.savedSolution) {
        store.loadSavedSolution(props.savedSolution);
      }

      // Focus first cell after loading
      setTimeout(() => {
        focusFirstCell();
      }, 100);
    } catch (error) {
      console.error('Error loading crossword:', error);
    } finally {
      loadingData.value = false;
    }
  };

  const focusFirstCell = () => {
    // Find the first cell with a letter
    for (let row = 0; row < gridData.value.length; row++) {
      for (let col = 0; col < gridData.value[row].length; col++) {
        if (gridData.value[row][col].letter) {
          setActiveCell(row, col);
          return;
        }
      }
    }
  };

  const isActiveCell = (row, col) => {
    return activeCell.value.row === row && activeCell.value.col === col;
  };

  const isPartOfActiveWord = (row, col) => {
    if (!activeWordIndex.value || !gridData.value[row]?.[col]?.letter) return false;

    const currentWord = words.value.find(w => w.index === activeWordIndex.value);
    if (!currentWord) return false;

    if (currentWord.orientation === 'horizontal' && row === currentWord.startRow) {
      return col >= currentWord.startCol && col < currentWord.startCol + currentWord.word.length;
    } else if (currentWord.orientation === 'vertical' && col === currentWord.startCol) {
      return row >= currentWord.startRow && row < currentWord.startRow + currentWord.word.length;
    }

    return false;
  };

  const setActiveCell = (row, col) => {
    if (completed.value || (props.isCompetition && competitionEnded.value)) return;

    store.setActiveCell(row, col);

    // Focus the input
    nextTick(() => {
      const ref = cellRefs.value[`${row}-${col}`];
      if (ref) {
        ref.focus();
      }
    });
  };

  const handleInput = (event, row, col) => {
    const letter = event.target.value;
    if (letter) {
      store.inputLetter(letter);
    }
  };

  const handleKeyDown = (event, row, col) => {
    switch (event.key) {
      case 'ArrowUp':
        moveFocus(row - 1, col);
        break;
      case 'ArrowDown':
        moveFocus(row + 1, col);
        break;
      case 'ArrowLeft':
        moveFocus(row, col - 1);
        break;
      case 'ArrowRight':
        moveFocus(row, col + 1);
        break;
      case 'Backspace':
        if (!userSolution.value[row][col].letter) {
          store.moveToPrevCell();
        }
        break;
      case ' ':
        store.toggleDirection();
        event.preventDefault();
        break;
    }
  };

  const moveFocus = (row, col) => {
    if (
      row >= 0 && row < gridData.value.length &&
      col >= 0 && col < gridData.value[row].length &&
      gridData.value[row][col].letter
    ) {
      setActiveCell(row, col);
    }
  };

  const selectWord = (word, direction) => {
    if (completed.value || (props.isCompetition && competitionEnded.value)) return;

    store.activeDirection = direction;
    store.activeWordIndex = word.index;
    setActiveCell(word.startRow, word.startCol);
  };

  const checkSolution = () => {
    const isComplete = store.checkSolution();

    if (isComplete) {
      saveSolution(true);
    }
  };

  const saveSolution = async (forceCompleted = false) => {
    await store.saveSolution(forceCompleted);

    if (completed.value) {
      // Wait a moment before showing success message
      setTimeout(() => {
        if (props.isCompetition) {
          window.location.href = `/competitions/${props.competitionId}`;
        }
      }, 3000);
    }
  };

  const resetGame = () => {
    if (!confirm('Are you sure you want to reset the crossword? All progress will be lost.')) return;

    store.resetGame();
    focusFirstCell();
  };

  const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
  };

  // Lifecycle hooks
  onMounted(() => {
    loadCrossword();

    // Auto-save every minute
    const autoSaveInterval = setInterval(() => {
      if (!completed.value && !competitionEnded.value) {
        saveSolution();
      }
    }, 60000);

    // Clean up interval on unmount
    onBeforeUnmount(() => {
      clearInterval(autoSaveInterval);
      store.stopTimer();
    });
  });
  </script>

  <style scoped>
  .crossword-grid-container {
    overflow-x: auto;
    padding: 15px;
  }

  .crossword-grid-container.disabled {
    opacity: 0.7;
  }

  .crossword-grid {
    display: inline-block;
    border: 2px solid #333;
    background-color: #000;
  }

  .crossword-row {
    display: flex;
  }

  .crossword-cell {
    width: 40px;
    height: 40px;
    position: relative;
    background-color: #000;
    border: 1px solid #555;
  }

  .crossword-cell.has-letter {
    background-color: #fff;
  }

  .crossword-cell.active {
    background-color: #ffeb3b;
  }

  .crossword-cell.same-word {
    background-color: #e3f2fd;
  }

  .crossword-cell.correct {
    background-color: #c8e6c9;
  }

  .crossword-cell.incorrect {
    background-color: #ffcdd2;
  }

  .word-index {
    position: absolute;
    top: 2px;
    left: 2px;
    font-size: 10px;
    color: #333;
    z-index: 10;
  }

  .cell-input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-align: center;
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    border: none;
    background: transparent;
    outline: none;
    padding: 0;
  }

  .list-group-item.active {
    background-color: #e3f2fd;
    color: #333;
    border-color: #b3e5fc;
  }

  .clues-container {
    max-height: 500px;
    overflow-y: auto;
  }

  .game-timer {
    font-weight: bold;
    padding: 5px 10px;
    background-color: #f0f0f0;
    border-radius: 4px;
  }
  </style>

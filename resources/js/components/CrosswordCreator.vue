<template>
    <div class="crossword-creator">
      <div class="card">
        <div class="card-header">
          <h3>Create New Crossword</h3>
        </div>
        <div class="card-body">
          <div v-if="error" class="alert alert-danger">{{ error }}</div>

          <div class="form-group mb-3">
            <label for="title" class="form-label">Crossword Title</label>
            <input
              type="text"
              id="title"
              v-model="title"
              class="form-control"
              placeholder="Enter a title for your crossword"
              required
            />
          </div>

          <div class="row">
            <div class="col-md-6">
              <h4>Add Words</h4>

              <div class="form-group mb-3">
                <label for="word" class="form-label">Word</label>
                <input
                  type="text"
                  id="word"
                  v-model="currentWord"
                  class="form-control"
                  placeholder="Enter a word"
                  @keyup.enter="focusClue"
                  ref="wordInput"
                />
              </div>

              <div class="form-group mb-3">
                <label for="clue" class="form-label">Clue</label>
                <input
                  type="text"
                  id="clue"
                  v-model="currentClue"
                  class="form-control"
                  placeholder="Enter a clue for this word"
                  @keyup.enter="addWord"
                  ref="clueInput"
                />
              </div>

              <button
                class="btn btn-primary me-2"
                @click="addWord"
                :disabled="!currentWord || !currentClue"
              >
                Add Word
              </button>

              <button
                class="btn btn-success"
                @click="previewCrossword"
                :disabled="words.length < 3"
              >
                Generate Preview
              </button>

              <hr />

              <div v-if="words.length > 0">
                <h4>Words List ({{ words.length }})</h4>
                <ul class="list-group">
                  <li
                    v-for="(wordItem, index) in words"
                    :key="index"
                    class="list-group-item d-flex justify-content-between align-items-center"
                  >
                    <div>
                      <strong>{{ wordItem.word }}</strong>
                      <p class="mb-0 text-muted">{{ wordItem.clue }}</p>
                    </div>
                    <button
                      class="btn btn-sm btn-danger"
                      @click="removeWord(index)"
                    >
                      Remove
                    </button>
                  </li>
                </ul>
              </div>
            </div>

            <div class="col-md-6">
              <h4>Crossword Preview</h4>

              <div v-if="loading" class="text-center p-5">
                <div class="spinner-border" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Generating crossword...</p>
              </div>

              <div v-else-if="gridData.length > 0" class="crossword-preview">
                <div class="crossword-grid">
                  <div v-for="(row, rowIndex) in gridData" :key="'row-' + rowIndex" class="crossword-row">
                    <div
                      v-for="(cell, colIndex) in row"
                      :key="'cell-' + rowIndex + '-' + colIndex"
                      class="crossword-cell"
                      :class="{ 'has-letter': cell.letter !== null }"
                    >
                      <span v-if="cell.wordIndex !== null" class="word-index">{{ cell.wordIndex }}</span>
                      <span v-if="cell.letter !== null" class="letter">{{ cell.letter }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else class="text-center p-5 border rounded bg-light">
                <p class="mb-0">Add at least 3 words and click "Generate Preview" to see your crossword.</p>
              </div>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-between">
            <button class="btn btn-secondary" @click="resetForm">Clear All</button>
            <div>
              <button
                class="btn btn-primary me-2"
                @click="saveCrossword(false)"
                :disabled="publishing || words.length < 3 || !title"
              >
                Save as Draft
              </button>
              <button
                class="btn btn-success"
                @click="saveCrossword(true)"
                :disabled="publishing || words.length < 3 || !title"
              >
                Save & Publish
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted } from 'vue';
  import { useCrosswordCreatorStore } from '../store/crosswordStore';
  import { storeToRefs } from 'pinia';

  const store = useCrosswordCreatorStore();
  const {
    title, words, currentWord, currentClue, gridData,
    loading, error, publishing
  } = storeToRefs(store);

  const wordInput = ref(null);
  const clueInput = ref(null);

  const addWord = () => {
    if (currentWord.value.trim() && currentClue.value.trim()) {
      store.addWord();
      wordInput.value.focus();
    }
  };

  const focusClue = () => {
    if (currentWord.value.trim()) {
      clueInput.value.focus();
    }
  };

  const removeWord = (index) => {
    store.removeWord(index);
  };

  const previewCrossword = async () => {
    await store.generateCrossword();
  };

  const saveCrossword = async (shouldPublish) => {
    const result = await store.saveCrossword(shouldPublish);

    if (result) {
      window.location.href = `/crosswords/${result.id}`;
    }
  };

  const resetForm = () => {
    if (confirm('Are you sure you want to clear all entered data?')) {
      store.resetForm();
    }
  };

  onMounted(() => {
    wordInput.value.focus();
  });
  </script>

  <style scoped>
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

  .word-index {
    position: absolute;
    top: 2px;
    left: 2px;
    font-size: 10px;
    color: #333;
  }

  .letter {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    font-weight: bold;
  }
  </style>

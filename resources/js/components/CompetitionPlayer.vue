<!-- resources/js/components/CompetitionPlayer.vue -->
<template>
    <div class="competition-player">
      <div class="competition-info alert alert-info mb-4">
        <h4>{{ competition?.title }}</h4>
        <p class="mb-1" v-if="competition?.description">{{ competition.description }}</p>
        <div class="d-flex justify-content-between align-items-center">
          <span>Crossword: <strong>{{ competition?.crossword?.title }}</strong></span>
          <div class="competition-timer" :class="{ 'text-danger': timeRemaining < 300 }">
            Time Remaining: {{ formatTimeRemaining }}
          </div>
        </div>
      </div>

      <crossword-player
        :crossword-id="crosswordId"
        :saved-solution="savedSolution"
        :is-competition="true"
        :competition-id="competitionId"
        :competition-end-time="competitionEndTime"
      />
    </div>
  </template>

  <script setup>
  import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
  import CrosswordPlayer from './CrosswordPlayer.vue';

  const props = defineProps({
    competition: {
      type: Object,
      required: true
    },
    savedSolution: {
      type: Object,
      default: () => null
    }
  });

  const crosswordId = computed(() => props.competition?.crossword?.id);
  const competitionId = computed(() => props.competition?.id);
  const competitionEndTime = computed(() => props.competition?.end_time);

  const timeRemaining = ref(0);
  let countdownTimer = null;

  const formatTimeRemaining = computed(() => {
    if (timeRemaining.value <= 0) return '00:00:00';

    const hours = Math.floor(timeRemaining.value / 3600);
    const minutes = Math.floor((timeRemaining.value % 3600) / 60);
    const seconds = timeRemaining.value % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
  });

  const calculateTimeRemaining = () => {
    if (!props.competition?.end_time) return 0;

    const now = new Date();
    const endTime = new Date(props.competition.end_time);
    const diff = Math.max(0, Math.floor((endTime - now) / 1000));

    return diff;
  };

  const startCountdown = () => {
    timeRemaining.value = calculateTimeRemaining();

    countdownTimer = setInterval(() => {
      timeRemaining.value--;

      if (timeRemaining.value <= 0) {
        clearInterval(countdownTimer);

        // Force page reload to show results
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      }
    }, 1000);
  };

  onMounted(() => {
    startCountdown();
  });

  onBeforeUnmount(() => {
    if (countdownTimer) {
      clearInterval(countdownTimer);
    }
  });
  </script>

  <style scoped>
  .competition-timer {
    font-weight: bold;
    font-size: 1.2rem;
    padding: 5px 10px;
    background-color: #f0f0f0;
    border-radius: 4px;
  }
  </style>

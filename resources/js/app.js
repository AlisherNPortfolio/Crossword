import './bootstrap';
import { createApp } from 'vue/dist/vue.esm-bundler.js';
import { createPinia } from 'pinia';

// Vue Components
import CrosswordCreator from './components/CrosswordCreator.vue';
import CrosswordPlayer from './components/CrosswordPlayer.vue';
import CompetitionPlayer from './components/CompetitionPlayer.vue';

// Initialize Pinia store
const pinia = createPinia();

// Create Vue application
const app = createApp({});

// Use plugins
app.use(pinia);

// Register global components
app.component('crossword-creator', CrosswordCreator);
app.component('crossword-player', CrosswordPlayer);
app.component('competition-player', CompetitionPlayer);

// Mount the app
app.mount('#app');

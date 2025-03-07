import { createApp } from 'vue';
import { createPinia } from 'pinia';
import CrosswordCreator from './components/CrosswordCreator.vue';
import CrosswordPlayer from './components/CrosswordPlayer.vue';
import CompetitionPlayer from './components/CompetitionPlayer.vue';

const app = createApp({});
const pinia = createPinia();

app.use(pinia);

app.component('crossword-creator', CrosswordCreator);
app.component('crossword-player', CrosswordPlayer);
app.component('competition-player', CompetitionPlayer);

app.mount('#app');

import './bootstrap';

import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import Categories from './pages/Categories.vue';
import Books from './pages/Books.vue';
import '../css/app.css';

const routes = [
  { path: '/', component: Categories },
  { path: '/books/:category', component: Books, props: true },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Mount the compiled SFC root (App.vue) so the router-view is rendered correctly
createApp(App).use(router).mount('#app');

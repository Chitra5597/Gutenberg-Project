<template>
  <div class="min-h-screen p-4 bg-gray-50">
    <div class="max-w-3xl mx-auto">
      <div class="flex items-center gap-3 mb-3">
        <button @click="$router.back()" class="text-indigo-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <h2 class="text-xl font-semibold text-indigo-600">{{ decodedCategory }}</h2>
      </div>

      <input v-model="searchTerm" @input="onSearch" placeholder="Search" class="border rounded p-2 mb-4 w-full bg-white" />

      <div class="grid grid-cols-3 gap-3">
        <BookCard v-for="b in books" :key="b.id" :book="b" />
      </div>

      <div v-if="!loading && books.length === 0" class="py-10 text-center text-gray-600">No books found.</div>
      <div v-else class="py-6 text-center">{{ loading ? 'Loading...' : '' }}</div>

      <!-- Pagination controls -->
      <div class="flex items-center justify-center gap-4 mt-4">
        <button :disabled="page <= 1 || loading" @click="goToPage(1)" class="px-3 py-1 border rounded bg-white">First</button>
        <button :disabled="page <= 1 || loading" @click="goToPage(page - 1)" class="px-3 py-1 border rounded bg-white">Prev</button>
        <div class="px-3 py-1">Page {{ page }}</div>
        <button :disabled="!next || loading" @click="goToPage(page + 1)" class="px-3 py-1 border rounded bg-white">Next</button>
        <button :disabled="!next || loading" @click="goToPage(Math.ceil((count || 0) / perPage))" class="px-3 py-1 border rounded bg-white">Last</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import BookCard from '../components/BookCard.vue';

export default {
  components: { BookCard },
  props: ['category'],
  data() {
    return {
      books: [],
      next: null,
      page: 1,
      searchTerm: '',
      loading: false,
      // pagination
      perPage: 10,
      count: 0,
    };
  },
  computed: {
    decodedCategory() {
      try { return decodeURIComponent(this.category); } catch(e) { return this.category; }
    }
  },
  mounted() {
    this.resetAndLoad();
  },
  watch: {
    '$route.params.category'(newVal) {
      this.resetAndLoad();
    }
  },
  methods: {
    resetAndLoad() {
      this.books = [];
      this.page = 1;
      this.next = null;
      this.loadBooks(1);
    },
    async loadBooks(page = 1) {
      if (this.loading) return;
      this.loading = true;
      this.page = page;
      const params = {
        topic: this.category,
        page: this.page,
        page_size: this.perPage,
        has_image: 1,
      };
      if (this.searchTerm) {
        params.title = this.searchTerm;
        params.author = this.searchTerm;
      }
      try {
        const res = await axios.get('/api/books', { params });
        if (res.data?.results) {
          // replace current page's books
          this.books = res.data.results;
          this.next = res.data.next;
          this.count = res.data.count || 0;
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.loading = false;
      }
    },
    goToPage(p) {
      if (p < 1) return;
      this.loadBooks(p);
    },
    onSearch() {
      clearTimeout(this._deb);
      this._deb = setTimeout(() => {
        this.books = [];
        this.page = 1;
        this.next = null;
        this.loadBooks();
      }, 400);
    }
  }
}
</script>


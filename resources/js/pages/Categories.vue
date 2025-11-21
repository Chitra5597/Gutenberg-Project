<template>
  <div class="min-h-screen bg-gray-100 flex justify-center py-10 px-5">
    <div class="w-full max-w-3xl">

      <!-- HEADER -->
      <div class="text-center mb-10">
        <h1 class="text-4xl font-bold text-indigo-700">Gutenberg Project</h1>
        <p class="mt-2 text-gray-600">
          A social cataloging website that allows you to freely search its database of books, annotations, and reviews.
        </p>
      </div>

      <!-- CATEGORY LIST -->
      <div v-if="loading" class="text-center text-gray-600">Loading categories...</div>

      <div v-else class="space-y-4">
        <div v-for="c in displayedCategories" :key="c.name"
             @click="open(c.name)"
             class="bg-white rounded-xl shadow hover:shadow-xl transition cursor-pointer p-5 flex justify-between items-center">

          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-xl font-semibold">
              {{ c.name.charAt(0).toUpperCase() }}
            </div>

            <div>
              <h3 class="text-lg font-semibold text-gray-800">{{ c.name }}</h3>
              <p class="text-sm text-gray-500">{{ c.books_count }} books</p>
            </div>
          </div>

          <!-- Arrow -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-500" fill="none"
               viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>

      <!-- PAGINATION -->
      <div class="mt-8 flex justify-between items-center text-sm text-gray-700">
        <div>Showing {{ showingRange.from }} - {{ showingRange.to }} of {{ totalCount }}</div>

        <div class="flex items-center gap-2">
          <button @click="goToPage(1)" :disabled="page === 1"
                  class="px-3 py-1 bg-white border rounded disabled:opacity-40">First</button>

          <button @click="goToPage(page - 1)" :disabled="page === 1"
                  class="px-3 py-1 bg-white border rounded disabled:opacity-40">Prev</button>

          <span class="px-2">Page {{ page }} / {{ totalPages }}</span>

          <button @click="goToPage(page + 1)" :disabled="page === totalPages"
                  class="px-3 py-1 bg-white border rounded disabled:opacity-40">Next</button>

          <button @click="goToPage(totalPages)" :disabled="page === totalPages"
                  class="px-3 py-1 bg-white border rounded disabled:opacity-40">Last</button>
        </div>
      </div>

      <div v-if="error" class="mt-4 text-red-600">{{ error }}</div>

    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      categories: [],
      loading: true,
      error: null,
      // pagination
      page: 1,
      perPage: 10,
      totalResults: 0,
    };
  },
  computed: {
    totalCount() {
      return this.totalResults || 0;
    },
    totalPages() {
      return Math.max(1, Math.ceil(this.totalCount / this.perPage));
    },
    displayedCategories() {
      // server returns only the page results, so display them directly
      return this.categories || [];
    },
    showingRange() {
      if (this.totalCount === 0) return { from: 0, to: 0 };
      const from = (this.page - 1) * this.perPage + 1;
      const to = Math.min(this.totalCount, this.page * this.perPage);
      return { from, to };
    }
  },
  methods: {
    open(c) {
      this.$router.push({ path: `/books/${encodeURIComponent(c)}`});
    },
    async loadPage(p = 1) {
      this.loading = true;
      try {
        const params = { page: p, page_size: this.perPage, popular: 1 };
        const res = await window.axios.get('/api/subjects', { params });
        if (res.data) {
          this.categories = Array.isArray(res.data.results) ? res.data.results : [];
          this.totalResults = res.data.count || 0;
          this.page = res.data.page || p;
        }
      } catch (e) {
        console.error('Failed to load categories', e);
        this.error = 'Failed to load categories';
      } finally {
        this.loading = false;
      }
    },
    goToPage(p) {
      if (p < 1) p = 1;
      if (p > this.totalPages) p = this.totalPages;
      this.page = p;
      this.loadPage(p);
      // scroll to top of list for convenience
      this.$nextTick(() => {
        const el = this.$el.querySelector('.mt-2');
        if (el) el.scrollIntoView({ behavior: 'smooth' });
      });
    }
  },
  async mounted() {
    await this.loadPage(1);
  }
}
</script>

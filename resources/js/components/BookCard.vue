<template>
  <div @click="openBook" class="cursor-pointer">
    <div class="bg-white rounded overflow-hidden shadow-sm hover:shadow-md transition">
      <div class="w-full h-36 bg-gray-100 flex items-center justify-center overflow-hidden">
        <img v-if="coverUrl" :src="coverUrl" class="w-full h-full object-cover" alt="cover" />
        <div v-else class="text-gray-400 text-xs">No image</div>
      </div>
      <div class="p-2">
        <div class="text-xs font-semibold leading-tight truncate">{{ book.title }}</div>
        <div class="text-xxs text-gray-500 truncate text-xs">{{ authorNames }}</div>
          <!-- <div v-if="limitedSubjects.length" class="text-xxs text-gray-400 mt-1">
            <span v-for="(subject, idx) in limitedSubjects" :key="idx">
              {{ subject }}<span v-if="idx < limitedSubjects.length - 1">, </span>
            </span>
          </div> -->
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['book'],
  computed: {
    coverUrl() {
      const f = this.book.formats || {};
      for (const k in f) {
        if ((k && k.toLowerCase().includes('image')) || (String(f[k]).toLowerCase().includes('image'))) {
          return f[k];
        }
      }
      return null;
    },
    authorNames() {
      if (!this.book.authors) return '';
      return this.book.authors.map(a => a.name).join(', ');
    },
    limitedSubjects() {
        if (!this.book.subjects || !Array.isArray(this.book.subjects)) return [];
        return this.book.subjects.slice(0, 10);
    }
  },
  methods: {
    openBook() {
      const formats = this.book.formats || {};
      const priority = ['text/html', 'application/pdf', 'text/plain'];
      for (const p of priority) {
        for (const k in formats) {
          if (k.toLowerCase().startsWith(p) || String(formats[k]).toLowerCase().includes(p)) {
            const url = formats[k];
            if (!String(url).endsWith('.zip')) { window.open(url, '_blank'); return; }
          }
        }
      }
      for (const k in formats) {
        const url = formats[k];
        if (!String(url).endsWith('.zip')) { window.open(url, '_blank'); return; }
      }
      alert("No viewable version available");
    }
  }
}
</script>

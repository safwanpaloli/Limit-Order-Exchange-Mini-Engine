<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col selection:bg-indigo-500 selection:text-white antialiased font-sans">
    <!-- Top Navigation -->
    <header class="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-red-500 via-emerald-500 to-indigo-500 p-0.5 shadow-lg shadow-indigo-500/20">
            <div class="h-full w-full bg-slate-950 rounded-[10px] flex items-center justify-center">
              <span class="text-xs font-black tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-red-400 via-emerald-400 to-indigo-400">L·V</span>
            </div>
          </div>
          <div>
            <h1 class="text-base font-bold text-white tracking-tight leading-none">Laravel <span class="text-slate-400 font-normal">&amp;</span> Vue 3</h1>
            <p class="text-xs text-slate-400">Tailwind CSS + Laravel Herd</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            Herd PHP 8.4 Active
          </div>
          <a
            href="https://laravel.com/docs"
            target="_blank"
            rel="noreferrer"
            class="hidden sm:inline-flex items-center text-xs font-semibold px-3 py-1.5 rounded-lg text-slate-300 bg-slate-800/80 hover:bg-slate-700/80 border border-slate-700 transition duration-150"
          >
            Laravel Docs
          </a>
        </div>
      </div>
    </header>

    <!-- Main Hero -->
    <main class="flex-1 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
      <!-- Glow background circles -->
      <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -bottom-32 left-1/4 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

      <div class="max-w-4xl w-full mx-auto space-y-10 relative z-10">
        <!-- Badge & Headline -->
        <div class="text-center space-y-4">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-medium bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 border border-indigo-500/20 text-indigo-300">
            <span>✨ Full Stack Ready</span>
            <span class="text-slate-500">•</span>
            <span>Vite + Vue 3 SFCs + Tailwind CSS v4</span>
          </div>

          <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white">
            Modern Full-Stack
            <span class="block mt-1 text-transparent bg-clip-text bg-gradient-to-r from-red-400 via-emerald-400 to-indigo-400">
              Laravel + Vue 3 Suite
            </span>
          </h2>

          <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto leading-relaxed">
            Your application is bootstrapped with high-performance tooling: Laravel backend, Vue 3 reactive frontend, Tailwind CSS utility styling, and local serving via Herd.
          </p>
        </div>

        <!-- Interactive Vue 3 Card (Demonstrating Reactive State) -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
              <h3 class="text-lg font-semibold text-white">Vue 3 Composition API Test</h3>
              <p class="text-xs text-slate-400">Test live reactive state &amp; micro-interactions powered by Vue 3</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
              <span class="text-xs text-slate-400">Current count:</span>
              <span class="px-2.5 py-1 rounded-md bg-indigo-500/20 text-indigo-300 font-mono font-bold text-sm border border-indigo-500/30">
                {{ count }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button
              @click="increment"
              class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm text-white bg-indigo-600 hover:bg-indigo-500 active:scale-95 transition-all shadow-lg shadow-indigo-600/30 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Increment ({{ count }})
            </button>

            <button
              @click="decrement"
              class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm text-slate-200 bg-slate-800 hover:bg-slate-700 active:scale-95 transition-all border border-slate-700 cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
              </svg>
              Decrement
            </button>

            <button
              @click="reset"
              class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-medium text-sm text-slate-400 hover:text-slate-200 bg-slate-800/40 hover:bg-slate-800 transition-all border border-slate-800 cursor-pointer"
            >
              Reset Counter
            </button>
          </div>

          <!-- Feature Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
            <div
              v-for="feature in features"
              :key="feature.name"
              class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700/80 transition-colors"
            >
              <div class="flex items-center gap-3 mb-2">
                <span class="text-xl">{{ feature.icon }}</span>
                <h4 class="font-semibold text-sm text-white">{{ feature.name }}</h4>
              </div>
              <p class="text-xs text-slate-400 leading-normal">{{ feature.desc }}</p>
              <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 font-mono">
                <span>{{ feature.version }}</span>
                <span class="text-emerald-400 flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Verified
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Tech Stack Highlights -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
          <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800">
            <div class="text-xs font-semibold text-red-400">Laravel</div>
            <div class="text-sm font-bold text-white mt-0.5">Framework 13</div>
          </div>
          <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800">
            <div class="text-xs font-semibold text-emerald-400">Vue 3</div>
            <div class="text-sm font-bold text-white mt-0.5">Composition API</div>
          </div>
          <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800">
            <div class="text-xs font-semibold text-sky-400">Tailwind CSS</div>
            <div class="text-sm font-bold text-white mt-0.5">v4 Engine</div>
          </div>
          <div class="p-3.5 rounded-xl bg-slate-900/40 border border-slate-800">
            <div class="text-xs font-semibold text-amber-400">Laravel Herd</div>
            <div class="text-sm font-bold text-white mt-0.5">PHP 8.4 NTS</div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 py-6 text-center text-xs text-slate-500">
      <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>Built for the Private Suite Assessment • Running locally via Laravel Herd</p>
        <p class="font-mono text-slate-600">Vite HMR Active</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const count = ref(0);

const increment = () => {
  count.value++;
};

const decrement = () => {
  if (count.value > 0) {
    count.value--;
  }
};

const reset = () => {
  count.value = 0;
};

const features = [
  {
    name: 'Laravel Backend',
    icon: '⚡',
    version: 'Laravel 13',
    desc: 'Robust routing, Eloquent ORM, Artisan CLI, and enterprise-grade backend infrastructure.',
  },
  {
    name: 'Vue 3 SFCs',
    icon: '💚',
    version: 'Vue 3.5+',
    desc: 'Reactive Single File Components with `<script setup>`, fast compilation, and seamless state.',
  },
  {
    name: 'Tailwind CSS v4',
    icon: '🎨',
    version: 'Tailwind v4',
    desc: 'Lightning-fast CSS-first configuration, zero-runtime overhead, and modern utility classes.',
  },
];
</script>

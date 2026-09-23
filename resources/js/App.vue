<template>
  <div class="min-h-screen bg-slate-50 text-gunmetal flex flex-col font-sans selection:bg-thatch/30">
    <header v-if="isLoggedIn" class="border-b border-elephant/20 bg-mushroom/80 sticky top-0 z-50 backdrop-blur-md">
      <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-6">
          <h1 class="text-lg font-bold text-gunmetal tracking-tight">Limit Exchange</h1>
          <nav class="hidden sm:flex space-x-6">
            <router-link to="/trade" class="text-sm font-medium text-elephant hover:text-gunmetal transition-colors" active-class="text-gunmetal font-semibold border-b-2 border-thatch pb-1">Trade Terminal</router-link>
            <router-link to="/profile" class="text-sm font-medium text-elephant hover:text-gunmetal transition-colors" active-class="text-gunmetal font-semibold border-b-2 border-thatch pb-1">Wallet & Orders</router-link>
          </nav>
        </div>
        <button @click="logout" class="text-sm px-4 py-2 bg-transparent hover:bg-elephant/10 text-gunmetal font-medium rounded-lg transition-colors border border-elephant/30 cursor-pointer">Logout</button>
      </div>
    </header>

    <main class="flex-1">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuth } from './composables/useAuth';

const { isLoggedIn, checkAuth, logout } = useAuth();

onMounted(() => {
  checkAuth();
});
</script>

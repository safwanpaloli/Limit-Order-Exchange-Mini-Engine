<template>
  <div class="min-h-screen bg-slate-50 text-gunmetal flex flex-col font-sans selection:bg-thatch/30">
    <header v-if="isLoggedIn" class="border-b border-elephant/20 bg-mushroom/80 sticky top-0 z-50 backdrop-blur-md">
      <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-6">
          <h1 class="text-lg font-bold text-gunmetal tracking-tight">Limit Exchange</h1>
          <nav class="hidden sm:flex space-x-6">
            <router-link to="/profile" class="text-sm font-medium text-elephant hover:text-gunmetal transition-colors" active-class="text-gunmetal font-semibold border-b-2 border-thatch pb-1">Wallet & Orders</router-link>
            <!-- Trade route placeholder -->
          </nav>
        </div>
        <button @click="logout" class="text-sm px-4 py-2 bg-transparent hover:bg-elephant/10 text-gunmetal font-medium rounded-lg transition-colors border border-elephant/30 cursor-pointer">Logout</button>
      </div>
    </header>

    <main class="flex-1">
      <router-view @login="checkAuth"></router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const isLoggedIn = ref(false);

const checkAuth = () => {
  isLoggedIn.value = !!localStorage.getItem('auth_token');
};

const logout = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    await axios.post('/api/logout', {}, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
  } catch (e) {
    // ignore
  } finally {
    localStorage.removeItem('auth_token');
    isLoggedIn.value = false;
    router.push('/');
  }
};

onMounted(() => {
  checkAuth();
});
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div v-if="loading" class="text-center py-20 text-elephant animate-pulse font-medium">Loading profile...</div>
    <div v-else-if="user" class="space-y-10">
      <!-- Wallet Overview -->
      <section>
        <h2 class="text-xl font-bold mb-6 text-gunmetal tracking-tight">Wallet Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white/60 border border-elephant/20 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-sm font-medium text-elephant mb-2 uppercase tracking-wide">USD Balance</h3>
            <div class="text-3xl font-bold text-gunmetal">${{ parseFloat(user.balance).toFixed(2) }}</div>
          </div>
          
          <div v-for="asset in assets" :key="asset.id" class="bg-white/60 border border-elephant/20 rounded-2xl p-6 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-sm font-medium text-elephant mb-2 uppercase tracking-wide">{{ asset.symbol }} Balance</h3>
            <div class="text-3xl font-bold text-gunmetal">{{ parseFloat(asset.amount).toFixed(8) }}</div>
            <div class="text-sm text-thatch mt-2 font-medium">Locked: {{ parseFloat(asset.locked_amount).toFixed(8) }}</div>
          </div>
        </div>
      </section>
      
      <!-- Placeholder for orders -->
      <section>
        <h2 class="text-xl font-bold mb-6 text-gunmetal tracking-tight mt-8">Recent Orders</h2>
        <div class="bg-mushroom/50 border border-elephant/20 rounded-2xl p-12 text-center text-elephant text-sm font-medium border-dashed">
          Order history and orderbook will be implemented in the next phase.
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const loading = ref(true);
const user = ref(null);
const assets = ref([]);

const fetchProfile = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const res = await axios.get('/api/profile', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    
    const data = res.data;
    user.value = data.user;
    assets.value = data.assets;
  } catch (e) {
    if (e.response && e.response.status === 401) {
      localStorage.removeItem('auth_token');
      router.push('/');
    } else {
      console.error('Failed to load profile', e);
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchProfile();
});
</script>

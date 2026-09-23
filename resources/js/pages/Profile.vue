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
      
      <!-- Active Orders -->
      <section>
        <h2 class="text-xl font-bold mb-6 text-gunmetal tracking-tight mt-8">Active Orders</h2>
        <div v-if="orders.length === 0" class="bg-mushroom/50 border border-elephant/20 rounded-2xl p-12 text-center text-elephant text-sm font-medium border-dashed">
          You have no active orders.
        </div>
        <div v-else class="bg-white/60 border border-elephant/20 rounded-2xl overflow-hidden shadow-sm">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-mushroom/30 border-b border-elephant/20">
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Side</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Symbol</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Price</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Amount</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-elephant/10">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-mushroom/10 transition-colors">
                <td class="py-4 px-6 text-sm font-medium">
                  <span :class="order.side === 'buy' ? 'text-green-600' : 'text-thatch'">
                    {{ order.side.toUpperCase() }}
                  </span>
                </td>
                <td class="py-4 px-6 text-sm text-gunmetal font-medium">{{ order.symbol }}</td>
                <td class="py-4 px-6 text-sm text-gunmetal">${{ parseFloat(order.price).toFixed(2) }}</td>
                <td class="py-4 px-6 text-sm text-gunmetal">{{ parseFloat(order.amount).toFixed(8) }}</td>
                <td class="py-4 px-6 text-right">
                  <button @click="confirmCancel(order)" :disabled="cancelling === order.id" class="text-sm text-thatch hover:text-thatch/70 font-medium transition-colors disabled:opacity-50 cursor-pointer">
                    {{ cancelling === order.id ? 'Cancelling...' : 'Cancel' }}
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
    
    <!-- Cancel Confirmation Modal -->
    <OrderModal
      :show="showCancelModal"
      title="Cancel Order"
      message="Are you sure you want to cancel this order?"
      :actionText="`${orderToCancel?.side} ${orderToCancel?.symbol}`"
      :actionSide="orderToCancel?.side"
      :price="orderToCancel?.price"
      :amount="orderToCancel?.amount"
      cancelText="Keep Order"
      confirmText="Cancel Order"
      confirmingText="Cancelling..."
      :isConfirming="cancelling === orderToCancel?.id"
      @close="showCancelModal = false"
      @confirm="executeCancel"
    />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import OrderModal from '../components/OrderModal.vue';
import { useProfile } from '../composables/useProfile';

const {
  loading,
  user,
  assets,
  orders,
  cancelling,
  showCancelModal,
  orderToCancel,
  fetchProfile,
  listenForWalletUpdates,
  confirmCancel,
  executeCancel
} = useProfile();

onMounted(async () => {
  const userId = await fetchProfile();
  if (userId) {
    listenForWalletUpdates(userId);
  }
});
</script>

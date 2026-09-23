<template>
  <div class="max-w-7xl mx-auto px-4 py-12">
    <div v-if="loading" class="text-center py-20 text-elephant animate-pulse font-medium">Loading profile...</div>
    <div v-else-if="user" class="space-y-10">
      <!-- Wallet Overview -->
      <section>
        <h2 class="text-xl font-bold mb-6 text-gunmetal tracking-tight">Wallet Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white/60 border border-elephant/20 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-sm font-medium text-elephant mb-2 uppercase tracking-wide">USD Balance</h3>
            <div class="text-3xl font-bold text-gunmetal">${{ parseFloat(user.balance).toFixed(2) }}</div>
          </div>
          
          <div v-for="asset in assets" :key="asset.id" class="bg-white/60 border border-elephant/20 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow">
            <h3 class="text-sm font-medium text-elephant mb-2 uppercase tracking-wide">{{ asset.symbol }} Balance</h3>
            <div class="text-3xl font-bold text-gunmetal">{{ parseFloat(asset.amount).toFixed(8) }}</div>
            <div class="text-sm text-thatch mt-2 font-medium">Locked: {{ parseFloat(asset.locked_amount).toFixed(8) }}</div>
          </div>
        </div>
      </section>
      
      <!-- Order History -->
      <section>
        <div class="flex items-center justify-between mb-6 mt-8">
          <h2 class="text-xl font-bold text-gunmetal tracking-tight">Order History</h2>
          <div class="flex gap-4">
            <CustomSelect 
              v-model="filterSide" 
              :options="[
                { value: 'all', label: 'All Sides' },
                { value: 'buy', label: 'Buy' },
                { value: 'sell', label: 'Sell' }
              ]"
              buttonClass="min-w-[120px]"
            />
              <CustomSelect 
              v-model="filterStatus" 
              :options="[
                { value: 'all', label: 'All Statuses' },
                { value: '1', label: 'Open' },
                { value: '2', label: 'Completed' },
                { value: '3', label: 'Cancelled' }
              ]"
              buttonClass="min-w-[140px]"
            />
          </div>
        </div>

        <div v-if="orders.length === 0" class="bg-mushroom/50 border border-elephant/20 rounded-lg p-12 text-center text-elephant text-sm font-medium border-dashed">
          No orders match your filters.
        </div>
        <div v-else class="bg-white/60 border border-elephant/20 rounded-lg overflow-hidden shadow-sm">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-mushroom/30 border-b border-elephant/20">
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Side</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Symbol</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Price</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Amount</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider">Status</th>
                <th class="py-4 px-6 text-xs font-semibold text-elephant uppercase tracking-wider text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-elephant/10">
              <tr v-for="order in orders" :key="order.id" class="hover:bg-mushroom/10 transition-colors" :class="{'opacity-60': order.status !== 1}">
                <td class="py-4 px-6 text-sm font-medium">
                  <span :class="order.side === 'buy' ? 'text-green-600' : 'text-thatch'">
                    {{ order.side.toUpperCase() }}
                  </span>
                </td>
                <td class="py-4 px-6 text-sm text-gunmetal font-medium">{{ order.symbol }}</td>
                <td class="py-4 px-6 text-sm text-gunmetal">${{ parseFloat(order.price).toFixed(2) }}</td>
                <td class="py-4 px-6 text-sm text-gunmetal">{{ parseFloat(order.amount).toFixed(8) }}</td>
                <td class="py-4 px-6 text-sm font-medium">
                  <span v-if="order.status === 1" class="px-2 py-1 bg-elephant/10 text-gunmetal rounded text-xs">Open</span>
                  <span v-else-if="order.status === 2" class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Completed</span>
                  <span v-else class="px-2 py-1 bg-thatch/10 text-thatch rounded text-xs">Cancelled</span>
                </td>
                <td class="py-4 px-6 text-right">
                  <button v-if="order.status === 1" @click="confirmCancel(order)" :disabled="cancelling === order.id" class="text-sm text-thatch hover:text-thatch/70 font-medium transition-colors disabled:opacity-50 cursor-pointer">
                    {{ cancelling === order.id ? 'Cancelling...' : 'Cancel' }}
                  </button>
                  <span v-else class="text-xs text-elephant">-</span>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Pagination Controls -->
          <div class="px-6 py-4 border-t border-elephant/10 flex items-center justify-between bg-white/40">
            <div class="flex items-center gap-4">
              <span class="text-sm text-elephant font-medium">
                Page {{ currentPage }} of {{ totalPages }}
              </span>
              <CustomSelect 
                v-model="perPage" 
                :options="[
                  { value: 5, label: '5 per page' },
                  { value: 10, label: '10 per page' },
                  { value: 25, label: '25 per page' },
                  { value: 50, label: '50 per page' }
                ]"
                buttonClass="min-w-[130px] !bg-white/60 !py-1.5"
                dropdownPosition="bottom-full mb-1"
              />
            </div>
            <div class="flex gap-2">
              <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-elephant/20 text-gunmetal hover:bg-mushroom/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Previous
              </button>
              <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-elephant/20 text-gunmetal hover:bg-mushroom/30 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                Next
              </button>
            </div>
          </div>
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
import CustomSelect from '../components/CustomSelect.vue';
import { useProfile } from '../composables/useProfile';

const {
  loading,
  user,
  assets,
  orders,
  filterSide,
  filterStatus,
  perPage,
  currentPage,
  totalPages,
  nextPage,
  prevPage,
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

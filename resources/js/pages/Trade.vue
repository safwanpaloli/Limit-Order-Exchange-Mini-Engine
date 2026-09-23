<template>
  <div class="max-w-7xl mx-auto px-4 py-8 flex flex-col lg:flex-row gap-8">
    <!-- Left Column: Orderbook -->
    <div class="w-full lg:w-2/3 flex flex-col gap-6">
      <div class="bg-white/60 border border-elephant/20 rounded-2xl p-6 shadow-sm flex-1">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gunmetal tracking-tight">Orderbook <span class="text-sm font-normal text-elephant ml-2">BTC/USD</span></h2>
          <button @click="fetchOrderbook" class="text-sm text-elephant hover:text-gunmetal transition-colors flex items-center gap-1 cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            Refresh
          </button>
        </div>

        <div v-if="loadingOrderbook" class="text-center py-12 text-elephant animate-pulse font-medium">Loading orderbook...</div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Bids (Buy Orders) -->
          <div>
            <h3 class="text-sm font-semibold text-green-600 uppercase tracking-wider mb-4 border-b border-elephant/10 pb-2">Bids (Buy)</h3>
            <div v-if="orderbook.buy.length === 0" class="text-sm text-elephant text-center py-4">No open bids.</div>
            <table v-else class="w-full text-left text-sm">
              <thead>
                <tr class="text-elephant">
                  <th class="py-2 font-medium">Price</th>
                  <th class="py-2 font-medium text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orderbook.buy" :key="order.id" class="hover:bg-mushroom/20 transition-colors">
                  <td class="py-2 text-green-600 font-medium">${{ parseFloat(order.price).toFixed(2) }}</td>
                  <td class="py-2 text-gunmetal text-right">{{ parseFloat(order.amount).toFixed(8) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Asks (Sell Orders) -->
          <div>
            <h3 class="text-sm font-semibold text-thatch uppercase tracking-wider mb-4 border-b border-elephant/10 pb-2">Asks (Sell)</h3>
            <div v-if="orderbook.sell.length === 0" class="text-sm text-elephant text-center py-4">No open asks.</div>
            <table v-else class="w-full text-left text-sm">
              <thead>
                <tr class="text-elephant">
                  <th class="py-2 font-medium">Price</th>
                  <th class="py-2 font-medium text-right">Amount</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="order in orderbook.sell" :key="order.id" class="hover:bg-mushroom/20 transition-colors">
                  <td class="py-2 text-thatch font-medium">${{ parseFloat(order.price).toFixed(2) }}</td>
                  <td class="py-2 text-gunmetal text-right">{{ parseFloat(order.amount).toFixed(8) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column: Order Form -->
    <div class="w-full lg:w-1/3">
      <div class="bg-mushroom/30 border border-elephant/20 rounded-2xl p-6 shadow-sm sticky top-24">
        <h2 class="text-xl font-bold text-gunmetal tracking-tight mb-6">Place Limit Order</h2>
        
        <!-- Toggle Buy/Sell -->
        <div class="flex bg-white/60 rounded-xl p-1 mb-6 border border-elephant/20">
          <button @click="side = 'buy'" :class="side === 'buy' ? 'bg-green-600 text-white shadow-md' : 'text-gunmetal hover:bg-mushroom/50'" class="flex-1 py-2 rounded-lg text-sm font-bold transition-all cursor-pointer">Buy</button>
          <button @click="side = 'sell'" :class="side === 'sell' ? 'bg-thatch text-white shadow-md' : 'text-gunmetal hover:bg-mushroom/50'" class="flex-1 py-2 rounded-lg text-sm font-bold transition-all cursor-pointer">Sell</button>
        </div>

        <form @submit.prevent="placeOrder" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-elephant mb-1.5">Price (USD)</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-elephant font-medium">$</span>
              <input v-model.number="price" type="number" step="0.01" min="0.01" class="w-full bg-white border border-elephant/30 rounded-xl pl-8 pr-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all font-mono" required>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-elephant mb-1.5">Amount (BTC)</label>
            <input v-model.number="amount" type="number" step="0.00000001" min="0.00000001" class="w-full bg-white border border-elephant/30 rounded-xl px-4 py-3 text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch focus:border-transparent transition-all font-mono" required>
          </div>
          
          <div class="pt-2 pb-4 border-b border-elephant/10">
            <div class="flex justify-between items-center text-sm">
              <span class="font-medium text-elephant">Total Value:</span>
              <span class="font-bold text-gunmetal">${{ totalValue.toFixed(2) }}</span>
            </div>
          </div>
          
          <div v-if="error" class="text-thatch text-sm bg-thatch/10 p-3 rounded-lg border border-thatch/20">
            {{ error }}
          </div>
          
          <div v-if="success" class="text-green-700 text-sm bg-green-50 p-3 rounded-lg border border-green-200">
            {{ success }}
          </div>

          <button :disabled="submitting || price <= 0 || amount <= 0" type="button" @click="confirmOrder" :class="side === 'buy' ? 'bg-green-600 hover:bg-green-700' : 'bg-thatch hover:bg-thatch/90'" class="w-full text-white font-bold py-3.5 rounded-xl transition-all cursor-pointer disabled:opacity-50 shadow-md mt-2">
            {{ side === 'buy' ? 'Place Buy Order' : 'Place Sell Order' }}
          </button>
        </form>
      </div>
    </div>
    
    <!-- Confirmation Modal -->
    <OrderModal
      :show="showModal"
      title="Confirm Order"
      :actionText="`${side} BTC`"
      :actionSide="side"
      :price="price"
      :amount="amount"
      :total="totalValue"
      cancelText="Cancel"
      confirmText="Confirm"
      confirmingText="Confirming..."
      :isConfirming="submitting"
      @close="showModal = false"
      @confirm="executeOrder"
    />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import OrderModal from '../components/OrderModal.vue';
import { useTrade } from '../composables/useTrade';

const {
  side,
  price,
  amount,
  submitting,
  error,
  success,
  showModal,
  orderbook,
  loadingOrderbook,
  totalValue,
  fetchOrderbook,
  listenForOrderbookUpdates,
  confirmOrder,
  executeOrder
} = useTrade();

onMounted(() => {
  fetchOrderbook();
  listenForOrderbookUpdates('BTC');
});
</script>

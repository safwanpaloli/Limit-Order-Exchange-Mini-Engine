<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-gunmetal/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm overflow-hidden border border-elephant/10 transform transition-all">
      <div class="p-6 border-b border-elephant/10">
        <h3 class="text-xl font-bold text-gunmetal tracking-tight text-center">{{ title }}</h3>
      </div>
      <div class="p-6 space-y-4 text-center text-gunmetal">
        <p v-if="message" class="mb-4 text-sm font-medium">{{ message }}</p>
        <div :class="message ? 'bg-mushroom/20 p-4 rounded-xl border border-elephant/10 flex flex-col gap-2 text-sm text-left' : 'space-y-4'">
          <div class="flex justify-between items-center text-sm">
            <span class="text-elephant font-medium">Action</span>
            <span :class="actionSide === 'buy' ? 'text-green-600' : 'text-thatch'" class="font-bold uppercase tracking-wider">{{ actionText }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-elephant font-medium">Price</span>
            <span class="text-gunmetal font-bold">${{ parseFloat(price || 0).toFixed(2) }}</span>
          </div>
          <div class="flex justify-between items-center text-sm">
            <span class="text-elephant font-medium">Amount</span>
            <span class="text-gunmetal font-bold">{{ parseFloat(amount || 0).toFixed(8) }}</span>
          </div>
          <div v-if="total !== undefined" class="pt-4 border-t border-elephant/10 flex justify-between items-center text-sm">
            <span class="text-elephant font-medium">Total Value</span>
            <span class="text-gunmetal font-bold text-lg">${{ parseFloat(total || 0).toFixed(2) }}</span>
          </div>
        </div>
      </div>
      <div class="p-6 bg-slate-50 border-t border-elephant/10 flex gap-4">
        <button @click="$emit('close')" :disabled="isConfirming" class="flex-1 py-3 text-sm font-bold text-elephant hover:text-gunmetal hover:bg-elephant/10 rounded-xl transition-colors disabled:opacity-50 cursor-pointer">
          {{ cancelText }}
        </button>
        <button @click="$emit('confirm')" :disabled="isConfirming" :class="confirmBtnClass" class="flex-1 py-3 text-sm font-bold text-white rounded-xl transition-all shadow-md disabled:opacity-50 cursor-pointer">
          {{ isConfirming ? confirmingText : confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  show: Boolean,
  title: String,
  message: String,
  actionText: String,
  actionSide: String,
  price: [Number, String],
  amount: [Number, String],
  total: [Number, String],
  confirmText: String,
  confirmingText: String,
  isConfirming: Boolean,
  cancelText: { type: String, default: 'Cancel' }
});

defineEmits(['close', 'confirm']);

const confirmBtnClass = computed(() => {
  if (props.title === 'Cancel Order') {
    return 'bg-thatch hover:bg-thatch/90';
  }
  return props.actionSide === 'buy' ? 'bg-green-600 hover:bg-green-700' : 'bg-thatch hover:bg-thatch/90';
});
</script>

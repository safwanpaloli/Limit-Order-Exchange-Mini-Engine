<template>
  <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-3 pointer-events-none">
    <transition-group name="toast">
      <div v-for="toast in toasts" :key="toast.id" 
           class="pointer-events-auto flex items-center justify-between w-80 p-4 rounded-lg shadow-lg border transition-all duration-300 transform"
           :class="{
             'bg-white border-green-500/30 text-green-800': toast.type === 'success',
             'bg-white border-thatch/30 text-thatch': toast.type === 'error',
             'bg-white border-elephant/30 text-gunmetal': toast.type === 'info'
           }">
        
        <div class="flex items-center gap-3">
          <svg v-if="toast.type === 'success'" class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          <svg v-else-if="toast.type === 'error'" class="w-5 h-5 text-thatch" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          <span class="font-medium text-sm">{{ toast.message }}</span>
        </div>
        
        <button @click="removeToast(toast.id)" class="text-elephant hover:text-gunmetal transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useToast } from '../composables/useToast';

const { toasts, removeToast } = useToast();
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateX(30px);
}
.toast-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>

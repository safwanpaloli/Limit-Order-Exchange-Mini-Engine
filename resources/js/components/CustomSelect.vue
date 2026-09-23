<template>
  <div class="relative" ref="selectContainer">
    <!-- Select Button -->
    <button 
      type="button" 
      @click="isOpen = !isOpen"
      class="w-full flex items-center justify-between bg-white/80 border border-elephant/20 rounded-lg px-4 py-2 text-sm text-gunmetal focus:outline-none focus:ring-2 focus:ring-thatch shadow-sm transition-all cursor-pointer"
      :class="[buttonClass]"
    >
      <span class="block truncate font-medium">{{ selectedLabel }}</span>
      <svg 
        class="w-4 h-4 text-elephant transition-transform duration-200" 
        :class="{ 'rotate-180': isOpen }"
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95 -translate-y-2"
      enter-to-class="transform opacity-100 scale-100 translate-y-0"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100 translate-y-0"
      leave-to-class="transform opacity-0 scale-95 -translate-y-2"
    >
      <div 
        v-if="isOpen" 
        class="absolute z-50 mt-2 w-full min-w-[120px] rounded-lg bg-white shadow-lg border border-elephant/10 overflow-hidden py-1"
        :class="[dropdownPosition]"
      >
        <ul class="max-h-60 overflow-auto focus:outline-none scrollbar-thin">
          <li 
            v-for="option in options" 
            :key="option.value"
            @click="selectOption(option)"
            class="cursor-pointer select-none relative py-2.5 px-4 text-sm transition-colors flex items-center justify-between"
            :class="{
              'bg-thatch/10 text-gunmetal font-bold': option.value === modelValue,
              'text-gunmetal hover:bg-mushroom/30 hover:text-gunmetal': option.value !== modelValue
            }"
          >
            <span class="block truncate">{{ option.label }}</span>
            <svg 
              v-if="option.value === modelValue" 
              class="w-4 h-4 text-thatch" 
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number],
    required: true
  },
  options: {
    type: Array,
    required: true,
    // Expects array of objects: { value: '...', label: '...' }
  },
  buttonClass: {
    type: String,
    default: ''
  },
  dropdownPosition: {
    type: String,
    default: 'left-0' // Use 'right-0' for right alignment if needed
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const selectContainer = ref(null);

const selectedLabel = computed(() => {
  const selected = props.options.find(opt => opt.value === props.modelValue);
  return selected ? selected.label : 'Select...';
});

const selectOption = (option) => {
  emit('update:modelValue', option.value);
  isOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (selectContainer.value && !selectContainer.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
/* Optional: Custom scrollbar for dropdown */
.scrollbar-thin::-webkit-scrollbar {
  width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: #8f8681;
  border-radius: 10px;
}
</style>

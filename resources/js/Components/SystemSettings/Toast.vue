<template>
  <div 
    v-if="visible"
    class="fixed top-4 right-4 z-50 font-['Inter']"
  >
    <div 
      :class="[
        'rounded-lg px-4 py-3 text-sm font-medium text-white shadow-lg transition-all duration-300',
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
      ]"
    >
      <div class="flex items-center">
        <CheckCircleIcon v-if="type === 'success'" class="h-5 w-5 mr-2" />
        <XCircleIcon v-else class="h-5 w-5 mr-2" />
        {{ message }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { CheckCircleIcon, XCircleIcon } from 'lucide-vue-next';

const props = defineProps({
  message: {
    type: String,
    required: true
  },
  type: {
    type: String,
    default: 'success' // 'success' or 'error'
  },
  duration: {
    type: Number,
    default: 3000 // 3 seconds
  }
});

const visible = ref(false);

// Show toast when message changes
watch(() => props.message, (newMessage) => {
  if (newMessage) {
    visible.value = true;
    setTimeout(() => {
      visible.value = false;
    }, props.duration);
  }
});
</script>
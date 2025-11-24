<template>
  <form @submit.prevent="submitForm" class="space-y-6 font-['Inter']">
    <div class="grid grid-cols-1 gap-6">
      <div>
        <InputLabel for="name" value="Nama Pengaturan" class="text-gray-700 font-medium" />
        <TextInput
          id="name"
          type="text"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.name"
          :disabled="isEdit"
          required
        />
        <InputError class="mt-2" :message="form.errors.name" />
      </div>

      <div>
        <InputLabel for="value" value="Nilai" class="text-gray-700 font-medium" />
        <TextInput
          id="value"
          type="text"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.value"
          required
        />
        <InputError class="mt-2" :message="form.errors.value" />
      </div>

      <div>
        <InputLabel for="description" value="Deskripsi" class="text-gray-700 font-medium" />
        <textarea
          id="description"
          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#C62828] focus:ring-[#C62828]"
          v-model="form.description"
          rows="3"
        ></textarea>
        <InputError class="mt-2" :message="form.errors.description" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-4 pt-4">
      <SecondaryButton 
        @click="emit('cancel')" 
        type="button"
        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-all duration-300"
      >
        Batal
      </SecondaryButton>
      <PrimaryButton 
        :disabled="form.processing"
        class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300"
      >
        {{ isEdit ? 'Update' : 'Simpan' }}
      </PrimaryButton>
    </div>
  </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
  setting: {
    type: Object,
    default: null
  },
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['submit', 'cancel']);

// Initialize form
const form = useForm({
  name: props.setting?.name || '',
  value: props.setting?.value || '',
  description: props.setting?.description || ''
});

// Methods
const submitForm = () => {
  emit('submit', form);
};
</script>
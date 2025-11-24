<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50 font-['Inter']" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-xl bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
              <div>
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                  <SettingsIcon class="h-6 w-6 text-blue-600" />
                </div>
                <div class="mt-3 text-center sm:mt-5">
                  <DialogTitle as="h3" class="text-base font-semibold leading-6 text-gray-900">
                    Detail Pengaturan
                  </DialogTitle>
                  <div class="mt-4">
                    <div class="border-t border-gray-200 pt-4">
                      <dl class="grid grid-cols-1 gap-4">
                        <div class="border-b border-gray-100 pb-4">
                          <dt class="text-sm font-medium text-gray-500">Nama Pengaturan</dt>
                          <dd class="mt-1 text-sm text-gray-900">{{ setting?.name }}</dd>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                          <dt class="text-sm font-medium text-gray-500">Nilai</dt>
                          <dd class="mt-1 text-sm text-gray-900">{{ setting?.value }}</dd>
                        </div>
                        <div class="border-b border-gray-100 pb-4">
                          <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                          <dd class="mt-1 text-sm text-gray-900">{{ setting?.description || '-' }}</dd>
                        </div>
                        <div>
                          <dt class="text-sm font-medium text-gray-500">Tanggal Dibuat</dt>
                          <dd class="mt-1 text-sm text-gray-900">
                            {{ setting?.created_at ? new Date(setting.created_at).toLocaleDateString('id-ID') : '-' }}
                          </dd>
                        </div>
                      </dl>
                    </div>
                  </div>
                </div>
              </div>
              <div class="mt-5 sm:mt-6">
                <button
                  type="button"
                  class="inline-flex w-full justify-center rounded-md bg-[#C62828] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#b71c1c] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#C62828] transition-all duration-300"
                  @click="closeModal"
                >
                  Tutup
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { 
  Dialog, 
  DialogPanel, 
  DialogTitle, 
  TransitionChild, 
  TransitionRoot 
} from '@headlessui/vue';
import { SettingsIcon } from 'lucide-vue-next';

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  setting: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close']);

// Methods
const closeModal = () => {
  emit('close');
};
</script>
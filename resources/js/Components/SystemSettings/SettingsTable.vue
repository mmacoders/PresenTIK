<template>
  <div class="bg-white shadow-md rounded-2xl p-6 font-['Inter']">
    <!-- Table Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <h3 class="text-xl font-bold text-gray-800">Daftar Pengaturan</h3>
      
      <!-- Add Setting Button -->
      <button
        @click="emit('open-create-modal')"
        class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center"
      >
        <PlusCircleIcon class="h-5 w-5 mr-2" />
        Tambah Pengaturan
      </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Nama Pengaturan
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Nilai
            </th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Deskripsi
            </th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="setting in settings" 
            :key="setting.id"
            class="hover:bg-gray-50 transition-all duration-300"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ setting.name }}</div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-500 max-w-xs truncate" :title="setting.value">
                {{ setting.value }}
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-gray-500 max-w-xs truncate" :title="setting.description">
                {{ setting.description || '-' }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex justify-center space-x-2">
                <button 
                  @click="emit('view-detail', setting)"
                  class="text-blue-600 hover:text-blue-900 p-1 rounded transition-all duration-300"
                  title="Lihat Detail"
                >
                  <EyeIcon class="h-5 w-5" />
                </button>
                <button 
                  @click="emit('edit-setting', setting)"
                  class="text-[#C62828] hover:text-[#b71c1c] p-1 rounded transition-all duration-300"
                  title="Edit"
                >
                  <EditIcon class="h-5 w-5" />
                </button>
                <button 
                  @click="emit('delete-setting', setting)"
                  class="text-gray-600 hover:text-gray-800 p-1 rounded transition-all duration-300"
                  title="Hapus"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="settings.length === 0">
            <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
              Tidak ada data pengaturan
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { 
  PlusCircleIcon,
  EyeIcon,
  EditIcon,
  TrashIcon
} from 'lucide-vue-next';

const props = defineProps({
  settings: {
    type: Array,
    required: true
  }
});

const emit = defineEmits([
  'open-create-modal',
  'view-detail',
  'edit-setting',
  'delete-setting'
]);
</script>
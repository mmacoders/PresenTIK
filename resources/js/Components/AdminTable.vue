<template>
  <div class="bg-white shadow-md rounded-2xl p-6 font-['Inter']">
    <!-- Table Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
      <h3 class="text-xl font-bold text-gray-800">Daftar Admin</h3>
      
      <div class="flex flex-col sm:flex-row gap-3">
        <!-- Search Bar -->
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari admin..."
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#C62828] focus:border-[#C62828] w-full md:w-64"
            @input="handleSearch"
          />
        </div>
        
        <!-- Add Admin Button -->
        <button
          @click="emit('open-create-modal')"
          class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center"
        >
          <PlusCircleIcon class="h-5 w-5 mr-2" />
          Tambah Admin
        </button>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th 
              v-for="column in columns" 
              :key="column.key"
              scope="col" 
              class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer"
              @click="sort(column.key)"
            >
              <div class="flex items-center">
                {{ column.label }}
                <ArrowUpDownIcon 
                  v-if="column.sortable" 
                  class="ml-1 h-4 w-4" 
                  :class="{
                    'text-[#C62828]': sortKey === column.key,
                    'text-gray-400': sortKey !== column.key
                  }"
                />
              </div>
            </th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr 
            v-for="admin in paginatedAdmins"
            :key="admin.id"
            class="hover:bg-gray-50 transition-all duration-300"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div v-if="admin.profile_pict_url" class="w-10 h-10 rounded-full overflow-hidden mr-3">
                  <img :src="admin.profile_pict_url" :alt="admin.name" class="w-full h-full object-cover">
                </div>
                <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                  <UserIcon class="h-5 w-5 text-gray-500" />
                </div>
                <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ admin.email }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ admin.bidang?.nama_bidang || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span 
                :class="admin.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ admin.status === 'active' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button 
                  @click="handleEdit(admin)"
                  class="text-[#C62828] hover:text-[#b71c1c] p-1 rounded transition-all duration-300"
                  title="Edit"
                >
                  <EditIcon class="h-5 w-5" />
                </button>
                <button 
                  @click="handleTransfer(admin)"
                  class="text-blue-600 hover:text-blue-800 p-1 rounded transition-all duration-300"
                  title="Pindah Bidang"
                >
                  <ArrowRightLeftIcon class="h-5 w-5" />
                </button>
                <button 
                  @click="handleToggleStatus(admin)"
                  :class="admin.status === 'active' ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800'"
                  class="p-1 rounded transition-all duration-300"
                  :title="admin.status === 'active' ? 'Nonaktifkan' : 'Aktifkan'"
                >
                  <PowerIcon class="h-5 w-5" />
                </button>
                <button 
                  @click="handleDelete(admin)"
                  class="text-gray-600 hover:text-gray-800 p-1 rounded transition-all duration-300"
                  title="Hapus"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="paginatedAdmins.length === 0">
            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
              Tidak ada data admin
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <Pagination 
      :current-page="currentPage"
      :total-pages="totalPages"
      :visible-pages="visiblePages"
      :start-index="startIndex"
      :per-page="adminsPerPage"
      :total-items="filteredAdmins.length"
      @prev-page="prevPage"
      @next-page="nextPage"
      @go-to-page="goToPage"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import {
  SearchIcon,
  PlusCircleIcon,
  ArrowUpDownIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  EditIcon,
  ArrowRightLeftIcon,
  PowerIcon,
  TrashIcon,
  UserIcon
} from 'lucide-vue-next';

const props = defineProps({
  admins: {
    type: Array,
    required: true
  }
});

const emit = defineEmits([
  'open-create-modal',
  'edit-admin',
  'transfer-admin',
  'toggle-status',
  'delete-admin'
]);

// State
const searchQuery = ref('');
const sortKey = ref('name');
const sortOrder = ref('asc');
const currentPage = ref(1);
const adminsPerPage = ref(10);

// Columns definition
const columns = [
  { key: 'name', label: 'Nama', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'bidang.nama_bidang', label: 'Bidang', sortable: true },
  { key: 'status', label: 'Status', sortable: true }
];

// Computed
const filteredAdmins = computed(() => {
  let result = [...props.admins];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(admin => 
      admin.name.toLowerCase().includes(query) || 
      admin.email.toLowerCase().includes(query) ||
      (admin.bidang?.nama_bidang && admin.bidang.nama_bidang.toLowerCase().includes(query))
    );
  }
  
  // Apply sorting
  result.sort((a, b) => {
    let aValue, bValue;
    
    // Handle nested properties
    if (sortKey.value.includes('.')) {
      const keys = sortKey.value.split('.');
      aValue = a[keys[0]]?.[keys[1]] || '';
      bValue = b[keys[0]]?.[keys[1]] || '';
    } else {
      aValue = a[sortKey.value] || '';
      bValue = b[sortKey.value] || '';
    }
    
    // Convert to string for comparison
    aValue = String(aValue);
    bValue = String(bValue);
    
    if (sortOrder.value === 'asc') {
      return aValue.localeCompare(bValue);
    } else {
      return bValue.localeCompare(aValue);
    }
  });
  
  return result;
});

const totalPages = computed(() => {
  return Math.ceil(filteredAdmins.value.length / adminsPerPage.value);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * adminsPerPage.value;
});

const paginatedAdmins = computed(() => {
  const start = startIndex.value;
  const end = start + adminsPerPage.value;
  return filteredAdmins.value.slice(start, end);
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisiblePages = 5;
  
  if (totalPages.value <= maxVisiblePages) {
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i);
    }
  } else {
    const start = Math.max(1, currentPage.value - Math.floor(maxVisiblePages / 2));
    const end = Math.min(totalPages.value, start + maxVisiblePages - 1);
    
    if (start > 1) {
      pages.push(1);
      if (start > 2) {
        pages.push('...');
      }
    }
    
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    
    if (end < totalPages.value) {
      if (end < totalPages.value - 1) {
        pages.push('...');
      }
      pages.push(totalPages.value);
    }
  }
  
  return pages;
});

// Methods
const handleSearch = () => {
  currentPage.value = 1;
};

const sort = (key) => {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const goToPage = (page) => {
  if (page !== '...') {
    currentPage.value = page;
  }
};

// Handler methods for Admin actions
const handleEdit = (admin) => {
  emit('edit-admin', admin);
};

const handleTransfer = (admin) => {
  emit('transfer-admin', admin);
};

const handleToggleStatus = (admin) => {
  emit('toggle-status', admin);
};

const handleDelete = (admin) => {
  emit('delete-admin', admin);
};

// Reset pagination when admins change
watch(() => props.admins, () => {
  currentPage.value = 1;
});
</script>
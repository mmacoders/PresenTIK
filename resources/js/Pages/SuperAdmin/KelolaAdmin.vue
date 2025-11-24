<template>
  <div class="flex h-screen bg-[#F5F6FA] font-sans">
    <!-- Sidebar -->
    <SuperAdminSidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <SuperAdminHeader 
        title="Kelola Admin"
        :user-profile-pic="$page.props.auth.user.profile_pict_url"
        @toggle-sidebar="toggleSidebar"
      />

      <main class="flex-1 overflow-y-auto py-7">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
            {{ $page.props.flash.success }}
          </div>

          <!-- Page Title + Search + Button -->
          <div class="mb-6 flex flex-col gap-4">
            <!-- Kiri: Title + Description -->
            <div>
              <h2 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2">
                <UsersIcon class="text-red-600 w-6 h-6 md:w-8 md:h-8" />
                Manajemen Admin
              </h2>
              <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">
                Kelola data admin, lihat detail, dan atur status keaktifan admin dalam sistem.
              </p>
            </div>

            <!-- Kanan: Search + Button -->
            <div class="flex flex-col md:flex-row gap-3">
              <!-- Search Bar -->
              <div class="relative w-full md:w-auto md:flex-1">
                <SearchIcon
                  class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari admin..."
                  class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full"
                  @input="handleSearch"
                />
              </div>

              <!-- Add Admin Button -->
              <button
                @click="showCreateModal = true"
                class="w-full md:w-auto px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center"
                title="Tambah Admin"
              >
                <PlusCircleIcon class="h-5 w-5 mr-2" />
                Tambah Admin
              </button>
            </div>
          </div>


          <!-- Admin List Card -->
          <div class="bg-white shadow-md rounded-2xl overflow-hidden">
            <!-- Admin Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-[#ad1f32] border-b border-gray-400">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider whitespace-nowrap">
                      Nama
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                      Email
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                      Jabatan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
                      Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-semibold text-white uppercase tracking-wider whitespace-nowrap">
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
                        <div v-if="admin.profile_pict_url" class="w-10 h-10 rounded-full overflow-hidden mr-3 flex-shrink-0">
                          <img :src="admin.profile_pict_url" :alt="admin.name" class="w-full h-full object-cover">
                        </div>
                        <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3 flex-shrink-0">
                          <UserIcon class="h-5 w-5 text-gray-500" />
                        </div>
                        <div class="text-sm font-medium text-gray-900">{{ admin.name }}</div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm ">{{ admin.email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm ">{{ admin.jabatan || '-' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span 
                        :class="admin.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                        class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                      >
                        {{ admin.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center align-middle">
                      <div class="inline-flex justify-center items-center space-x-3">
                        <button 
                          @click="viewDetail(admin)"
                          class="text-blue-600 hover:text-blue-800 p-1 rounded transition-all duration-300"
                          title="Detail"
                        >
                          <EyeIcon class="h-5 w-5" />
                        </button>
                        <button 
                          @click="editAdmin(admin)"
                          class="text-[#C62828] hover:text-[#b71c1c] p-1 rounded transition-all duration-300"
                          title="Edit"
                        >
                          <EditIcon class="h-5 w-5" />
                        </button>
                        <button 
                          @click="openDeleteModal(admin)"
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
                      Tidak ada data admin tersedia
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
        </div>
      </main>

      <!-- Create/Edit Modal -->
      <Modal :show="showCreateModal || showEditModal" @close="closeModal">
        <div class="p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showEditModal ? 'Edit Admin' : 'Tambah Admin' }}
          </h3>

          <AdminForm 
            :admin="editingAdmin"
            :is-edit="showEditModal"
            @submit="handleSubmit"
            @cancel="closeModal"
          />
        </div>
      </Modal>

      <!-- Detail View Modal (Following Pegawai style) -->
      <div v-if="showDetailModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-gray-900">Detail Admin</h3>
              <button @click="closeDetailModal" class="text-gray-500 hover:text-gray-700">
                <XIcon class="h-6 w-6" />
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="md:col-span-1">
                <div class="bg-gray-100 rounded-2xl p-6 flex flex-col items-center">
                  <img 
                    :src="detailAdmin.profile_pict_url || '/images/profile.png'" 
                    :alt="detailAdmin.name"
                    class="w-32 h-32 rounded-full ring-4 ring-red-500 object-cover mb-4"
                    @error="handleImageError"
                  />
                  <h4 class="text-lg font-bold text-gray-900">{{ detailAdmin.name }}</h4>
                  <p class="text-gray-600">{{ detailAdmin.jabatan }}</p>
                  <div class="mt-3">
                    <span 
                      v-if="detailAdmin.status === 'active'"
                      class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"
                    >
                      Aktif
                    </span>
                    <span 
                      v-else
                      class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800"
                    >
                      Nonaktif
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="md:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                  <h5 class="text-lg font-bold text-gray-900 mb-4">Informasi Pribadi</h5>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <p class="text-sm text-gray-500">Nama Lengkap</p>
                      <p class="font-medium">{{ detailAdmin.name }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Email</p>
                      <p class="font-medium">{{ detailAdmin.email }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Jabatan</p>
                      <p class="font-medium">{{ detailAdmin.jabatan }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Status</p>
                      <p class="font-medium">
                        <span 
                          v-if="detailAdmin.status === 'active'"
                          class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                        >
                          Aktif
                        </span>
                        <span 
                          v-else
                          class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800"
                        >
                          Nonaktif
                        </span>
                      </p>
                    </div>
                  </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
                  <button
                    @click="editAdmin(detailAdmin)"
                    class="flex-1 px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-colors duration-200 flex items-center justify-center"
                  >
                    <EditIcon class="h-4 w-4 mr-2" />
                    Edit
                  </button>
                  <button
                    @click="closeDetailModal"
                    class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                  >
                    Tutup
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :open="showDeleteModal"
        title="Hapus Admin"
        message="Apakah Anda yakin ingin menghapus admin ini? Tindakan ini tidak dapat dibatalkan."
        type="danger"
        confirm-text="Hapus"
        cancel-text="Batal"
        @close="showDeleteModal = false"
        @confirm="confirmDelete"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import AdminForm from '@/Components/AdminForm.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';
import Pagination from '@/Components/Pagination.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {
  SearchIcon,
  PlusCircleIcon,
  EditIcon,
  EyeIcon,
  TrashIcon,
  UsersIcon,
  UserIcon,
  XIcon,
  LockIcon
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

// Get page props
const page = usePage();
const props = defineProps({
  admins: Object,
});

// State
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showDetailModal = ref(false);

const editingAdmin = ref(null);
const detailAdmin = ref({});
const adminToDelete = ref(null);

// Search and pagination state
const searchQuery = ref('');
const currentPage = ref(1);
const adminsPerPage = ref(10);

const adminForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  jabatan: '',
  status: 'active',
});

// Computed properties
const filteredAdmins = computed(() => {
  let result = [...props.admins.data];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(admin => 
      admin.name.toLowerCase().includes(query) || 
      admin.email.toLowerCase().includes(query) ||
      (admin.jabatan && admin.jabatan.toLowerCase().includes(query))
    );
  }
  
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
const handleSearch = debounce(() => {
  currentPage.value = 1;
}, 300);

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

const createAdmin = (form) => {
  form.post(route('superadmin.admin.store'), {
    onSuccess: () => {
      closeModal();
      form.reset();
    },
  });
};

const updateAdmin = (form) => {
  form.patch(route('superadmin.admin.update', editingAdmin.value.id), {
    onSuccess: () => {
      closeModal();
    },
  });
};

const handleSubmit = (form) => {
  if (showEditModal.value) {
    updateAdmin(form);
  } else {
    createAdmin(form);
  }
};

const editAdmin = (admin) => {
  editingAdmin.value = admin;
  showEditModal.value = true;
};

const viewDetail = (admin) => {
  detailAdmin.value = { ...admin };
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  detailAdmin.value = {};
};

const openDeleteModal = (admin) => {
  adminToDelete.value = admin;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (adminToDelete.value) {
    router.delete(route('superadmin.admin.destroy', adminToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        adminToDelete.value = null;
      }
    });
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showDeleteModal.value = false;
  editingAdmin.value = null;
  adminToDelete.value = null;
  adminForm.reset();
};

const handleImageError = (event) => {
  event.target.src = '/images/profile.png';
};

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

const logout = () => {
  router.post(route('logout'));
};

// Reset pagination when admins or search query change
watch([() => props.admins, searchQuery], () => {
  currentPage.value = 1;
});
</script>
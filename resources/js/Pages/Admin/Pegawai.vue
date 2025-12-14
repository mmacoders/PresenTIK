<template>
  <AdminLayout page-title="Data Pegawai" mobile-page-title="Pegawai">
    <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
      <!-- Success/Error Messages -->
      <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
        {{ $page.props.flash.error }}
      </div>

      <!-- Page Title + Search + Filter -->
      <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <!-- Kiri: Title + Description -->
        <div>
          <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <UsersIcon class="text-red-600" />
            Manajemen Pegawai
          </h2>
          <p class="text-gray-600 mt-2">
            Kelola data pegawai dan lihat detail informasi pegawai.
          </p>
        </div>
        
        <!-- Kanan: Search + Filter -->
        <div class="flex flex-col sm:flex-row gap-3">
          <!-- Search Bar -->
          <div class="relative">
            <SearchIcon
              class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
            />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari pegawai..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500 block w-full sm:text-sm"
            />
          </div>
          
          <!-- Add Button -->
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <PlusCircleIcon class="w-4 h-4 mr-2" />
            Tambah Pegawai
          </button>
        </div>
      </div>

      <!-- Pegawai List Card -->
      <div class="bg-white shadow-md rounded-2xl">
        <!-- Pegawai Table -->
        <div class="overflow-x-auto rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-[#ad1f32] border-b border-gray-400">
              <tr>
                <th scope="col" class="px-10 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                  Nama
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                  Email
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                  Jabatan
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr 
                v-for="user in paginatedUsers"
                :key="user.id"
                class="hover:bg-gray-50 transition-all duration-300"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div v-if="user.profile_pict_url" class="w-10 h-10 rounded-full overflow-hidden mr-3">
                      <img :src="user.profile_pict_url" :alt="user.name" class="w-full h-full object-cover">
                    </div>
                    <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                      <UserIcon class="h-5 w-5 text-gray-500" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.jabatan || '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm">{{ user.jabatan || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="user.status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                    class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  >
                    {{ user.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center align-middle">
                  <div class="inline-flex justify-center items-center">
                    <button 
                      @click="viewDetail(user)"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded transition-all duration-300"
                      title="Detail"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </button>
                    <button 
                      @click="editUser(user)"
                      class="text-yellow-600 hover:text-yellow-800 p-1 rounded transition-all duration-300 ml-1"
                      title="Edit"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>
                    <button 
                      @click="openResetPasswordModal(user)"
                      class="text-orange-600 hover:text-orange-800 p-1 rounded transition-all duration-300 ml-1"
                      title="Reset Password"
                    >
                      <KeyIcon class="h-5 w-5" />
                    </button>
                    <button 
                      @click="openDeleteModal(user)"
                      class="text-red-600 hover:text-red-800 p-1 rounded transition-all duration-300 ml-1"
                      title="Hapus"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedUsers.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                  Tidak ada data pegawai tersedia
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
          :per-page="usersPerPage"
          :total-items="filteredUsers.length"
          @prev-page="prevPage"
          @next-page="nextPage"
          @go-to-page="goToPage"
        />
      </div>

      <!-- Detail View Modal -->
      <div v-if="showDetailModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-xl font-bold text-gray-900">Detail Pegawai</h3>
              <button @click="closeDetailModal" class="text-gray-500 hover:text-gray-700">
                <XIcon class="h-6 w-6" />
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="md:col-span-1">
                <div class="bg-gray-100 rounded-2xl p-6 flex flex-col items-center">
                  <img 
                    :src="detailUser.profile_pict_url || '/images/profile.png'" 
                    :alt="detailUser.name"
                    class="w-32 h-32 rounded-full ring-4 ring-red-500 object-cover mb-4"
                    @error="handleImageError"
                  />
                  <h4 class="text-lg font-bold text-gray-900">{{ detailUser.name }}</h4>
                  <p class="text-gray-600">{{ detailUser.jabatan }}</p>
                  <div class="mt-3">
                    <span 
                      v-if="detailUser.status === 'aktif'"
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
                      <p class="font-medium">{{ detailUser.name }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Pangkat</p>
                      <p class="font-medium">{{ detailUser.pangkat }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">NRP</p>
                      <p class="font-medium">{{ detailUser.nrp }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">NIP</p>
                      <p class="font-medium">{{ detailUser.nip || '-' }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Email</p>
                      <p class="font-medium">{{ detailUser.email }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">No. HP</p>
                      <p class="font-medium">{{ detailUser.no_hp || '-' }}</p>
                    </div>
                    
                    <div>
                      <p class="text-sm text-gray-500">Jabatan</p>
                      <p class="font-medium">{{ detailUser.jabatan }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="mt-6 flex space-x-3">
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

      <!-- Create/Edit Modal -->
      <Modal :show="showCreateModal || showEditModal" @close="closeModal">
        <div class="p-6">
          <!-- Header -->
          <div class="mb-6">
            <div class="flex justify-between items-start mb-3">
              <div>
                <h3 class="text-xl font-bold text-gray-900">
                  {{ showEditModal ? 'Edit Data Pegawai' : 'Tambah Pegawai Baru' }}
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                  {{ showEditModal ? 'Perbarui informasi data pegawai' : 'Lengkapi formulir di bawah untuk menambahkan pegawai baru' }}
                </p>
              </div>
              <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                <XIcon class="h-6 w-6" />
              </button>
            </div>
            <div class="border-b border-gray-200"></div>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Form Fields in 2 Columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Column 1 (Left) -->
              <div class="space-y-4">
                <!-- Nama Lengkap -->
                <div>
                  <InputLabel for="name" value="Nama Lengkap" />
                  <TextInput
                    id="name"
                    v-model="userForm.name"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Masukkan nama lengkap"
                    required
                  />
                  <InputError :message="userForm.errors.name" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                  <InputLabel for="email" value="Email" />
                  <TextInput
                    id="email"
                    v-model="userForm.email"
                    type="email"
                    class="mt-1 block w-full"
                    placeholder="email@example.com"
                  />
                  <InputError :message="userForm.errors.email" class="mt-2" />
                </div>

                <!-- No HP -->
                <div>
                  <InputLabel for="no_hp" value="Nomor HP" />
                  <TextInput
                    id="no_hp"
                    v-model="userForm.no_hp"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="08xxxxxxxxxx"
                  />
                  <InputError :message="userForm.errors.no_hp" class="mt-2" />
                </div>

                <!-- Pangkat -->
                <div>
                  <InputLabel for="pangkat" value="Pangkat" />
                  <TextInput
                    id="pangkat"
                    v-model="userForm.pangkat"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Masukkan pangkat"
                  />
                  <InputError :message="userForm.errors.pangkat" class="mt-2" />
                </div>
              </div>

              <!-- Column 2 (Right) -->
              <div class="space-y-4">
                <!-- NRP -->
                <div>
                  <InputLabel for="nrp" value="NRP" />
                  <TextInput
                    id="nrp"
                    v-model="userForm.nrp"
                    type="text"
                    class="mt-1 block w-full disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed"
                    placeholder="Masukkan NRP"
                    :disabled="showEditModal"
                  />
                  <InputError :message="userForm.errors.nrp" class="mt-2" />
                </div>

                <div>
                  <InputLabel for="nip" value="NIP" />
                  <TextInput
                    id="nip"
                    v-model="userForm.nip"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Masukkan NIP"
                  />
                  <InputError :message="userForm.errors.nip" class="mt-2" />
                </div>

                <!-- Jabatan -->
                <div>
                  <InputLabel for="jabatan" value="Jabatan" />
                  <TextInput
                    id="jabatan"
                    v-model="userForm.jabatan"
                    type="text"
                    class="mt-1 block w-full"
                    placeholder="Masukkan jabatan"
                  />
                  <InputError :message="userForm.errors.jabatan" class="mt-2" />
                </div>

                <!-- Status -->
                <div>
                  <InputLabel for="status" value="Status" />
                  <select
                    id="status"
                    v-model="userForm.status"
                    class="mt-1 block w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm"
                  >
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                  </select>
                  <InputError :message="userForm.errors.status" class="mt-2" />
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
              <SecondaryButton type="button" @click="closeModal">
                Batal
              </SecondaryButton>
              <PrimaryButton type="submit" :disabled="userForm.processing">
                {{ showEditModal ? 'Update' : 'Simpan' }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :show="showDeleteModal"
        @close="showDeleteModal = false"
        @confirm="confirmDelete"
        title="Hapus Pegawai"
        message="Apakah Anda yakin ingin menghapus pegawai ini? Tindakan ini tidak dapat dibatalkan."
        confirmText="Hapus"
        cancelText="Batal"
      />

      <!-- Reset Password Confirmation Modal -->
      <ConfirmModal
        :show="showResetPasswordModal"
        @close="showResetPasswordModal = false"
        @confirm="confirmResetPassword"
        title="Reset Password Pegawai"
        message="Apakah Anda yakin ingin mereset password pegawai ini menjadi default ('password')? Tindakan ini tidak dapat dibatalkan."
        confirmText="Reset Password"
        cancelText="Batal"
      />
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { ref, computed, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {
  SearchIcon,
  UsersIcon,
  EyeIcon,
  UserIcon,
  XIcon,
  PlusCircleIcon,
  KeyIcon,
  PencilIcon,
  TrashIcon
} from 'lucide-vue-next';
import debounce from 'lodash/debounce';

const props = defineProps({
  users: Object,
});

// State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showDetailModal = ref(false);
const editingUser = ref(null);
const detailUser = ref({});
const userToDelete = ref(null);
const showResetPasswordModal = ref(false);
const userToResetPassword = ref(null);

// Initialize form
const userForm = useForm({
  name: '',
  email: '',
  no_hp: '',
  pangkat: '',
  nip: '',
  nrp: '',
  jabatan: '',
  status: 'aktif',
});

// Search and filter state
const searchQuery = ref('');

const currentPage = ref(1);
const usersPerPage = ref(10);

// Computed properties
const filteredUsers = computed(() => {
  let result = [...props.users.data];
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter(user => 
      user.name.toLowerCase().includes(query) || 
      user.email.toLowerCase().includes(query) ||
      (user.jabatan && user.jabatan.toLowerCase().includes(query))
    );
  }
  
  return result;
});

const totalPages = computed(() => {
  return Math.ceil(filteredUsers.value.length / usersPerPage.value);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * usersPerPage.value;
});

const paginatedUsers = computed(() => {
  const start = startIndex.value;
  const end = start + usersPerPage.value;
  return filteredUsers.value.slice(start, end);
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

const viewDetail = (user) => {
  detailUser.value = { ...user };
  showDetailModal.value = true;
};

const closeDetailModal = () => {
  showDetailModal.value = false;
  detailUser.value = {};
};

const handleImageError = (event) => {
  event.target.src = '/images/profile.png';
};

const createUser = () => {
  userForm.post(route('admin.pegawai.store'), {
    onSuccess: () => {
      closeModal();
      userForm.reset();
    },
  });
};

const updateUser = () => {
  userForm.patch(route('admin.pegawai.update', editingUser.value.id), {
    onSuccess: () => {
      closeModal();
    },
  });
};

const handleSubmit = () => {
  if (showEditModal.value) {
    updateUser();
  } else {
    createUser();
  }
};

const editUser = (user) => {
  editingUser.value = user;
  userForm.name = user.name;
  userForm.email = user.email;
  userForm.pangkat = user.pangkat;
  userForm.nrp = user.nrp;
  userForm.nip = user.nip;
  userForm.no_hp = user.no_hp;
  userForm.jabatan = user.jabatan;
  userForm.status = user.status;
  showEditModal.value = true;
};

const openDeleteModal = (user) => {
  userToDelete.value = user;
  showDeleteModal.value = true;
};

const confirmDelete = () => {
  if (userToDelete.value) {
    router.delete(route('admin.pegawai.destroy', userToDelete.value.id), {
      onSuccess: () => {
        showDeleteModal.value = false;
        userToDelete.value = null;
      }
    });
  }
};

const openResetPasswordModal = (user) => {
  userToResetPassword.value = user;
  showResetPasswordModal.value = true;
};

const confirmResetPassword = () => {
  if (userToResetPassword.value) {
    router.patch(route('admin.pegawai.reset-password', userToResetPassword.value.id), {}, {
      onSuccess: () => {
        showResetPasswordModal.value = false;
        userToResetPassword.value = null;
      }
    });
  }
};

const closeModal = () => {
  showCreateModal.value = false;
  showEditModal.value = false;
  showDeleteModal.value = false;
  showResetPasswordModal.value = false;
  editingUser.value = null;
  userToDelete.value = null;
  userToResetPassword.value = null;
  userForm.reset();
};

// Reset pagination when users or search query change
watch([() => props.users, searchQuery], () => {
  currentPage.value = 1;
});
</script>
<template>
  <div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Sidebar -->
    <SuperAdminSidebar 
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content -->
    <div class="flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <SuperAdminHeader 
        title="Perbarui Profil"
        mobile-title="Profil"
        :user-profile-pic="user.profile_pict"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-16 p-4 sm:p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Page Title -->
          <div class="mb-8">
            <div class="flex items-center mb-3">
              <UserCircleIcon class="h-7 w-7 text-[#dc2626] mr-3" />
              <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Perbarui Profil</h1>
            </div>
            <p class="text-gray-600">Ubah informasi pribadi dan foto profil Anda di sini.</p>
          </div>

          <div class="space-y-8">
            <!-- Profile Picture Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
              <h2 class="text-xl font-semibold text-gray-900 mb-5">Foto Profil</h2>
              <div class="flex flex-col sm:flex-row items-center gap-8">
                <div class="relative">
                  <div v-if="profilePreview" class="w-36 h-36 rounded-full overflow-hidden ring-4 ring-[#dc2626] shadow-lg">
                    <img :src="profilePreview" alt="Profile Preview" class="w-full h-full object-cover">
                  </div>
                  <div v-else class="w-36 h-36 rounded-full bg-gray-200 flex items-center justify-center ring-4 ring-[#dc2626] shadow-lg">
                    <UserCircleIcon class="h-20 w-20 text-gray-400" />
                  </div>
                </div>
                
                <div class="text-center sm:text-left">
                  <label class="bg-[#dc2626] hover:bg-[#b91c1c] text-white rounded-xl px-5 py-3 text-sm font-medium transition-all cursor-pointer inline-flex items-center shadow-md transform hover:scale-[1.02]">
                    <UploadIcon class="w-5 h-5 mr-2" />
                    <span>Ubah Foto</span>
                    <input 
                      type="file" 
                      class="hidden" 
                      @change="handleProfilePictureChange"
                      accept="image/jpeg,image/png,image/jpg"
                    >
                  </label>
                  <p class="text-xs text-gray-500 mt-3">Format JPG/PNG, maks. 2MB</p>
                </div>
              </div>
            </div>

            <!-- Profile Information Form -->
            <form @submit.prevent="submitProfile" class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
              <h2 class="text-xl font-semibold text-gray-900 mb-5">Informasi Pribadi</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Nama Lengkap</label>
                  <input
                    type="text"
                    v-model="profileForm.name"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-colors duration-200 shadow-sm"
                    :class="{ 'border-red-500': profileForm.errors.name }"
                  >
                  <div v-if="profileForm.errors.name" class="text-red-500 text-sm mt-1">{{ profileForm.errors.name }}</div>
                </div>

                <!-- NIP/NRP (Disabled) -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">NIP / NRP</label>
                  <input
                    type="text"
                    :value="profileForm.nip || profileForm.nrp"
                    disabled
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-gray-100 cursor-not-allowed shadow-sm"
                  >
                </div>

                <!-- Email -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Email</label>
                  <input
                    type="email"
                    v-model="profileForm.email"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-colors duration-200 shadow-sm"
                    :class="{ 'border-red-500': profileForm.errors.email }"
                  >
                  <div v-if="profileForm.errors.email" class="text-red-500 text-sm mt-1">{{ profileForm.errors.email }}</div>
                </div>

                <!-- Phone -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">No. HP</label>
                  <input
                    type="text"
                    v-model="profileForm.no_hp"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-colors duration-200 shadow-sm"
                    :class="{ 'border-red-500': profileForm.errors.no_hp }"
                  >
                  <div v-if="profileForm.errors.no_hp" class="text-red-500 text-sm mt-1">{{ profileForm.errors.no_hp }}</div>
                </div>

                <!-- Position -->
                <div>
                  <label class="text-sm font-medium text-gray-700 block mb-2">Jabatan</label>
                  <input
                    type="text"
                    v-model="profileForm.jabatan"
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-colors duration-200 shadow-sm"
                    :class="{ 'border-red-500': profileForm.errors.jabatan }"
                  >
                  <div v-if="profileForm.errors.jabatan" class="text-red-500 text-sm mt-1">{{ profileForm.errors.jabatan }}</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row justify-end gap-4 pt-8">
                <button
                  type="button"
                  @click="cancelChanges"
                  class="px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors duration-200 font-medium"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="px-5 py-2.5 bg-[#dc2626] text-white rounded-xl hover:bg-[#b91c1c] transition-colors duration-200 flex items-center justify-center font-medium shadow-md transform hover:scale-[1.02]"
                  :class="{ 'opacity-60 cursor-not-allowed': isSubmitting }"
                >
                  <Loader2Icon v-if="isSubmitting" class="w-5 h-5 mr-2 animate-spin" />
                  <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import { UserCircleIcon, UploadIcon, Loader2Icon } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    user: Object,
    bidangs: Array,
});

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

// Store original data for comparison
const originalData = {
    name: props.user.name || '',
    email: props.user.email || '',
    no_hp: props.user.no_hp || '',
    jabatan: props.user.jabatan || '',
    // profile_pict: null,
    nip: props.user.nip || '',
    nrp: props.user.nrp || '',
    pangkat: props.user.pangkat || '',
};

const profileForm = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    no_hp: props.user.no_hp || '',
    jabatan: props.user.jabatan || '',
    profile_pict: null,
    nip: props.user.nip || '',
    nrp: props.user.nrp || '',
    pangkat: props.user.pangkat || '',
});

const profilePreview = ref(props.user.profile_pict_url || null);
const isSubmitting = ref(false);

const handleProfilePictureChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!validTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'File tidak valid',
                text: 'Hanya file JPG/PNG yang diperbolehkan',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File terlalu besar',
                text: 'Ukuran file maksimal 2MB',
                confirmButtonColor: '#dc2626'
            });
            return;
        }

        profileForm.profile_pict = file;
        // Create preview
        profilePreview.value = URL.createObjectURL(file);
        // Removed auto-submit logic - user must click "Simpan Perubahan" manually
    }
};

const submitProfile = () => {
    // Check if no changes were made
    const currentData = {
        name: profileForm.name,
        email: profileForm.email,
        no_hp: profileForm.no_hp || '',
        jabatan: profileForm.jabatan || '',
        nip: profileForm.nip || '',
        nrp: profileForm.nrp || '',
        pangkat: profileForm.pangkat || '',
    };
    
    if (JSON.stringify(currentData) === JSON.stringify(originalData) && !profileForm.profile_pict) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak ada perubahan',
            text: 'Tidak ada perubahan untuk disimpan.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }
    
    isSubmitting.value = true;
    
    // Use post method with forceFormData to ensure all fields are properly sent
    profileForm.post(route('superadmin.profil.update'), {
        forceFormData: true,
        onSuccess: () => {
            isSubmitting.value = false;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Profil berhasil diperbarui!',
                confirmButtonColor: '#dc2626'
            });
            // Refresh user data
            router.reload({ only: ['user'] });
        },
        onError: () => {
            isSubmitting.value = false;
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan saat memperbarui profil',
                confirmButtonColor: '#dc2626'
            });
        }
    });
};

const cancelChanges = () => {
    // Reset form to original values
    profileForm.name = props.user.name || '';
    profileForm.email = props.user.email || '';
    profileForm.no_hp = props.user.no_hp || '';
    profileForm.jabatan = props.user.jabatan || '';
    profileForm.nip = props.user.nip || '';
    profileForm.nrp = props.user.nrp || '';
    profileForm.pangkat = props.user.pangkat || '';
    profileForm.profile_pict = null; // Also reset profile picture
    profilePreview.value = props.user.profile_pict_url || null;
};

// Clean up object URLs to prevent memory leaks
window.addEventListener('beforeunload', () => {
  if (profilePreview.value && profilePreview.value.startsWith('blob:')) {
    URL.revokeObjectURL(profilePreview.value);
  }
});
</script>
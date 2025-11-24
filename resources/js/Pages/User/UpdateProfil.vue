<template>
  <div class="min-h-screen bg-gray-50 overflow-x-hidden">
    <!-- Sidebar -->
    <Sidebar 
      ref="sidebarRef"
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content Wrapper -->
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header 
        :user="user" 
        :sidebar-collapsed="sidebarCollapsed"
        current-page="Profil" 
        mobile-breadcrumb="Profil"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main>
        <!-- Enhanced Header Section -->
        <div class="border-gray-200 px-6 py-8 mb-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center mb-2">
                    <UserCircleIcon class="h-8 w-8 text-[#dc2626] mr-3" />
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Perbarui Profil</h1>
                </div>
                <p class="text-gray-500 text-lg ml-11">Kelola informasi pribadi dan keamanan akun Anda.</p>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 pb-12">
            <form @submit.prevent="submitProfile" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Left Column: Profile Picture -->
                <div class="md:col-span-1">
                    <div class="bg-gradient-to-br from-red-100 to-blue-100 rounded-2xl shadow-sm border border-red-100 p-8 text-center sticky top-24">
                        <div class="relative inline-block mb-6">
                            <div v-if="profilePreview" class="w-48 h-48 rounded-full overflow-hidden ring-4 ring-white shadow-xl mx-auto">
                                <img :src="profilePreview" alt="Profile Preview" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="w-48 h-48 rounded-full bg-white flex items-center justify-center ring-4 ring-white shadow-xl mx-auto">
                                <UserCircleIcon class="h-24 w-24 text-gray-300" />
                            </div>
                            
                            <label class="absolute bottom-2 right-2 bg-[#dc2626] hover:bg-[#b91c1c] text-white p-2.5 rounded-full shadow-lg cursor-pointer transition-transform hover:scale-110" title="Ubah Foto">
                                <UploadIcon class="w-5 h-5" />
                                <input 
                                    type="file" 
                                    class="hidden" 
                                    @change="handleProfilePictureChange"
                                    accept="image/jpeg,image/png,image/jpg"
                                >
                            </label>
                        </div>
                        
                        <h3 class="text-lg font-bold">{{ user.name }}</h3>
                        <p class="text-sm text-gray-900 mb-4">{{ user.nrp || user.nip || 'Personil' }}</p>
                        <p class="text-xs text-gray-900">Format JPG/PNG, maks. 2MB</p>
                    </div>
                </div>

                <!-- Right Column: Personal Info Form -->
                <div class="md:col-span-2">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                        <div class="flex items-center mb-6 pb-4 border-b border-gray-100">
                            <UserIcon class="w-5 h-5 text-[#dc2626] mr-2" />
                            <h2 class="text-xl font-bold text-gray-900">Informasi Pribadi</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Row 1 -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Nama Lengkap</label>
                                <input
                                    type="text"
                                    v-model="profileForm.name"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.name }"
                                >
                                <div v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</div>
                            </div>
                            
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">NIP / NRP</label>
                                <input
                                    type="text"
                                    :value="profileForm.nip || profileForm.nrp"
                                    disabled
                                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-500 cursor-not-allowed"
                                >
                            </div>

                            <!-- Row 2 -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Email</label>
                                <input
                                    type="email"
                                    v-model="profileForm.email"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.email }"
                                >
                                <div v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">No. HP</label>
                                <input
                                    type="text"
                                    v-model="profileForm.no_hp"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.no_hp }"
                                >
                                <div v-if="profileForm.errors.no_hp" class="text-red-500 text-xs mt-1">{{ profileForm.errors.no_hp }}</div>
                            </div>

                            <!-- Row 3 -->
                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Jabatan</label>
                                <input
                                    type="text"
                                    v-model="profileForm.jabatan"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.jabatan }"
                                >
                                <div v-if="profileForm.errors.jabatan" class="text-red-500 text-xs mt-1">{{ profileForm.errors.jabatan }}</div>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Password Baru</label>
                                <input
                                    type="password"
                                    v-model="profileForm.password"
                                    placeholder="Kosongkan jika tidak ingin mengubah"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.password }"
                                >
                                <div v-if="profileForm.errors.password" class="text-red-500 text-xs mt-1">{{ profileForm.errors.password }}</div>
                            </div>

                            <!-- Row 4 -->
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-gray-700 block mb-2">Konfirmasi Password</label>
                                <input
                                    type="password"
                                    v-model="profileForm.password_confirmation"
                                    placeholder="Ulangi password baru"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] focus:outline-none transition-all"
                                    :class="{ 'border-red-500': profileForm.errors.password_confirmation }"
                                >
                                <div v-if="profileForm.errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ profileForm.errors.password_confirmation }}</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-100">
                            <button
                                type="button"
                                @click="cancelChanges"
                                class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="isSubmitting"
                                class="px-6 py-2.5 bg-[#dc2626] text-white rounded-lg font-medium hover:bg-[#b91c1c] focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 shadow-md hover:shadow-lg transition-all flex items-center"
                                :class="{ 'opacity-70 cursor-not-allowed': isSubmitting }"
                            >
                                <Loader2Icon v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
                                <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { UserCircleIcon, UploadIcon, Loader2Icon, UserIcon } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    user: Object,
    bidangs: Array,
});

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const sidebarRef = ref(null);

const toggleSidebar = () => {
  if (window.innerWidth >= 768) {
    if (sidebarRef.value) {
      sidebarRef.value.toggleCollapse();
    }
  } else {
    sidebarOpen.value = !sidebarOpen.value;
  }
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
    password: '',
    password_confirmation: '',
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
    
    if (JSON.stringify(currentData) === JSON.stringify(originalData) && !profileForm.profile_pict && !profileForm.password) {
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
    profileForm.post(route('user.profil.update'), {
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
    profileForm.password = '';
    profileForm.password_confirmation = '';
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
<template>
    <AdminLayout page-title="Edit Izin" mobile-page-title="Edit Izin">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Breadcrumb -->
            <nav class="flex mb-8" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link :href="route('admin.dashboard')" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-[#dc2626] transition-colors duration-200 px-3 py-1 rounded-lg hover:bg-gray-100">
                            <HomeIcon class="w-4 h-4 mr-2" />
                            Dashboard
                        </Link>
                    </li>
                    <li class="inline-flex items-center">
                        <div class="flex items-center">
                            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                            <Link :href="route('admin.izin')" class="ml-2 text-sm font-medium text-gray-700 hover:text-[#dc2626] transition-colors duration-200 px-3 py-1 rounded-lg hover:bg-gray-100 md:ml-3">
                                Izin & Cuti
                            </Link>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                            <span class="ml-2 text-sm font-medium text-gray-900 md:ml-3">Edit Izin</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <FileTextIcon class="text-red-600" />
                    Edit Permintaan Izin
                </h1>
                <p class="text-gray-600 mt-2">Ubah informasi permintaan izin pegawai.</p>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-2xl shadow-md p-6">
                <form @submit.prevent="submitForm">
                    <!-- User Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                        <select
                            v-model="form.user_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            :disabled="form.processing"
                            required
                        >
                            <option value="">Pilih Pegawai</option>
                            <option 
                                v-for="user in users" 
                                :key="user.id" 
                                :value="user.id"
                            >
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                            <input
                                v-model="form.tanggal_mulai"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                :disabled="form.processing"
                                required
                            />
                            <p v-if="form.errors.tanggal_mulai" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal_mulai }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai</label>
                            <input
                                v-model="form.tanggal_selesai"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                                :disabled="form.processing"
                                required
                            />
                            <p v-if="form.errors.tanggal_selesai" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal_selesai }}</p>
                        </div>
                    </div>
                    
                    <!-- Jenis Izin -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin</label>
                        <div class="flex space-x-6">
                            <label class="inline-flex items-center">
                                <input
                                    v-model="form.jenis_izin"
                                    type="radio"
                                    value="penuh"
                                    class="text-red-600 focus:ring-red-500"
                                    :disabled="form.processing"
                                />
                                <span class="ml-2">Izin Penuh</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input
                                    v-model="form.jenis_izin"
                                    type="radio"
                                    value="parsial"
                                    class="text-red-600 focus:ring-red-500"
                                    :disabled="form.processing"
                                />
                                <span class="ml-2">Izin Parsial</span>
                            </label>
                        </div>
                        <p v-if="form.errors.jenis_izin" class="mt-1 text-sm text-red-600">{{ form.errors.jenis_izin }}</p>
                    </div>
                    
                    <!-- File Upload for Full Leave Requests -->
                    <div v-if="form.jenis_izin === 'penuh'" class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dokumen Pendukung (Opsional)</label>
                        <input
                            type="file"
                            @change="handleFileChange"
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            :disabled="form.processing"
                        />
                        <p class="text-xs text-gray-500 mt-1">Unggah dokumen pendukung untuk izin cuti atau sakit (maks. 2MB)</p>
                        <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
                        
                        <!-- Show existing file if available -->
                        <div v-if="izin.file_path" class="mt-2 flex items-center">
                            <PaperclipIcon class="w-4 h-4 text-gray-500 mr-2" />
                            <a 
                                :href="route('admin.izin.download', izin.id)" 
                                target="_blank"
                                class="text-blue-600 hover:text-blue-800 underline"
                            >
                                Dokumen saat ini
                            </a>
                        </div>
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea
                            v-model="form.keterangan"
                            rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            :disabled="form.processing"
                            placeholder="Masukkan keterangan izin..."
                            required
                        ></textarea>
                        <p v-if="form.errors.keterangan" class="mt-1 text-sm text-red-600">{{ form.errors.keterangan }}</p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <Link 
                            :href="route('admin.izin')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                        >
                            Batal
                        </Link>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center"
                            :class="{ 'opacity-25': form.processing }" 
                            :disabled="form.processing"
                        >
                            <Spinner v-if="form.processing" class="w-4 h-4 mr-2" />
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { HomeIcon, ChevronRightIcon, FileTextIcon, PaperclipIcon } from 'lucide-vue-next';
import Spinner from '@/Components/Spinner.vue';

const props = defineProps({
    izin: Object,
    users: Array,
});

const form = useForm({
    user_id: props.izin.user_id,
    tanggal_mulai: props.izin.tanggal_mulai,
    tanggal_selesai: props.izin.tanggal_selesai,
    jenis_izin: props.izin.jenis_izin,
    keterangan: props.izin.keterangan,
    file: null, // Add file field
});

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type and size
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        const maxSize = 2 * 1024 * 1024; // 2MB in bytes
        
        if (!allowedTypes.includes(file.type)) {
            alert('File harus berupa PDF, JPG, JPEG, atau PNG.');
            event.target.value = ''; // Clear the input
            return;
        }
        
        if (file.size > maxSize) {
            alert('Ukuran file maksimal 2MB.');
            event.target.value = ''; // Clear the input
            return;
        }
        
        form.file = file;
    }
};

const submitForm = () => {
    // Create FormData for file upload
    const formData = new FormData();
    formData.append('_method', 'PATCH'); // Laravel expects PATCH method
    formData.append('user_id', form.user_id);
    formData.append('tanggal_mulai', form.tanggal_mulai);
    formData.append('tanggal_selesai', form.tanggal_selesai);
    formData.append('jenis_izin', form.jenis_izin);
    formData.append('keterangan', form.keterangan);
    
    // Add file if provided
    if (form.file) {
        formData.append('file', form.file);
    }
    
    router.post(route('admin.izin.update', props.izin.id), formData, {
        forceFormData: true, // Force Inertia to send as FormData
    });
};
</script>
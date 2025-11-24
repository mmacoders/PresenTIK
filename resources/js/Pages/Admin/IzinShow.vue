<template>
    <AdminLayout page-title="Detail Izin" mobile-page-title="Detail Izin">
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
                            <span class="ml-2 text-sm font-medium text-gray-900 md:ml-3">Detail Izin</span>
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
                    Detail Permintaan Izin
                </h1>
                <p class="text-gray-600 mt-2">Informasi lengkap permintaan izin pegawai.</p>
            </div>

            <!-- Izin Detail Card -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">Informasi Permintaan Izin</h2>
                    
                    <div class="flex space-x-2">
                        <button
                            @click="editIzin"
                            class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors duration-200 flex items-center"
                        >
                            <EditIcon class="w-4 h-4 mr-2" />
                            Edit
                        </button>
                        <button
                            @click="deleteIzin"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 flex items-center"
                        >
                            <TrashIcon class="w-4 h-4 mr-2" />
                            Hapus
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pegawai</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Nama</p>
                                <p class="font-medium">{{ izin.user.name }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="font-medium">{{ izin.user.email }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Jabatan</p>
                                <p class="font-medium">{{ izin.user.jabatan || '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Izin</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-500">Tanggal Izin</p>
                                <p class="font-medium">{{ formatDateRange(izin.tanggal_mulai, izin.tanggal_selesai) }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Jenis Izin</p>
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="izin.jenis_izin === 'penuh' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                                >
                                    {{ izin.jenis_izin === 'penuh' ? 'Izin Penuh' : 'Izin Parsial' }}
                                </span>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Keterangan</p>
                                <p class="font-medium">{{ izin.keterangan }}</p>
                            </div>
                            
                            <!-- File Attachment for Full Leave Requests -->
                            <div v-if="izin.jenis_izin === 'penuh' && izin.file_path">
                                <p class="text-sm text-gray-500">Dokumen Pendukung</p>
                                <div class="flex items-center mt-1">
                                    <PaperclipIcon class="w-4 h-4 text-gray-500 mr-2" />
                                    <a 
                                        :href="route('admin.izin.download', izin.id)" 
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline flex items-center"
                                    >
                                        Lihat Dokumen
                                    </a>
                                </div>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span 
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    :class="getStatusClass(izin.status)"
                                >
                                    {{ getStatusText(izin.status) }}
                                </span>
                            </div>
                            
                            <div v-if="izin.disetujui_oleh">
                                <p class="text-sm text-gray-500">Disetujui Oleh</p>
                                <p class="font-medium">{{ izin.disetujui_oleh }}</p>
                            </div>
                            
                            <div v-if="izin.catatan">
                                <p class="text-sm text-gray-500">Catatan</p>
                                <p class="font-medium">{{ izin.catatan }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Update Form (only for pending requests) -->
            <div v-if="izin.status === 'pending'" class="bg-white rounded-2xl shadow-md p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Update Status</h3>
                
                <form @submit.prevent="updateStatus" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                        <textarea
                            v-model="statusForm.catatan"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            :disabled="statusForm.processing"
                            placeholder="Tambahkan catatan untuk pegawai..."
                        ></textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="updateStatus('approved')"
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
                            :disabled="statusForm.processing"
                        >
                            <CheckIcon class="w-4 h-4 inline mr-2" />
                            Setujui
                        </button>
                        <button
                            type="button"
                            @click="updateStatus('rejected')"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                            :disabled="statusForm.processing"
                        >
                            <XIcon class="w-4 h-4 inline mr-2" />
                            Tolak
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
import { HomeIcon, ChevronRightIcon, FileTextIcon, EditIcon, TrashIcon, CheckIcon, XIcon, PaperclipIcon } from 'lucide-vue-next';

const props = defineProps({
    izin: Object,
});

const statusForm = useForm({
    catatan: '',
});

const formatDateRange = (startDate, endDate) => {
    if (startDate === endDate) {
        return new Date(startDate).toLocaleDateString('id-ID');
    }
    return `${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`;
};

const getStatusClass = (status) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
        default:
            return 'bg-yellow-100 text-yellow-800';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'approved':
            return 'Disetujui';
        case 'rejected':
            return 'Ditolak';
        case 'pending':
        default:
            return 'Menunggu';
    }
};

const editIzin = () => {
    router.get(route('admin.izin.edit', props.izin.id));
};

const deleteIzin = () => {
    if (confirm('Apakah Anda yakin ingin menghapus permintaan izin ini?')) {
        router.delete(route('admin.izin.destroy', props.izin.id));
    }
};

const updateStatus = (status) => {
    if (confirm(`Apakah Anda yakin ingin ${status === 'approved' ? 'menyetujui' : 'menolak'} permintaan izin ini?`)) {
        statusForm.patch(route('admin.izin.update', props.izin.id), {
            data: {
                status: status,
                catatan: statusForm.catatan,
            },
            onSuccess: () => {
                statusForm.reset();
            }
        });
    }
};
</script>
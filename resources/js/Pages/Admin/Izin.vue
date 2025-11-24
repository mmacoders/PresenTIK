<template>
    <AdminLayout page-title="Izin & Cuti" mobile-page-title="Izin">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Title + Controls -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Title + Description -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileTextIcon class="text-red-600" />
                        Izin & Cuti
                    </h2>
                    <p class="text-gray-600 mt-2">Kelola permintaan izin dan cuti pegawai</p>
                </div>

                <!-- Right: Search + Date Inputs + Filter + Export -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Bar -->
                    <div class="relative">
                        <SearchIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                        />
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Cari pegawai..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64"
                            @input="handleSearch"
                        />
                    </div>

                    <!-- Filter Dropdown -->
                    <Dropdown align="right" width="auto">
                        <template #trigger>
                            <button 
                                class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                title="Filter Rentang Waktu"
                            >
                                <FilterIcon class="w-4 h-4" />
                                <span>Rentang Waktu</span>
                            </button>
                        </template>

                        <template #content>
                            <div class="p-4 w-72">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                    <input
                                        v-model="filterForm.start_date"
                                        type="date"
                                        class="px-3 py-2 border border-gray-300 rounded-lg w-full"
                                    />
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                    <input
                                        v-model="filterForm.end_date"
                                        type="date"
                                        class="px-3 py-2 border border-gray-300 rounded-lg w-full"
                                    />
                                </div>
                                
                                <div class="flex justify-between gap-2">
                                    <button 
                                        @click="resetFilters"
                                        class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                                    >
                                        Reset
                                    </button>
                                    <button 
                                        @click="applyDateFilters"
                                        class="flex-1 px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                                    >
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </template>
                    </Dropdown>
                </div>
            </div>

            <!-- Leave Requests Table -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#ad1f32] border-b border-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Pegawai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal Izin</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jenis</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="izin in izins.data" 
                                :key="izin.id"
                                class="hover:bg-gray-50 transition-all duration-300"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ izin.user.name }}</div>
                                    <div class="text-sm text-gray-500">{{ izin.user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDateRange(izin.tanggal_mulai, izin.tanggal_selesai) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="izin.jenis_izin === 'penuh' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                                    >
                                        {{ izin.jenis_izin === 'penuh' ? 'Izin Penuh' : 'Izin Parsial' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate" :title="izin.keterangan">
                                        {{ izin.keterangan }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusClass(izin.status)"
                                    >
                                        {{ getStatusText(izin.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button
                                            @click="viewIzin(izin)"
                                            class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                                            title="Lihat Detail"
                                        >
                                            <EyeIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            v-if="izin.status === 'pending'"
                                            @click="verifyIzin(izin)"
                                            class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                            title="Verifikasi"
                                        >
                                            <CheckCircleIcon class="w-5 h-5" />
                                        </button>
                                        <button
                                            @click="deleteIzin(izin)"
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                            title="Hapus"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="izins.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada permintaan izin
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="fetchPage(izins.prev_page_url)" 
                            :disabled="!izins.prev_page_url"
                            :class="izins.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Sebelumnya
                        </button>
                        <button 
                            @click="fetchPage(izins.next_page_url)" 
                            :disabled="!izins.next_page_url"
                            :class="izins.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Berikutnya
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Menampilkan
                                <span class="font-medium">{{ izins.from || 0 }}</span>
                                sampai
                                <span class="font-medium">{{ izins.to || 0 }}</span>
                                dari
                                <span class="font-medium">{{ izins.total || 0 }}</span>
                                permintaan
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, index) in izins.links" :key="index">
                                    <span v-if="link.url === null" 
                                          class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed rounded-lg">
                                        {{ link.label }}
                                    </span>
                                    <button v-else-if="link.label === 'pagination.previous' || link.label.includes('Previous')" 
                                       @click="fetchPage(link.url)"
                                       class="relative inline-flex items-center px-2 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                        <ChevronLeftIcon class="h-5 w-5" />
                                    </button>
                                    <button v-else-if="link.label === 'pagination.next' || link.label.includes('Next')" 
                                       @click="fetchPage(link.url)"
                                       class="relative inline-flex items-center px-2 py-2 rounded-r-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                        <ChevronRightIcon class="h-5 w-5" />
                                    </button>
                                    <button v-else 
                                       @click="fetchPage(link.url)"
                                       :class="link.active ? 'z-10 bg-red-600 text-white relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-100 relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-lg'">
                                        {{ link.label }}
                                    </button>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Verification Modal -->
        <IzinVerificationModal
            :show="showVerificationModal"
            :izin="verifyingIzin"
            @close="closeVerificationModal"
            @updated="handleVerificationUpdated"
        />
        
        <!-- View Modal -->
        <div v-if="showViewModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden transform transition ease-out duration-200">
                <div class="border-b">
                    <div class="flex items-center gap-3 px-6 py-4">
                        <FileTextIcon class="text-red-600 w-6 h-6" />
                        <h3 class="text-lg font-bold text-gray-900">Detail Permintaan Izin</h3>
                    </div>
                </div>
                
                <div class="overflow-y-auto max-h-[calc(80vh-140px)]">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">Pegawai</label>
                            <p class="font-medium text-gray-900">{{ viewingIzin.user.name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">Email</label>
                            <p class="font-medium text-gray-900">{{ viewingIzin.user.email }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">Tanggal Izin</label>
                            <p class="font-medium text-gray-900">{{ formatDateRange(viewingIzin.tanggal_mulai, viewingIzin.tanggal_selesai) }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs uppercase text-gray-500 mb-1">Jenis Izin</label>
                            <span 
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700"
                            >
                                {{ viewingIzin.jenis_izin === 'penuh' ? 'Izin Penuh' : 'Izin Parsial' }}
                            </span>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs uppercase text-gray-500 mb-1">Keterangan</label>
                            <p class="font-medium text-gray-900">{{ viewingIzin.keterangan }}</p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs uppercase text-gray-500 mb-1">Status</label>
                            <span 
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getStatusClass(viewingIzin.status)"
                            >
                                {{ getStatusText(viewingIzin.status) }}
                            </span>
                        </div>
                        
                        <div v-if="viewingIzin.disetujui_oleh">
                            <label class="block text-xs uppercase text-gray-500 mb-1">Disetujui Oleh</label>
                            <p class="font-medium text-gray-900">{{ viewingIzin.disetujui_oleh }}</p>
                        </div>
                        
                        <div class="md:col-span-2" v-if="viewingIzin.catatan">
                            <label class="block text-xs uppercase text-gray-500 mb-1">Catatan</label>
                            <p class="font-medium text-gray-900">{{ viewingIzin.catatan }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="sticky bottom-0 bg-gray-50 border-t px-6 py-4">
                    <div class="flex justify-end">
                        <button 
                            @click="closeViewModal"
                            class="px-4 py-2 border text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { CheckIcon, XIcon, FileTextIcon, SearchIcon, FilterIcon, EyeIcon, TrashIcon, ChevronLeftIcon, ChevronRightIcon, CheckCircleIcon } from 'lucide-vue-next';
import { router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import debounce from 'lodash/debounce';
import Dropdown from '@/Components/Dropdown.vue';
import IzinVerificationModal from '@/Components/IzinVerificationModal.vue';

const props = defineProps({
    izins: Object,
    filters: Object,
    users: Array,
});

const showExportDropdown = ref(false);
const showVerificationModal = ref(false);
const showViewModal = ref(false);
const verifyingIzin = ref(null);
const viewingIzin = ref(null);

const filterForm = useForm({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
});

const handleSearch = debounce(() => {
    router.get(route('admin.izin'), filterForm.data(), {
        preserveState: true,
        replace: true
    });
}, 300);

const resetFilters = () => {
    filterForm.reset();
    router.get(route('admin.izin'));
};

const applyDateFilters = () => {
    applyFilters();
};

const fetchPage = (url) => {
    if (url) {
        router.get(url);
    }
};

const exportToExcel = () => {
    router.post(route('admin.laporan.export'), {
        ...filterForm.data()
    });
    showExportDropdown.value = false;
};

const exportToPDF = () => {
    // For now, we'll just show an alert since there's no specific PDF export route
    alert('Export ke PDF akan segera tersedia');
    showExportDropdown.value = false;
};

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

// CRUD Functions
const verifyIzin = (izin) => {
    verifyingIzin.value = izin;
    showVerificationModal.value = true;
};

const closeVerificationModal = () => {
    showVerificationModal.value = false;
    verifyingIzin.value = null;
};

const handleVerificationUpdated = () => {
    closeVerificationModal();
    router.reload();
};

const viewIzin = (izin) => {
    viewingIzin.value = izin;
    showViewModal.value = true;
};

const deleteIzin = (izin) => {
    if (confirm('Apakah Anda yakin ingin menghapus permintaan izin ini?')) {
        router.delete(route('admin.izin.destroy', izin.id), {
            onSuccess: () => {
                router.reload();
            }
        });
    }
};

const closeViewModal = () => {
    showViewModal.value = false;
    viewingIzin.value = null;
};

// Close export dropdown when clicking outside
const handleClickOutside = (event) => {
    if (showExportDropdown.value && 
        !event.target.closest('[title="Export"]') && 
        !event.target.closest('.absolute.right-0.mt-2.w-48')) {
        showExportDropdown.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
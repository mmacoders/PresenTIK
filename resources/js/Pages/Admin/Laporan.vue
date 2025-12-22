<template>
    <AdminLayout page-title="Laporan Presensi" mobile-page-title="Laporan">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                {{ $page.props.flash.error }}
            </div>

            <!-- Page Title + Controls -->
            <div class="mb-6 flex flex-col gap-4">
                <!-- Title + Description -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileTextIcon class="text-red-600" />
                        Laporan Global
                    </h1>
                    <p class="text-gray-600 mt-2">Rekapitulasi presensi dan izin seluruh pegawai.</p>
                </div>

                <!-- Tabs & Controls Toolbar -->
                <div class="flex flex-col lg:flex-row justify-between items-end border-b border-gray-200 mb-6 gap-4">
                    <!-- Tabs -->
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button
                            @click="activeTab = 'presensi'"
                            :class="[
                                activeTab === 'presensi'
                                    ? 'border-red-500 text-red-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                            ]"
                        >
                            <ClipboardListIcon class="w-5 h-5" />
                            Presensi
                        </button>
                        <button
                            @click="activeTab = 'izin'"
                            :class="[
                                activeTab === 'izin'
                                    ? 'border-red-500 text-red-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center gap-2'
                            ]"
                        >
                            <CalendarIcon class="w-5 h-5" />
                            Izin
                        </button>
                    </nav>

                    <!-- Controls: Search + Filter + Export -->
                    <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto pb-2">
                        <!-- Search Bar -->
                        <div class="relative w-full md:w-64">
                            <SearchIcon
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                            />
                            <input
                                v-model="filterForm.search"
                                type="text"
                                placeholder="Cari laporan..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full text-sm"
                                @input="handleSearch"
                            />
                        </div>

                        <!-- Filter Button -->
                        <div class="relative">
                            <button
                                ref="filterButton"
                                @click="showFilter = !showFilter"
                                class="px-3 py-2 border rounded-lg flex items-center space-x-2 w-full md:w-auto justify-center"
                                :class="isFilterActive ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:bg-gray-50'"
                            >
                                <FilterIcon class="h-5 w-5" :class="isFilterActive ? 'text-red-600' : 'text-gray-500'" />
                                <span class="text-sm">Filter</span>
                            </button>

                            <!-- Filter Popover -->
                            <div 
                                v-if="showFilter" 
                                ref="filterPopover"
                                class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-11/12 max-w-sm bg-white shadow-2xl rounded-xl border p-4 z-50 md:absolute md:top-full md:right-0 md:left-auto md:translate-x-0 md:translate-y-0 md:mt-2 md:w-80 md:shadow-lg"
                            >
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                        <input
                                            type="date"
                                            v-model="filterForm.start_date"
                                            class="w-full border border-gray-300 rounded-lg p-2"
                                        />
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                        <input
                                            type="date"
                                            v-model="filterForm.end_date"
                                            class="w-full border border-gray-300 rounded-lg p-2"
                                        />
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button
                                        @click="resetFilters"
                                        class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200 text-sm"
                                    >
                                        Reset
                                    </button>
                                    <button
                                        @click="applyFilters"
                                        class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200 text-sm"
                                    >
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Export Dropdown -->
                        <div class="relative">
                            <button
                                @click="showExportDropdown = !showExportDropdown"
                                class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center w-full md:w-auto"
                                title="Export"
                            >
                                <FileDownIcon class="h-5 w-5" />
                                <span class="md:hidden ml-2 text-sm">Export</span>
                            </button>

                            <!-- Export Dropdown Menu -->
                            <div 
                                v-if="showExportDropdown" 
                                class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                            >
                                <button
                                    @click="exportToExcel"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                >
                                    <FileSpreadsheetIcon class="h-4 w-4 mr-2" />
                                    Export ke Excel
                                </button>
                                <button
                                    @click="exportToPDF"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                                >
                                    <FileTextIcon class="h-4 w-4 mr-2" />
                                    Export ke PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div v-if="activeTab === 'presensi'" class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#ad1f32] border-b border-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Jam Masuk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="attendance in currentData.data" 
                                :key="attendance.id" 
                                class="hover:bg-gray-50 transition-all duration-300"
                            >
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(attendance.tanggal) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ attendance.user?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ attendance.user?.email || '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="getRoleClass(attendance.user?.role)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getRoleText(attendance.user?.role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTime(attendance.waktu_masuk) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getStatusClass(attendance)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getStatusText(attendance) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" :title="attendance.keterangan">
                                    {{ attendance.keterangan || '-' }}
                                </td>
                            </tr>
                            <tr v-if="!currentData.data || currentData.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data presensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Izin Table -->
            <div v-if="activeTab === 'izin'" class="bg-white rounded-2xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#ad1f32] border-b border-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Nama</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal Mulai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Tanggal Selesai</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Kategori Izin</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Dokumen</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr 
                                v-for="permission in currentData.data" 
                                :key="permission.id" 
                                class="hover:bg-gray-50 transition-all duration-300"
                            >
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ permission.user?.name || '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ permission.user?.email || '' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(permission.tanggal_mulai) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatDate(permission.tanggal_selesai) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 capitalize">Izin ({{ permission.catatan }})</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="getIzinStatusClass(permission.status)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getIzinStatusText(permission.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a
                                        v-if="permission.file_path"
                                        :href="route('admin.izin.download', permission.id)"
                                        target="_blank"
                                        class="text-blue-600 hover:text-blue-800 transition-colors duration-200"
                                        title="Unduh Dokumen"
                                    >
                                        <DownloadIcon class="w-5 h-5" />
                                    </a>
                                    <span v-else class="text-gray-400 text-sm">-</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-xs truncate" :title="permission.keterangan">
                                    {{ permission.keterangan || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2" v-if="permission.status === 'pending' || permission.status === 'Diajukan'">
                                        <button @click="updateStatus(permission.id, 'approved')" class="text-green-600 hover:text-green-900" title="Setujui">
                                            <CheckCircleIcon class="w-5 h-5" />
                                        </button>
                                        <button @click="updateStatus(permission.id, 'rejected')" class="text-red-600 hover:text-red-900" title="Tolak">
                                            <XCircleIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs italic">
                                        {{ permission.status === 'approved' || permission.status === 'Disetujui' ? 'Disetujui' : 'Ditolak' }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="!currentData.data || currentData.data.length === 0">
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data izin
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination (Shared) -->
            <div v-if="currentData && currentData.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-2xl shadow-md mt-0">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button 
                        @click="fetchPage(currentData.prev_page_url)" 
                        :disabled="!currentData.prev_page_url"
                        :class="currentData.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                        Sebelumnya
                    </button>
                    <button 
                        @click="fetchPage(currentData.next_page_url)" 
                        :disabled="!currentData.next_page_url"
                        :class="currentData.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                        Berikutnya
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Menampilkan
                            <span class="font-medium">{{ currentData.from || 0 }}</span>
                            sampai
                            <span class="font-medium">{{ currentData.to || 0 }}</span>
                            dari
                            <span class="font-medium">{{ currentData.total }}</span>
                            hasil
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                            <template v-for="(link, index) in currentData.links" :key="index">
                                <span v-if="link.url === null" 
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed rounded-lg">
                                    {{ link.label }}
                                </span>
                                <button v-else-if="link.label === 'pagination.previous' || link.label.includes('Previous') || link.label.includes('&laquo;')" 
                                   @click="fetchPage(link.url)"
                                   class="relative inline-flex items-center px-2 py-2 rounded-l-lg border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-100 rounded-lg">
                                    <ChevronLeftIcon class="h-5 w-5" />
                                </button>
                                <button v-else-if="link.label === 'pagination.next' || link.label.includes('Next') || link.label.includes('&raquo;')" 
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
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';
import Swal from 'sweetalert2'
import { 
    SearchIcon, 
    FilterIcon, 
    FileDownIcon, 
    FileSpreadsheetIcon, 
    FileTextIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    RefreshCwIcon,
    ClipboardListIcon,
    CalendarIcon,
    DownloadIcon,
    CheckCircleIcon,
    XCircleIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    attendances: Object,
    permissions: Object,
    filters: Object,
});

// Reactive data
const showFilter = ref(false);
const showExportDropdown = ref(false);
const filterButton = ref(null);
const filterPopover = ref(null);
const isRefreshing = ref(false);
const activeTab = ref('presensi');

const filterForm = useForm({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
});

// Computed properties
const currentData = computed(() => {
    return activeTab.value === 'presensi' ? props.attendances : props.permissions;
});

const isFilterActive = computed(() => {
    return filterForm.start_date || filterForm.end_date;
});

// Methods
const formatDate = (dateString) => {
    if (!dateString) return '-';
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatTime = (timeString) => {
    if (!timeString || timeString === '-') return '-';
    const date = new Date(timeString);
    if (isNaN(date.getTime())) return timeString;
    return date.toLocaleTimeString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: false 
    }).replace(/\./g, ':');
};

const getStatusText = (attendance) => {
    // Handle different status types
    if (attendance.status === 'Hadir') {
        return 'Hadir';
    } else if (attendance.status === 'Terlambat') {
        return 'Terlambat';
    } else if (attendance.status.includes('Izin')) {
        return 'Izin';
    } else if (attendance.status.includes('Tidak Hadir')) {
        return 'Absen';
    } else {
        return attendance.status || '-';
    }
};

const getStatusClass = (attendance) => {
    // Handle different status types
    if (attendance.status === 'Hadir') {
        return 'bg-green-100 text-green-800';
    } else if (attendance.status === 'Terlambat') {
        return 'bg-yellow-100 text-yellow-800';
    } else if (attendance.status.includes('Izin')) {
        return 'bg-blue-100 text-blue-800';
    } else if (attendance.status.includes('Tidak Hadir')) {
        return 'bg-red-100 text-red-800';
    } else {
        return 'bg-gray-100 text-gray-800';
    }
};

const getIzinStatusText = (status) => {
    switch (status) {
        case 'approved':
        case 'disetujui':
            return 'Disetujui';
        case 'rejected':
        case 'ditolak':
            return 'Ditolak';
        case 'pending':
        case 'diajukan':
            return 'Menunggu Persetujuan';
        default:
            return status;
    }
};

const getIzinStatusClass = (status) => {
    switch (status) {
        case 'Disetujui':
        case 'approved':
        case 'disetujui':
            return 'bg-green-100 text-green-800';
        case 'Ditolak':
        case 'rejected':
        case 'ditolak':
            return 'bg-red-100 text-red-800';
        case 'Diajukan':
        case 'pending':
        case 'diajukan':
        case 'Menunggu Persetujuan':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getRoleText = (role) => {
    if (!role) return '-';
    switch (role.toLowerCase()) {
        case 'superadmin':
            return 'SuperAdmin';
        case 'admin':
            return 'Admin';
        case 'user':
        case 'pegawai':
            return 'User';
        default:
            return role;
    }
};

const getRoleClass = (role) => {
    if (!role) return 'bg-gray-100 text-gray-800';
    switch (role.toLowerCase()) {
        case 'superadmin':
            return 'bg-purple-100 text-purple-800';
        case 'admin':
            return 'bg-indigo-100 text-indigo-800';
        case 'user':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const handleSearch = debounce(() => {
    router.get(route('admin.laporan'), filterForm.data(), {
        preserveState: true,
        replace: true
    });
}, 300);

const applyFilters = () => {
    router.get(route('admin.laporan'), filterForm.data(), {
        preserveState: true,
        replace: true
    });
    showFilter.value = false;
};

const resetFilters = () => {
    filterForm.reset();
    router.get(route('admin.laporan'));
    showFilter.value = false;
};

const exportToExcel = () => {
    const params = new URLSearchParams({
        format: 'excel',
        start_date: filterForm.start_date,
        end_date: filterForm.end_date,
        search: filterForm.search
    });
    
    window.location.href = `${route('admin.laporan.export')}?${params.toString()}`;
    showExportDropdown.value = false;
};

const exportToPDF = () => {
    const params = new URLSearchParams({
        format: 'pdf',
        start_date: filterForm.start_date,
        end_date: filterForm.end_date,
        search: filterForm.search
    });
    
    window.location.href = `${route('admin.laporan.export')}?${params.toString()}`;
    showExportDropdown.value = false;
};

const fetchPage = (url) => {
    if (!url) return;
    router.get(url, filterForm.data(), { preserveState: true });
};

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['attendances', 'permissions'],
        preserveScroll: true,
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

const updateStatus = (id, status) => {
    const isApprove = status === 'approved'

    Swal.fire({
        title: isApprove ? 'Setujui Izin?' : 'Tolak Izin?',
        text: isApprove
            ? 'Apakah Anda yakin ingin menyetujui izin ini?'
            : 'Apakah Anda yakin ingin menolak izin ini?',
        icon: isApprove ? 'question' : 'warning',
        showCancelButton: true,
        confirmButtonText: isApprove ? 'Ya, Setujui' : 'Ya, Tolak',
        cancelButtonText: 'Batal',
        confirmButtonColor: isApprove ? '#16a34a' : '#dc2626',
        cancelButtonColor: '#6b7280'
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(
                route('admin.izin.update', id),
                { status },
                {
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire({
                            icon: 'success',
                            title: isApprove ? 'Disetujui' : 'Ditolak',
                            text: isApprove
                                ? 'Izin berhasil disetujui.'
                                : 'Izin berhasil ditolak.',
                            timer: 1800,
                            showConfirmButton: false
                        })
                    }
                }
            )
        }
    })
}


// Click outside handler for filter popover
const handleClickOutside = (event) => {
    if (showFilter.value && 
        filterButton.value && 
        !filterButton.value.contains(event.target) && 
        filterPopover.value && 
        !filterPopover.value.contains(event.target)) {
        showFilter.value = false;
    }
    
    if (showExportDropdown.value && 
        !event.target.closest('.relative')) {
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
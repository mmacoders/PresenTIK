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
            <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Left: Title + Description -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <FileTextIcon class="text-red-600" />
                        Laporan Presensi
                    </h1>
                    <p class="text-gray-600 mt-2">Rekapitulasi presensi seluruh pegawai POLDA TIK.</p>
                </div>

                <!-- Right: Search + Filter + Export -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Search Bar -->
                    <div class="relative">
                        <SearchIcon
                            class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                        />
                        <input
                            v-model="filterForm.search"
                            type="text"
                            placeholder="Cari laporan..."
                            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64"
                            @input="handleSearch"
                        />
                    </div>

                    <!-- Filter Button -->
                    <div class="relative">
                        <button
                            ref="filterButton"
                            @click="showFilter = !showFilter"
                            class="px-3 py-2 border rounded-lg flex items-center space-x-2"
                            :class="isFilterActive ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:bg-gray-50'"
                        >
                            <FilterIcon class="h-5 w-5" :class="isFilterActive ? 'text-red-600' : 'text-gray-500'" />
                            <span>Rentang Waktu</span>
                        </button>

                        <!-- Filter Popover -->
                        <div 
                            v-if="showFilter" 
                            ref="filterPopover"
                            class="absolute right-0 mt-2 bg-white shadow-lg rounded-xl border p-4 z-50 w-80 max-w-sm"
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
                                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200"
                                >
                                    Reset
                                </button>
                                <button
                                    @click="applyFilters"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200"
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
                            class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center"
                            title="Export"
                        >
                            <FileDownIcon class="h-5 w-5" />
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

                    <!-- Refresh Button -->
                    <button
                        @click="refreshData"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-300 flex items-center justify-center"
                        title="Refresh Data"
                        :disabled="isRefreshing"
                    >
                        <RefreshCwIcon class="h-5 w-5" :class="{ 'animate-spin': isRefreshing }" />
                    </button>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
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
                                v-for="attendance in attendancesData" 
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ attendance.waktu_masuk || '-' }}</td>
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
                            <tr v-if="!attendancesData || attendancesData.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data presensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="attendances && attendances.links" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button 
                            @click="fetchPage(attendances.prev_page_url)" 
                            :disabled="!attendances.prev_page_url"
                            :class="attendances.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Sebelumnya
                        </button>
                        <button 
                            @click="fetchPage(attendances.next_page_url)" 
                            :disabled="!attendances.next_page_url"
                            :class="attendances.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                            Berikutnya
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Menampilkan
                                <span class="font-medium">{{ attendances.from || 0 }}</span>
                                sampai
                                <span class="font-medium">{{ attendances.to || 0 }}</span>
                                dari
                                <span class="font-medium">{{ attendances.total }}</span>
                                hasil
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-lg shadow-sm -space-x-px" aria-label="Pagination">
                                <template v-for="(link, index) in attendances.links" :key="index">
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
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import debounce from 'lodash/debounce';
import { 
    SearchIcon, 
    FilterIcon, 
    FileDownIcon, 
    FileSpreadsheetIcon, 
    FileTextIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    RefreshCwIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    attendances: Object,
    filters: Object,
});

// Reactive data
const showFilter = ref(false);
const showExportDropdown = ref(false);
const filterButton = ref(null);
const filterPopover = ref(null);
const isRefreshing = ref(false);

const filterForm = useForm({
    start_date: props.filters?.start_date || '',
    end_date: props.filters?.end_date || '',
    search: props.filters?.search || '',
});

// Computed properties
const attendancesData = computed(() => {
    return props.attendances?.data || props.attendances || [];
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

const getRoleText = (role) => {
    if (!role) return '-';
    switch (role.toLowerCase()) {
        case 'superadmin':
            return 'Super Admin';
        case 'admin':
            return 'Admin';
        case 'user':
            return 'Pegawai';
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
    // Use window.location.href to trigger download directly, or form submission
    // Inertia router.post might expect a JSON response, but export returns a file.
    // Better to use window.open or a hidden form for file downloads if not using Inertia's way properly.
    // However, if the backend returns a download response, Inertia can handle it if configured, 
    // but often it's easier to just construct the URL.
    
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
        only: ['attendances'],
        preserveScroll: true,
        onFinish: () => {
            isRefreshing.value = false;
        }
    });
};

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
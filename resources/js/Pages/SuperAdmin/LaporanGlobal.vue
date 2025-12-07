<template>
    <div class="flex h-screen bg-[#F5F6FA]">
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
                title="Laporan Global"
                :user-profile-pic="$page.props.auth.user.profile_pict_url"
                @toggle-sidebar="toggleSidebar"
            />

            <main class="flex-1 overflow-y-auto py-8">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Success/Error Messages -->
                    <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ $page.props.flash.error }}
                    </div>

                    <!-- Page Title + Search + Filter + Export -->
                    <!-- Page Title -->
                    <div class="mb-6">
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2">
                            <FileTextIcon class="text-red-600 w-6 h-6 md:w-8 md:h-8" />
                            Laporan Global Presensi
                        </h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Rekapitulasi presensi seluruh pegawai POLDA TIK.</p>
                    </div>

                    <!-- Tabs & Controls Toolbar -->
                    <div class="flex flex-col lg:flex-row justify-between items-end border-b border-gray-200 mb-6 gap-4">
                        <!-- Tabs -->
                        <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
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
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari laporan..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full text-sm"
                                    @input="handleSearch"
                                />
                            </div>

                            <div class="flex gap-2">
                                <!-- Filter Button -->
                                <div class="relative">
                                    <button
                                        ref="filterButton"
                                        @click="showFilter = !showFilter"
                                        class="px-3 py-2 border rounded-lg flex items-center justify-center space-x-2 transition-colors duration-200 text-sm"
                                        :class="isFilterActive ? 'border-red-500 bg-red-50' : 'border-gray-300 hover:bg-gray-50'"
                                    >
                                        <FilterIcon class="h-4 w-4" :class="isFilterActive ? 'text-red-600' : 'text-gray-500'" />
                                        <span>Filter</span>
                                    </button>

                                    <!-- Filter Popover -->
                                    <div 
                                        v-if="showFilter" 
                                        ref="filterPopover"
                                        class="absolute right-0 mt-2 bg-white shadow-lg rounded-xl border p-4 z-50 w-72"
                                    >
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                                <input
                                                    type="date"
                                                    v-model="filters.start_date"
                                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                                                />
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                                                <input
                                                    type="date"
                                                    v-model="filters.end_date"
                                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm"
                                                />
                                            </div>
                                        </div>

                                        <div class="mt-6 flex justify-end gap-3">
                                            <button
                                                @click="resetFilters"
                                                class="border border-gray-300 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-50 transition-all duration-200 text-xs"
                                            >
                                                Reset
                                            </button>
                                            <button
                                                @click="applyFilters"
                                                class="bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition-all duration-200 text-xs"
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
                                        class="px-3 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition-all duration-300 flex items-center justify-center gap-2 text-sm"
                                        title="Export"
                                    >
                                        <FileDownIcon class="h-4 w-4" />
                                        <span class="hidden md:inline">Export</span>
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
                    <div v-if="activeTab === 'presensi'" class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-red-600 text-white text-left">
                                    <tr>
                                        <th class="px-6 py-3">Nama</th>
                                        <th class="px-6 py-3">Role</th>
                                        <th class="px-6 py-3">Tanggal</th>
                                        <th class="px-6 py-3">Jam Masuk</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="attendance in attendances.data" :key="attendance.id" class="border-b hover:bg-red-50">
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ attendance.user.name }}</td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getRoleClass(attendance.user.role)">
                                                {{ getRoleText(attendance.user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">{{ formatDate(attendance.tanggal) }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ attendance.waktu_masuk || '-' }}</td>
                                        <td class="px-6 py-3">
                                            <span
                                                :class="getStatusClass(attendance)"
                                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                            >
                                                {{ getStatusText(attendance) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">
                                            {{ attendance.keterangan || '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="attendances.data.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data presensi
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Izin Table -->
                    <div v-if="activeTab === 'izin'" class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-red-600 text-white text-left">
                                    <tr>
                                        <th class="px-6 py-3">Nama</th>
                                        <th class="px-6 py-3">Role</th>
                                        <th class="px-6 py-3">Tanggal Mulai</th>
                                        <th class="px-6 py-3">Tanggal Selesai</th>
                                        <th class="px-6 py-3">Kategori Izin</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="permission in permissions.data" :key="permission.id" class="border-b hover:bg-red-50">
                                        <td class="px-6 py-3 font-medium text-gray-900">{{ permission.user.name }}</td>
                                        <td class="px-6 py-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getRoleClass(permission.user.role)">
                                                {{ getRoleText(permission.user.role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">{{ formatDate(permission.tanggal_mulai) }}</td>
                                        <td class="px-6 py-3 text-gray-700">{{ formatDate(permission.tanggal_selesai) }}</td>
                                        <td class="px-6 py-3 text-gray-700">Izin ({{ permission.catatan }})</td>
                                        <td class="px-6 py-3">
                                            <span
                                                :class="getIzinStatusClass(permission.status)"
                                                class="px-3 py-1 rounded-full text-xs font-semibold"
                                            >
                                                {{ getIzinStatusText(permission.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-gray-700">
                                            {{ permission.keterangan || '-' }}
                                        </td>
                                    </tr>
                                    <tr v-if="permissions.data.length === 0">
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data izin
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Pagination (Shared) -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-b-xl shadow-md mt-[-1px]">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <a :href="currentData.prev_page_url" 
                               :class="currentData.prev_page_url ? 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                                Sebelumnya
                            </a>
                            <a :href="currentData.next_page_url" 
                               :class="currentData.next_page_url ? 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50' : 'ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-white cursor-not-allowed'">
                                Berikutnya
                            </a>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan
                                    <span class="font-medium">{{ currentData.from }}</span>
                                    sampai
                                    <span class="font-medium">{{ currentData.to }}</span>
                                    dari
                                    <span class="font-medium">{{ currentData.total }}</span>
                                    hasil
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, index) in currentData.links" :key="index">
                                        <span v-if="link.url === null" 
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 cursor-not-allowed">
                                            {{ link.label }}
                                        </span>
                                        <a v-else
                                            :href="link.url"
                                            @click.prevent="fetchPage(link.url)"
                                            :class="link.active ? 'z-10 bg-red-50 border-red-500 text-red-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                                            {{ link.label }}
                                        </a>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';
import { 
    SearchIcon, 
    FilterIcon, 
    FileDownIcon, 
    FileSpreadsheetIcon, 
    FileTextIcon,
    ClipboardListIcon,
    CalendarIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
    attendances: Object,
    permissions: Object,
    filters: Object,
});

// Reactive data
const page = usePage();
const searchQuery = ref('');
const activeTab = ref('presensi');
const showFilter = ref(false);
const showExportDropdown = ref(false);
const filterButton = ref(null);
const filterPopover = ref(null);
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);

// Computed properties
const isFilterActive = computed(() => {
    return props.filters?.start_date || props.filters?.end_date;
});

const currentData = computed(() => {
    return activeTab.value === 'presensi' ? props.attendances : props.permissions;
});

// Methods
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
    sidebarCollapsed.value = collapsed;
};

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

const handleSearch = () => {
    // Implement search functionality
    router.get('/superadmin/laporan', { search: searchQuery.value }, { 
        preserveState: true,
        replace: true
    });
};

const applyFilters = () => {
    router.get('/superadmin/laporan', { 
        start_date: props.filters.start_date,
        end_date: props.filters.end_date
    }, { 
        preserveState: true,
        replace: true
    });
    showFilter.value = false;
};

const resetFilters = () => {
    router.get('/superadmin/laporan', {}, { 
        preserveState: true,
        replace: true
    });
    showFilter.value = false;
};

const exportToExcel = () => {
    const params = new URLSearchParams();
    if (props.filters.start_date) params.append('start_date', props.filters.start_date);
    if (props.filters.end_date) params.append('end_date', props.filters.end_date);
    if (searchQuery.value) params.append('search', searchQuery.value);
    
    window.location.href = `/superadmin/laporan/export/excel?${params.toString()}`;
    showExportDropdown.value = false;
};

const exportToPDF = () => {
    const params = new URLSearchParams();
    if (props.filters.start_date) params.append('start_date', props.filters.start_date);
    if (props.filters.end_date) params.append('end_date', props.filters.end_date);
    if (searchQuery.value) params.append('search', searchQuery.value);
    
    window.location.href = `/superadmin/laporan/export/pdf?${params.toString()}`;
    showExportDropdown.value = false;
};

const fetchPage = (url) => {
    if (!url) return;
    router.get(url, {}, { preserveState: true });
};

const getRoleText = (role) => {
    const roleMap = {
        'user': 'User',
        'admin': 'Admin',
        'superadmin': 'Super Admin'
    };
    return roleMap[role] || role;
};

const getRoleClass = (role) => {
    const classMap = {
        'user': 'bg-blue-100 text-blue-800',
        'admin': 'bg-purple-100 text-purple-800',
        'superadmin': 'bg-red-100 text-red-800'
    };
    return classMap[role] || 'bg-gray-100 text-gray-800';
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
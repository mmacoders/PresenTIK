<template>
    <AdminLayout page-title="Detail Pegawai" mobile-page-title="Detail Pegawai">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Breadcrumb -->
            <nav class="flex mb-6">
                <ol class="flex items-center space-x-2 text-sm">
                    <li>
                        <Link :href="route('admin.dashboard')" class="text-gray-500 hover:text-gray-700 flex items-center">
                            <HomeIcon class="h-4 w-4 mr-1" />
                            Dashboard
                        </Link>
                    </li>
                    <li>
                        <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                    </li>
                    <li>
                        <Link :href="route('admin.pegawai')" class="text-gray-500 hover:text-gray-700">
                            Pegawai
                        </Link>
                    </li>
                    <li>
                        <ChevronRightIcon class="h-4 w-4 text-gray-400" />
                    </li>
                    <li class="text-gray-900 font-medium">
                        {{ pegawai.name }}
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

            <!-- Profile Header -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div class="flex items-center">
                        <div class="relative">
                            <img 
                                :src="pegawai.profile_pict_url || '/images/profile.png'" 
                                :alt="pegawai.name"
                                class="w-20 h-20 rounded-full ring-4 ring-red-500 object-cover"
                                @error="handleImageError"
                            />
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ pegawai.name }}</h2>
                            <p class="text-gray-600">{{ pegawai.jabatan }}</p>
                            <div class="mt-1">
                                <span 
                                    v-if="pegawai.status === 'aktif'"
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
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-2">
                        <button 
                            @click="toggleUserStatus"
                            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition"
                            :class="pegawai.status === 'aktif' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                        >
                            <BanIcon v-if="pegawai.status === 'aktif'" class="h-4 w-4 inline mr-2" />
                            <CheckIcon v-else class="h-4 w-4 inline mr-2" />
                            {{ pegawai.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Attendance Statistics -->
            <div class="bg-white rounded-2xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik Absensi Bulan Ini</h3>
                
                <div class="bg-gray-50 p-4 rounded-lg text-center">
                    <p class="text-gray-700 text-sm font-medium">Total Absensi Bulan Ini</p>
                    <div class="flex justify-center gap-8 mt-4">
                        <div>
                            <p class="text-2xl font-bold text-red-600">{{ stats.hadir }}</p>
                            <span class="text-xs text-gray-500">Hadir</span>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ stats.izin }}</p>
                            <span class="text-xs text-gray-500">Izin</span>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-yellow-600">{{ stats.terlambat }}</p>
                            <span class="text-xs text-gray-500">Terlambat</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Records -->
            <div class="bg-white rounded-2xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Absensi Terbaru</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keluar</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="attendance in attendances" :key="attendance.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(attendance.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ attendance.waktu_masuk || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ attendance.waktu_keluar || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        :class="getStatusClass(attendance)"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                    >
                                        {{ getStatusText(attendance) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ attendance.keterangan || '-' }}
                                </td>
                            </tr>
                            <tr v-if="attendances.length === 0">
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data absensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
  HomeIcon, 
  ChevronRightIcon, 
  UserIcon,
  BanIcon,
  CheckIcon
} from 'lucide-vue-next';

const props = defineProps({
    pegawai: Object,
    stats: Object,
    attendances: Array,
});

// Methods
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const getStatusClass = (attendance) => {
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'bg-blue-100 text-blue-800';
        case 'sakit':
            return 'bg-yellow-100 text-yellow-800';
        case 'terlambat':
        case 'Terlambat':
            return 'bg-orange-100 text-orange-800';
        case 'hadir':
        case 'Hadir':
            return attendance.waktu_masuk ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
        case 'Izin Parsial (Check-in)':
        case 'Izin Parsial (Selesai)':
            return 'bg-purple-100 text-purple-800';
        default:
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'bg-green-100 text-green-800';
            } else if (attendance.waktu_masuk) {
                return 'bg-blue-100 text-blue-800';
            } else {
                return 'bg-gray-100 text-gray-800';
            }
    }
};

const getStatusText = (attendance) => {
    switch (attendance.status) {
        case 'izin':
        case 'Izin':
        case 'Izin (Valid)':
            return 'Izin';
        case 'sakit':
            return 'Sakit';
        case 'terlambat':
        case 'Terlambat':
            return 'Terlambat';
        case 'hadir':
        case 'Hadir':
            return attendance.waktu_masuk ? 'Hadir' : 'Belum Absen';
        case 'Izin Parsial (Check-in)':
            return 'Izin Parsial (Check-in)';
        case 'Izin Parsial (Selesai)':
            return 'Izin Parsial (Selesai)';
        default:
            if (attendance.waktu_masuk && attendance.waktu_keluar) {
                return 'Hadir';
            } else if (attendance.waktu_masuk) {
                return 'Sudah Check-in';
            } else {
                return attendance.status || 'Belum Absen';
            }
    }
};

const toggleUserStatus = () => {
    if (confirm(`Apakah Anda yakin ingin ${props.pegawai.status === 'aktif' ? 'menonaktifkan' : 'mengaktifkan'} pegawai ini?`)) {
        router.patch(route('admin.pegawai.toggle-status', props.pegawai), {}, {
            onSuccess: () => {
                // Reload the page to show updated data
                router.reload();
            }
        });
    }
};

const handleImageError = (event) => {
    event.target.src = '/images/profile.png';
};
</script>

<style scoped>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>
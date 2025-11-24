<template>
    <SuperAdminLayout page-title="Detail Pegawai" mobile-page-title="Detail Pegawai">
        <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">

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
                            @click="editUser"
                            class="px-4 py-2 bg-[#C62828] text-white rounded-lg hover:bg-[#b71c1c] transition flex items-center"
                        >
                            <EditIcon class="h-4 w-4 mr-2" />
                            Edit
                        </button>
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
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam Keluar</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="record in absensi" :key="record.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(record.tanggal) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.jam_masuk || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ record.jam_keluar || '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        v-if="record.status === 'hadir'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                                    >
                                        Hadir
                                    </span>
                                    <span 
                                        v-else-if="record.status === 'izin'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"
                                    >
                                        Izin
                                    </span>
                                    <span 
                                        v-else-if="record.status === 'terlambat'"
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"
                                    >
                                        Terlambat
                                    </span>
                                    <span 
                                        v-else
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                                    >
                                        Tidak Hadir
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="absensi.length === 0">
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada data absensi
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SuperAdminLayout>
</template>

<script setup>
import { router, usePage } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import {
    UserIcon,
    EditIcon,
    BanIcon,
    CheckIcon,
    HomeIcon,
    ChevronRightIcon
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

// Get page props
const page = usePage();
const props = defineProps({
    pegawai: Object,
    absensi: Array,
    stats: Object
});

// Methods
const editUser = () => {
    // Redirect to the main pegawai page where the user can be edited
    router.visit(route('superadmin.pegawai'));
};

const toggleUserStatus = () => {
    const newStatus = props.pegawai.status === 'aktif' ? 'nonaktif' : 'aktif';
    router.put(route('superadmin.pegawai.toggle-status', props.pegawai.id), {
        status: newStatus
    }, {
        onSuccess: () => {
            // Refresh the page to show updated data
            router.reload();
        }
    });
};

const handleImageError = (event) => {
    event.target.src = '/images/default-profile.png';
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
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
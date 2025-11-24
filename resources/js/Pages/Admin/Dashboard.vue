<template>
  <AdminLayout page-title="Dashboard" mobile-page-title="Dashboard">
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
      <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Greeting Banner -->
        <div class="bg-gradient-to-r from-red-800 to-red-600 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
            <div class="absolute right-0 top-0 h-full w-1/3 opacity-10 pointer-events-none">
                <img src="/images/logo-tik-polri.png" class="h-full w-full object-contain object-right" />
            </div>
            <div class="relative z-10">
                <h1 class="text-2xl md:text-3xl font-bold uppercase tracking-wider">{{ greeting }}</h1>
                <p class="text-red-100 mt-2 font-medium flex items-center">
                    <CalendarIcon class="w-5 h-5 mr-2" />
                    {{ currentDate }}
                </p>
            </div>
        </div>

        <!-- Info Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Admin Info Card -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden lg:col-span-1">
              <div class="p-6">
                  <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <UserIcon class="w-5 h-5 text-red-600 mr-2" />
                        Administrator
                    </h2>
                    <div class="px-3 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-full uppercase">
                        Aktif
                    </div>
                  </div>
                  
                  <div class="space-y-4">
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-24 h-24 rounded-full p-1 border-2 border-red-600 mb-3">
                            <img :src="$page.props.auth.user.profile_pict_url || '/images/default-avatar.png'" class="w-full h-full rounded-full object-cover" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center">{{ $page.props.auth.user.name }}</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ $page.props.auth.user.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100 text-center">
                            <p class="text-gray-500 text-xs uppercase">Role</p>
                            <p class="font-bold text-gray-900 truncate">Super Admin / Admin</p>
                        </div>
                    </div>
                  </div>
              </div>
            </div>

            <!-- Stats Card -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden lg:col-span-2 flex flex-col">
              <div class="p-6 flex-1 flex flex-col">
                  <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <BarChart3Icon class="w-5 h-5 text-red-600 mr-2" />
                        Statistik Hari Ini
                    </h2>
                    <div class="flex items-center text-gray-900 font-mono font-bold bg-gray-100 px-3 py-1 rounded">
                        <ClockIcon class="w-4 h-4 mr-2 text-red-600" />
                        {{ currentTime }}
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                    <div v-for="stat in stats" :key="stat.name" class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center justify-between transition-all hover:border-red-200 hover:shadow-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">{{ stat.name }}</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ stat.value }}</p>
                        </div>
                        <div :class="stat.bgColor" class="p-3 rounded-xl">
                            <component :is="stat.icon" class="w-6 h-6" :class="stat.color" />
                        </div>
                    </div>
                  </div>
              </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden">
             <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                    <ClipboardListIcon class="w-5 h-5 text-red-600 mr-2" />
                    Daftar Presensi Hari Ini
                </h2>
                <a href="/admin/laporan" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase">
                    Lihat Semua
                </a>
             </div>
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                   <thead class="bg-gray-50">
                      <tr>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Masuk</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                      </tr>
                   </thead>
                   <tbody class="divide-y divide-gray-100">
                      <tr v-for="(person, index) in attendanceData" :key="index" class="hover:bg-gray-50">
                         <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ person.name }}</td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">{{ person.checkIn }}</td>
                         <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="getStatusClass(person.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded uppercase">
                               {{ person.status }}
                            </span>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">{{ person.keterangan }}</td>
                      </tr>
                      <tr v-if="!attendanceData || attendanceData.length === 0">
                         <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Tidak ada data presensi hari ini
                         </td>
                      </tr>
                   </tbody>
                </table>
             </div>
        </div>

      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { 
  CheckCircleIcon, 
  ClockIcon, 
  ClipboardIcon, 
  XCircleIcon,
  CalendarIcon,
  UserIcon,
  BarChart3Icon,
  ClipboardListIcon
} from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

// Get data from the controller
const props = defineProps({
  admin: Object,
  stats: Object,
  attendanceData: Array,
});

// Prepare statistics data
const stats = ref([
  { name: 'Hadir Hari Ini', value: props.stats?.present || 0, icon: CheckCircleIcon, color: 'text-green-600', bgColor: 'bg-green-100' },
  { name: 'Terlambat', value: props.stats?.late || 0, icon: ClockIcon, color: 'text-yellow-600', bgColor: 'bg-yellow-100' },
  { name: 'Izin / Cuti', value: props.stats?.leave || 0, icon: ClipboardIcon, color: 'text-blue-600', bgColor: 'bg-blue-100' },
  { name: 'Tidak Hadir', value: props.stats?.absent || 0, icon: XCircleIcon, color: 'text-red-600', bgColor: 'bg-red-100' }
]);

const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
let timeInterval;

onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  }, 60000);
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});

// Computed properties
const greeting = computed(() => {
  const hour = new Date().getHours();
  const name = usePage().props.auth.user.name;
  if (hour >= 5 && hour < 11) {
    return `Selamat Pagi, ${name}!`;
  } else if (hour >= 11 && hour < 15) {
    return `Selamat Siang, ${name}!`;
  } else if (hour >= 15 && hour < 20) {
    return `Selamat Sore, ${name}!`;
  } else {
    return `Selamat Malam, ${name}!`;
  }
});

const currentDate = computed(() => {
  return new Date().toLocaleDateString('id-ID', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
});

const getStatusClass = (status) => {
  // Check if status contains "Terlambat" with time difference
  if (status && status.includes('Terlambat')) {
    return 'bg-yellow-100 text-yellow-800';
  }
  
  switch (status) {
    case 'Hadir':
      return 'bg-green-100 text-green-800';
    case 'Terlambat':
      return 'bg-yellow-100 text-yellow-800';
    case 'Izin':
      return 'bg-blue-100 text-blue-800';
    case 'Tidak Hadir':
      return 'bg-red-100 text-red-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>
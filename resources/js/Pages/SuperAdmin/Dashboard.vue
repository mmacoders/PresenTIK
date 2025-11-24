<template>
  <SuperAdminLayout page-title="Dashboard" mobile-page-title="Dashboard">
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
            <!-- Super Admin Info Card -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden lg:col-span-1">
              <div class="p-6">
                  <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <UserIcon class="w-5 h-5 text-red-600 mr-2" />
                        Super Admin
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
                            <p class="font-bold text-gray-900 truncate">Super Admin</p>
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
                        Ringkasan Sistem
                    </h2>
                    <div class="flex items-center text-gray-900 font-mono font-bold bg-gray-100 px-3 py-1 rounded">
                        <ClockIcon class="w-4 h-4 mr-2 text-red-600" />
                        {{ currentTime }}
                    </div>
                  </div>

                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 flex-1">
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center justify-between transition-all hover:border-red-200 hover:shadow-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Total Pegawai</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ totalUsers }}</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <UsersIcon class="w-6 h-6 text-blue-600" />
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center justify-between transition-all hover:border-red-200 hover:shadow-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Total Admin</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ totalAdmins }}</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-xl">
                            <ShieldIcon class="w-6 h-6 text-green-600" />
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center justify-between transition-all hover:border-red-200 hover:shadow-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Hadir Hari Ini</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ presentToday }}</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <CheckCircleIcon class="w-6 h-6 text-purple-600" />
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200 flex items-center justify-between transition-all hover:border-red-200 hover:shadow-sm">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-bold">Terlambat Hari Ini</p>
                            <p class="text-2xl font-black text-gray-900 mt-1">{{ lateToday }}</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-xl">
                            <BellIcon class="w-6 h-6 text-orange-600" />
                        </div>
                    </div>
                  </div>
              </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Attendance Chart -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <TrendingUpIcon class="w-5 h-5 text-red-600 mr-2" />
                        Statistik Kehadiran
                    </h3>
                </div>
                <div class="h-80">
                    <apexchart 
                        type="area" 
                        :options="attendanceChartOptions" 
                        :series="attendanceChartSeries"
                        height="100%"
                    />
                </div>
            </div>
            
            <!-- Jabatan Distribution Chart -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <PieChartIcon class="w-5 h-5 text-red-600 mr-2" />
                        Distribusi Pegawai
                    </h3>
                </div>
                <div class="h-80">
                    <apexchart 
                        type="donut" 
                        :options="jabatanChartOptions" 
                        :series="jabatanChartSeries"
                        height="100%"
                    />
                </div>
            </div>
        </div>

      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import VueApexCharts from "vue3-apexcharts";
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import {
  UsersIcon,
  ShieldIcon,
  CheckCircleIcon,
  BellIcon,
  UserIcon,
  TrendingUpIcon,
  PieChartIcon,
  BarChart3Icon,
  ClockIcon,
  CalendarIcon
} from 'lucide-vue-next';

// Props
const props = defineProps({
  totalUsers: Number,
  totalAdmins: Number,
  presentToday: Number,
  lateToday: Number,
  absentToday: Number,
  jabatanStats: Array,
  weeklyAttendance: Object,
});

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

// Chart data
const attendanceChartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    zoom: { enabled: false }
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    categories: props.weeklyAttendance?.dates || [],
    axisBorder: { show: false },
    axisTicks: { show: false }
  },
  yaxis: {
    min: 0,
    labels: {
      formatter: function (val) {
        return val.toFixed(0);
      }
    }
  },
  tooltip: {
    x: { format: 'dd/MM/yy' }
  },
  legend: {
    position: 'top',
    horizontalAlign: 'right'
  },
  colors: ['#10B981', '#EF4444']
}));

const attendanceChartSeries = computed(() => [
  {
    name: 'Hadir',
    data: props.weeklyAttendance?.present || []
  },
  {
    name: 'Absen',
    data: props.weeklyAttendance?.absent || []
  }
]);

const jabatanChartOptions = computed(() => ({
  chart: { type: 'donut' },
  labels: props.jabatanStats?.map(stat => stat.name) || [],
  legend: { position: 'bottom' },
  dataLabels: { enabled: false },
  colors: ['#dc2626', '#2563eb', '#16a34a', '#d97706', '#9333ea', '#0891b2']
}));

const jabatanChartSeries = computed(() => {
  return props.jabatanStats?.map(stat => stat.total_users) || [];
});
</script>
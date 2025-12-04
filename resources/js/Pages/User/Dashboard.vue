```html
<template>
  <div class="min-h-screen bg-gray-50 overflow-x-hidden">
    <!-- Sidebar -->
    <Sidebar 
      ref="sidebarRef"
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />

    <!-- Main Content Wrapper -->
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header 
        :user="user" 
        :sidebar-collapsed="sidebarCollapsed"
        current-page="Dashboard" 
        mobile-breadcrumb="Dashboard"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content -->
      <main class="pt-8 p-4 sm:p-6">
        <div class="max-w-7xl mx-auto space-y-6">
          
          <!-- Official Banner / Greeting -->
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
            <!-- User Info Card -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden lg:col-span-1">
              <div class="p-6">
                  <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <UserIcon class="w-5 h-5 text-red-600 mr-2" />
                        Personil
                    </h2>
                    <div class="px-3 py-1 bg-red-50 text-red-700 text-xs font-bold rounded-full uppercase">
                        Aktif
                    </div>
                  </div>
                  
                  <div class="space-y-4">
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-24 h-24 rounded-full p-1 border-2 border-red-600 mb-3">
                            <img :src="user.profile_pict_url || '/images/default-avatar.png'" class="w-full h-full rounded-full object-cover" />
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 text-center">{{ user.name }}</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ user.jabatan || '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <p class="text-gray-500 text-xs uppercase">Pangkat</p>
                            <p class="font-bold text-gray-900 truncate">{{ user.pangkat || '-' }}</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <p class="text-gray-500 text-xs uppercase">NRP/NIP</p>
                            <p class="font-bold text-gray-900 truncate">{{ user.nip || user.nrp }}</p>
                        </div>
                    </div>
                  </div>
              </div>
            </div>

            <!-- Attendance Card -->
            <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden lg:col-span-2 flex flex-col">
              <div class="p-6 flex-1 flex flex-col">
                  <div class="flex items-center justify-between mb-6 border-b border-gray-100 pb-4">
                    <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                        <CalendarCheckIcon class="w-5 h-5 text-red-600 mr-2" />
                        Presensi Hari Ini
                    </h2>
                    <div class="flex items-center text-gray-900 font-mono font-bold bg-gray-100 px-3 py-1 rounded">
                        <ClockIcon class="w-4 h-4 mr-2 text-red-600" />
                        {{ currentTime }}
                    </div>
                  </div>

                  <div class="flex flex-col gap-6 flex-1">
                    <!-- Check-in Section -->
                    <div class="rounded-xl border border-gray-200 p-6 flex flex-col md:flex-row items-center justify-between transition-all hover:border-red-200 hover:shadow-sm bg-gradient-to-b from-white to-gray-50">
                        <div class="flex items-center gap-4 mb-4 md:mb-0">
                            <div class="p-3 bg-red-100 text-[#b21f1f] rounded-xl">
                                <LogInIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <span class="text-xs font-bold uppercase text-gray-500 tracking-wider">Waktu Masuk</span>
                                <p class="text-4xl font-black text-gray-900 mt-1">
                                    {{ todayAttendance && todayAttendance.waktu_masuk ? todayAttendance.waktu_masuk : '--:--' }}
                                </p>
                                <p class="text-sm font-medium mt-1" :class="todayAttendance && todayAttendance.status_lokasi_masuk === 'valid' ? 'text-green-600' : 'text-gray-500'">
                                    {{ getLocationValidationText() }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="w-full md:w-auto flex flex-col gap-2">
                             <button
                                :disabled="hasCheckedIn || (todayIzin && todayIzin.status === 'approved' && todayIzin.jenis_izin === 'penuh')"
                                @click="goToAbsensi"
                                class="w-full md:w-48 py-3 rounded-lg font-bold text-sm uppercase tracking-wide transition-all shadow-lg transform hover:-translate-y-0.5"
                                :class="(hasCheckedIn || (todayIzin && todayIzin.status === 'approved' && todayIzin.jenis_izin === 'penuh')) ? 'bg-gray-100 text-gray-400 cursor-not-allowed shadow-none' : 'bg-[#b21f1f] text-white hover:bg-[#991b1b] shadow-red-200'"
                            >
                                {{ hasCheckedIn ? 'Sudah Check-in' : (todayIzin && todayIzin.status === 'approved' && todayIzin.jenis_izin === 'penuh' ? 'Sedang Izin' : 'Check-in Sekarang') }}
                            </button>
                        </div>
                    </div>

                    <!-- Attendance Info / Keterangan -->
                    <div class="bg-gray-50 rounded-xl p-5 border border-gray-200">
                        <h3 class="text-sm font-bold text-gray-800 uppercase mb-3 flex items-center">
                            <InfoIcon class="w-4 h-4 mr-2 text-[#b21f1f]" />
                            Keterangan Presensi
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Status Kehadiran</p>
                                <p class="font-bold text-gray-900 mt-1">{{ getTodayStatusText() }}</p>
                            </div>
                            <div v-if="todayAttendance && todayAttendance.keterangan">
                                <p class="text-xs text-gray-500 uppercase">Catatan Harian</p>
                                <p class="font-medium text-gray-900 mt-1">{{ todayAttendance.keterangan }}</p>
                            </div>
                            <div v-else>
                                <p class="text-xs text-gray-500 uppercase">Catatan Harian</p>
                                <p class="text-gray-400 italic mt-1 text-sm">Tidak ada catatan</p>
                            </div>
                        </div>
                    </div>

                  </div>
              </div>
            </div>
          </div>

          <!-- History Table -->
          <div class="bg-white rounded-xl shadow-md border-t-4 border-red-600 overflow-hidden">
             <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-800 uppercase flex items-center">
                    <HistoryIcon class="w-5 h-5 text-red-600 mr-2" />
                    Riwayat Minggu Ini
                </h2>
                <button @click="goToAbsensi" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase">
                    Lihat Semua
                </button>
             </div>
             <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                   <thead class="bg-gray-50">
                      <tr>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Masuk</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lampiran</th>
                         <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Ket</th>
                      </tr>
                   </thead>
                   <tbody class="divide-y divide-gray-100">
                      <tr v-for="(item, index) in combinedHistory" :key="`history-${index}`" class="hover:bg-gray-50">
                         <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(item.date) }}</td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                           <template v-if="item.type === 'attendance'">
                             {{ item.data.waktu_masuk || '-' }}
                           </template>
                           <template v-else>
                             <span class="text-gray-400 italic text-xs">Izin</span>
                           </template>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap">
                           <span 
                             :class="item.type === 'attendance' ? getAttendanceStatusClass(item.data) : getIzinStatusClass(item.data)" 
                             class="px-2 py-1 inline-flex text-xs leading-5 font-bold rounded uppercase"
                           >
                               <template v-if="item.type === 'attendance'">
                                 {{ getAttendanceStatusText(item.data) }}
                               </template>
                               <template v-else>
                                 {{ getIzinStatusText(item.data) }}
                               </template>
                           </span>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm">
                           <template v-if="item.type === 'izin' && item.data.file_path">
                             <a 
                               :href="`/storage/${item.data.file_path}`" 
                               target="_blank"
                               class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium"
                             >
                               <FileTextIcon class="w-4 h-4 mr-1" />
                               Lihat File
                             </a>
                           </template>
                           <template v-else>
                             <span class="text-gray-400 text-xs">-</span>
                           </template>
                         </td>
                         <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">
                           <template v-if="item.type === 'attendance'">
                             {{ item.data.keterangan || '-' }}
                           </template>
                           <template v-else>
                             {{ item.data.keterangan || '-' }}
                           </template>
                         </td>
                      </tr>
                      <tr v-if="combinedHistory.length === 0">
                         <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">
                            Tidak ada data presensi atau izin
                         </td>
                      </tr>
                   </tbody>
                </table>
             </div>
          </div>

        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { 
  UserIcon, 
  CalendarCheckIcon, 
  LogInIcon, 
  LogOutIcon, 
  HistoryIcon, 
  MapPinIcon,
  CalendarIcon,
  CalendarXIcon,
  ClockIcon,
  InfoIcon,
  FileTextIcon
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    todayAttendance: Object,
    todayIzin: Object,
    attendanceHistory: Array,
    izinHistory: Array, // Add izin history
    systemSettings: Object,
});

// Office location for POLDA TIK
const officeLocation = {
  lat: 0.5240005,
  lng: 123.0604752
};

// Attendance radius in meters
const attendanceRadius = 100;

const mapContainer = ref(null);
const mapInstance = ref(null);
const marker = ref(null);
const circle = ref(null);

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));

let timeInterval;

// Initialize the map when the component is mounted
onMounted(() => {
  initMap();
  
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  }, 60000); // Update every minute
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
  
  // Clean up map instance
  if (mapInstance.value) {
    mapInstance.value.remove();
  }
});

// Initialize Leaflet map
const initMap = () => {
  if (!mapContainer.value) return;
  
  // Create map instance
  mapInstance.value = L.map(mapContainer.value).setView([officeLocation.lat, officeLocation.lng], 15);
  
  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
  }).addTo(mapInstance.value);
  
  // Add marker for office location
  marker.value = L.marker([officeLocation.lat, officeLocation.lng], {
    icon: L.divIcon({
      className: 'bg-red-600 rounded-full w-8 h-8 border-2 border-white shadow-lg flex items-center justify-center',
      iconSize: [32, 32],
      iconAnchor: [16, 16],
      html: '<div class="w-6 h-6 bg-white rounded-full flex items-center justify-center"><div class="w-3 h-3 bg-red-600 rounded-full"></div></div>'
    })
  }).addTo(mapInstance.value);
  
  // Add circle for attendance radius
  circle.value = L.circle([officeLocation.lat, officeLocation.lng], {
    color: '#dc2626',
    fillColor: '#dc2626',
    fillOpacity: 0.2,
    radius: attendanceRadius
  }).addTo(mapInstance.value);
  
  // Add popup to marker
  marker.value.bindPopup("Kantor POLRES").openPopup();
};

// Computed properties
const greeting = computed(() => {
  const hour = new Date().getHours();
  if (hour >= 5 && hour < 11) {
    return `Selamat Pagi, ${props.user.name}!`;
  } else if (hour >= 11 && hour < 15) {
    return `Selamat Siang, ${props.user.name}!`;
  } else if (hour >= 15 && hour < 20) {
    return `Selamat Sore, ${props.user.name}!`;
  } else {
    return `Selamat Malam, ${props.user.name}!`;
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

const hasCheckedIn = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_masuk;
});

const todayStatus = computed(() => {
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    return 'Izin (Valid)';
  } else if (props.todayAttendance && props.todayAttendance.status === 'Izin Parsial (Check-in)') {
    return 'Sudah Check-in (Izin Parsial)';
  } else if (hasCheckedIn.value) {
    return 'Sudah Check-in';
  } else {
    return 'Belum Check-in';
  }
});

// Combine attendance and izin history
const combinedHistory = computed(() => {
  const combined = [];
  
  // Add attendance records
  if (props.attendanceHistory) {
    props.attendanceHistory.forEach(attendance => {
      combined.push({
        type: 'attendance',
        date: attendance.tanggal,
        data: attendance
      });
    });
  }
  
  // Add izin records - expand multi-day izin into individual days
  if (props.izinHistory) {
    props.izinHistory.forEach(izin => {
      const startDate = new Date(izin.tanggal_mulai);
      const endDate = new Date(izin.tanggal_selesai);
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
      
      // Loop through each day of the izin period
      for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
        // Only add if within the last 7 days
        if (d >= sevenDaysAgo) {
          const dateStr = d.toISOString().split('T')[0];
          
          // Check if there's already an attendance record for this date
          const hasAttendance = combined.some(item => 
            item.type === 'attendance' && item.date === dateStr
          );
          
          // Only add izin if there's no attendance record (to avoid duplicates)
          if (!hasAttendance) {
            combined.push({
              type: 'izin',
              date: dateStr,
              data: izin
            });
          }
        }
      }
    });
  }
  
  // Sort by date descending (newest first)
  return combined.sort((a, b) => new Date(b.date) - new Date(a.date));
});

// Methods
const getTodayStatusText = () => {
    return todayStatus.value;
};

const getLocationValidationText = () => {
  if (!props.todayAttendance || !props.todayAttendance.status_lokasi_masuk) {
    return 'Belum check-in';
  }
  
  if (props.todayAttendance.status_lokasi_masuk === 'valid') {
    return 'Lokasi Valid';
  } else {
    return 'Lokasi Tidak Valid';
  }
};

const getAttendanceStatusText = (attendance) => {
  // First check if this attendance record is associated with any leave permission
  if (attendance.status === 'Izin (Valid)' || attendance.status === 'izin' || attendance.status === 'Izin') {
    return 'Izin';
  } else if (attendance.status === 'Izin Parsial (Check-in)' || attendance.status === 'Izin Parsial (Selesai)') {
    return attendance.status;
  } else if (attendance.status === 'terlambat' || attendance.status === 'Terlambat') {
    return 'Terlambat';
  } else if (attendance.status === 'alpha') {
    return 'Tidak Hadir';
  } else if (attendance.status === 'hadir' || attendance.status === 'Hadir') {
    return attendance.waktu_masuk ? 'Tepat Waktu' : 'Tidak Hadir';
  } else if (attendance.waktu_masuk) {
    return 'Hadir';
  } else {
    return 'Tidak Hadir';
  }
};

const sidebarRef = ref(null);

const toggleSidebar = () => {
  if (window.innerWidth >= 768) {
    if (sidebarRef.value) {
      sidebarRef.value.toggleCollapse();
    }
  } else {
    sidebarOpen.value = !sidebarOpen.value;
  }
};

const handleSidebarCollapse = (collapsed) => {
  sidebarCollapsed.value = collapsed;
};

const goToAbsensi = () => {
  router.visit('/user/absensi');
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', { 
    weekday: 'long', 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const getStatusClass = (status) => {
  switch (status) {
    case 'Belum Check-in':
      return 'bg-gray-100 text-gray-700';
    case 'Sudah Check-in':
      return 'bg-red-100 text-red-700';
    case 'Izin (Valid)':
    case 'Sudah Check-in (Izin Parsial)':
      return 'bg-pink-100 text-pink-700';
    default:
      return 'bg-gray-100 text-gray-700';
  }
};

const getAttendanceStatusClass = (attendance) => {
  // Handle all possible status values from the database
  if (attendance.status === 'Izin (Valid)' || attendance.status === 'izin' || attendance.status === 'Izin' || 
      attendance.status === 'Izin Parsial (Check-in)' || attendance.status === 'Izin Parsial (Selesai)') {
    return 'bg-gray-100 text-gray-700';
  } else if (attendance.status === 'terlambat' || attendance.status === 'Terlambat') {
    return 'bg-red-100 text-red-700';
  } else if (attendance.status === 'hadir' || attendance.status === 'Hadir') {
    return attendance.waktu_masuk ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700';
  } else if (attendance.waktu_masuk) {
    return 'bg-green-100 text-green-700'; // Has checked in
  } else {
    return 'bg-gray-100 text-gray-700'; // No attendance or absent
  }
};

const getIzinStatusText = (izin) => {
  if (izin.status === 'approved' || izin.status === 'disetujui') {
    return `Izin Disetujui (${izin.jenis_izin === 'penuh' ? 'Full' : 'Parsial'})`;
  } else if (izin.status === 'rejected' || izin.status === 'ditolak') {
    return `Izin Ditolak`;
  } else {
    return `Menunggu Persetujuan`;
  }
};

const getIzinStatusClass = (izin) => {
  if (izin.status === 'approved' || izin.status === 'disetujui') {
    return 'bg-green-100 text-green-700';
  } else if (izin.status === 'rejected' || izin.status === 'ditolak') {
    return 'bg-red-100 text-red-700';
  } else {
    return 'bg-yellow-100 text-yellow-800';
  }
};
</script>
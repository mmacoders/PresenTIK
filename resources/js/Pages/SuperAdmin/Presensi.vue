<template>
  <SuperAdminLayout page-title="Presensi" mobile-page-title="Presensi">
    <div class="p-4 sm:p-6 bg-gray-50 min-h-screen">
      <div class="max-w-4xl mx-auto">
        
        <!-- Page Title -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900 flex items-center">
            <CalendarCheckIcon class="w-8 h-8 text-red-600 mr-3" />
            Presensi Harian
          </h1>
          <p class="text-gray-600 mt-2">{{ currentDate }}</p>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash && $page.props.flash.error" class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
          {{ $page.props.flash.error }}
        </div>

        <!-- Presensi Card -->
        <div class="bg-white rounded-2xl shadow-lg border-t-4 border-red-600 p-8">
          <div class="flex items-center mb-6">
            <div class="p-4 rounded-xl bg-red-50 mr-4">
              <ClockIcon class="w-8 h-8 text-red-600" />
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Presensi Super Admin</h2>
              <p class="text-gray-600">{{ currentTime }}</p>
            </div>
          </div>

          <!-- Status Info -->
          <div v-if="todayAttendance" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
              <CheckCircleIcon class="h-6 w-6 text-green-600 mr-3" />
              <div>
                <p class="font-semibold text-green-800">Anda sudah melakukan presensi hari ini</p>
                <p class="text-sm text-green-700 mt-1">Waktu Check-in: {{ todayAttendance.waktu_masuk }}</p>
              </div>
            </div>
          </div>

          <div v-else class="mb-6">
            <p class="text-gray-700 mb-4">Silakan lakukan presensi untuk hari ini.</p>
            
            <!-- Check-in Button -->
            <button
              @click="handleCheckIn"
              :disabled="isSubmitting"
              class="w-full bg-red-600 text-white py-4 px-6 rounded-xl hover:bg-red-700 transition-all duration-200 flex items-center justify-center font-semibold text-lg shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <CalendarCheckIcon class="w-6 h-6 mr-2" />
              {{ isSubmitting ? 'Memproses...' : 'Check-in Sekarang' }}
            </button>
          </div>

          <!-- Location Info -->
          <div class="mb-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
              <MapPinIcon class="w-5 h-5 mr-2 text-red-600" />
              Informasi Lokasi
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <div class="text-sm text-gray-600 mb-1">Status Lokasi Anda:</div>
                <div class="flex items-center">
                  <div v-if="gettingLocation" class="flex items-center text-gray-500">
                    <Loader2Icon class="w-4 h-4 mr-2 animate-spin" />
                    Mendeteksi lokasi...
                  </div>
                  <div v-else-if="currentLocation" :class="isWithinOfficeRadius ? 'text-green-600' : 'text-red-600'" class="flex items-center font-medium">
                    <component :is="isWithinOfficeRadius ? CheckCircleIcon : XCircleIcon" class="w-5 h-5 mr-2" />
                    {{ isWithinOfficeRadius ? 'Dalam Radius Kantor' : 'Diluar Radius Kantor' }}
                  </div>
                  <div v-else class="text-red-500">Lokasi tidak terdeteksi</div>
                </div>
                
                <div class="mt-3 text-sm text-gray-600">
                  <div>Radius Diizinkan: <span class="font-medium text-gray-900">{{ attendanceRadius }} Meter</span></div>
                </div>
              </div>
              
              <div class="h-48 bg-gray-200 rounded-lg overflow-hidden relative">
                <div ref="mapContainer" class="w-full h-full z-0"></div>
              </div>
            </div>
            
            <!-- Toggle Button for Radius Validation -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <button
                @click="toggleLocationValidation"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center"
                :class="locationValidationDisabled 
                  ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                  : 'bg-red-100 text-red-700 hover:bg-red-200'"
              >
                <component :is="locationValidationDisabled ? ToggleLeftIcon : ToggleRightIcon" class="w-4 h-4 mr-2" />
                {{ locationValidationDisabled ? 'Aktifkan Validasi Radius' : 'Nonaktifkan Validasi Radius' }}
              </button>
              <p class="text-xs text-gray-500 mt-1">
                {{ locationValidationDisabled 
                  ? 'Validasi radius dinonaktifkan (Mode Testing)' 
                  : 'Validasi radius aktif' }}
              </p>
            </div>
          </div>

          <!-- Info -->
          <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start">
              <InfoIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
              <div class="text-sm text-blue-800">
                <p class="font-semibold mb-1">Informasi:</p>
                <ul class="list-disc list-inside space-y-1">
                  <li>Presensi dapat dilakukan setiap hari kerja</li>
                  <li>Pastikan Anda melakukan check-in pada waktu yang tepat</li>
                  <li>Riwayat presensi dapat dilihat di menu Laporan Global</li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Attendance History -->
        <div v-if="recentAttendance && recentAttendance.length > 0" class="mt-8 bg-white rounded-2xl shadow-lg border-t-4 border-red-600 p-8">
          <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
            <ClipboardListIcon class="w-6 h-6 text-red-600 mr-2" />
            Riwayat Presensi Terbaru
          </h3>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tanggal</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Waktu Check-in</th>
                  <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="attendance in recentAttendance" :key="attendance.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(attendance.tanggal) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ attendance.waktu_masuk }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(attendance.status)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ attendance.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </SuperAdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import SuperAdminLayout from '@/Layouts/SuperAdminLayout.vue';
import {
  CalendarCheckIcon,
  ClockIcon,
  CheckCircleIcon,
  InfoIcon,
  ClipboardListIcon,
  MapPinIcon,
  CheckIcon,
  XCircleIcon,
  Loader2Icon,
  ToggleLeftIcon,
  ToggleRightIcon
} from 'lucide-vue-next';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Swal from 'sweetalert2';

const props = defineProps({
  todayAttendance: Object,
  recentAttendance: Array,
  systemSettings: Object,
});

const isSubmitting = ref(false);
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));
const gettingLocation = ref(false);
const currentLocation = ref(null);
const mapContainer = ref(null);
const mapLoaded = ref(false);
const locationValidationDisabled = ref(false);

// Office location with fallback values
const officeLocation = {
  lat: props.systemSettings?.location_latitude || 0.5240005,
  lng: props.systemSettings?.location_longitude || 123.0604752
};

// Attendance radius with fallback value
const attendanceRadius = props.systemSettings?.location_radius || 100;

// Check if current location is within office radius
const isWithinOfficeRadius = computed(() => {
  if (!currentLocation.value) return false;
  
  const lat1 = currentLocation.value.lat;
  const lng1 = currentLocation.value.lng;
  const lat2 = officeLocation.lat;
  const lng2 = officeLocation.lng;
  
  // Convert to radians
  const radLat1 = (Math.PI * lat1) / 180;
  const radLng1 = (Math.PI * lng1) / 180;
  const radLat2 = (Math.PI * lat2) / 180;
  const radLng2 = (Math.PI * lng2) / 180;
  
  // Haversine formula
  const deltaLat = radLat2 - radLat1;
  const deltaLng = radLng2 - radLng1;
  
  const a = Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) +
            Math.cos(radLat1) * Math.cos(radLat2) *
            Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
  
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  
  // Earth's radius in meters
  const earthRadius = 6371000;
  
  // Calculate distance
  const distance = earthRadius * c;
  
  return distance <= attendanceRadius;
});

const toggleLocationValidation = () => {
  locationValidationDisabled.value = !locationValidationDisabled.value;
};

// Get current location
const getCurrentLocation = () => {
  gettingLocation.value = true;
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        gettingLocation.value = false;
        currentLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
      },
      (error) => {
        gettingLocation.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal mendapatkan lokasi: ' + error.message,
        });
      }
    );
  } else {
    gettingLocation.value = false;
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Geolocation tidak didukung oleh browser Anda.',
    });
  }
};

// Initialize map
const initMap = () => {
  setTimeout(() => {
    if (mapContainer.value) {
      try {
        const map = L.map(mapContainer.value).setView([officeLocation.lat, officeLocation.lng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        const marker = L.marker([officeLocation.lat, officeLocation.lng]).addTo(map);
        
        L.circle([officeLocation.lat, officeLocation.lng], {
          color: '#dc2626',
          fillColor: '#dc2626',
          fillOpacity: 0.2,
          radius: attendanceRadius
        }).addTo(map);
        
        marker.bindPopup("Kantor POLDA TIK").openPopup();
        mapLoaded.value = true;
      } catch (error) {
        console.error('Error initializing map:', error);
        mapLoaded.value = true;
      }
    }
  }, 100);
};

// Update time every second
let timeInterval;
onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }, 1000);
  
  initMap();
  getCurrentLocation();
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
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

const handleCheckIn = () => {
  Swal.fire({
    title: 'Konfirmasi Check-in',
    text: 'Apakah Anda yakin ingin melakukan check-in sekarang?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Check-in',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      if (!currentLocation.value && !locationValidationDisabled.value) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Lokasi Anda belum terdeteksi. Silakan tunggu sebentar atau refresh halaman.',
        });
        return;
      }

      if (!isWithinOfficeRadius.value && !locationValidationDisabled.value) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Anda berada di luar radius kantor. Tidak dapat melakukan presensi.',
        });
        return;
      }

      isSubmitting.value = true;
      
      const checkinData = {
        lat: currentLocation.value ? currentLocation.value.lat : 0,
        lng: currentLocation.value ? currentLocation.value.lng : 0,
        disable_validation: locationValidationDisabled.value
      };
      
      router.post(route('superadmin.presensi.checkin'), checkinData, {
        onSuccess: () => {
          isSubmitting.value = false;
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Presensi berhasil dicatat',
            confirmButtonColor: '#dc2626'
          });
        },
        onError: () => {
          isSubmitting.value = false;
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan saat melakukan presensi',
            confirmButtonColor: '#dc2626'
          });
        }
      });
    }
  });
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString('id-ID', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
};

const getStatusClass = (status) => {
  if (status === 'Hadir') return 'bg-green-100 text-green-800';
  if (status === 'Terlambat') return 'bg-yellow-100 text-yellow-800';
  return 'bg-gray-100 text-gray-800';
};
</script>

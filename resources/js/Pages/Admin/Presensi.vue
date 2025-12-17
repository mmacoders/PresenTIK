<template>
  <AdminLayout page-title="Presensi" mobile-page-title="Presensi">
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

        <!-- Cards Container -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <!-- Presensi Card -->
          <div class="bg-white rounded-2xl shadow-lg border-t-4 border-red-600 p-6 flex flex-col h-full">
            <div class="flex items-center mb-6">
              <div class="p-3 rounded-xl bg-red-50 mr-4">
                <ClockIcon class="w-8 h-8 text-red-600" />
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-900">Presensi Admin</h2>
                <p class="text-gray-600">{{ currentTime }}</p>
              </div>
            </div>

            <!-- Status Info -->
            <div v-if="todayAttendance" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex-grow">
              <div class="flex items-center">
                <CheckCircleIcon class="h-6 w-6 text-green-600 mr-3" />
                <div>
                  <p class="font-semibold text-green-800">Anda sudah melakukan presensi hari ini</p>
                  <p class="text-sm text-green-700 mt-1">Waktu Check-in: {{ todayAttendance.waktu_masuk }}</p>
                </div>
              </div>
            </div>

            <div v-else class="mb-6 flex-grow">
              <p class="text-gray-700 mb-4">Silakan lakukan presensi untuk hari ini.</p>
              
              <!-- Late arrival reason input -->
              <div v-if="isLateArrival" class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="late_reason">
                  Keterangan Keterlambatan
                </label>
                <textarea 
                  v-model="lateArrivalReason" 
                  id="late_reason"
                  class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-colors duration-200 shadow-sm" 
                  rows="3" 
                  placeholder="Mohon berikan alasan keterlambatan Anda..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">Harap isi keterangan keterlambatan Anda sebelum melakukan check-in.</p>
              </div>

              <!-- Check-in Button -->
              <button
                @click="handleCheckIn"
                :disabled="isCheckInDisabled"
                class="w-full py-4 px-6 rounded-xl transition-all duration-200 flex items-center justify-center font-semibold text-lg shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                :class="isCheckInDisabled ? 'bg-gray-400 text-gray-200' : 'bg-red-600 text-white hover:bg-red-700'"
              >
                <CalendarCheckIcon class="w-6 h-6 mr-2" />
                {{ getCheckInButtonText }}
              </button>
            </div>


          </div>

          <!-- Leave Request Card -->
          <div class="bg-white rounded-2xl shadow-lg border-t-4 border-blue-600 p-6 flex flex-col h-full">
            <div class="flex items-center mb-6">
              <div class="p-3 rounded-xl bg-blue-50 mr-4">
                <FileTextIcon class="w-8 h-8 text-blue-600" />
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-900">Pengajuan Izin</h2>
                <p class="text-gray-600">Ajukan izin jika tidak dapat hadir</p>
              </div>
            </div>
            
            <div class="mb-6 flex-grow">
              <p class="text-gray-700 mb-4">Ajukan izin jika tidak dapat hadir hari ini:</p>
              
              <button 
                @click="handlePermissionClick"
                class="w-full py-4 rounded-xl transition-all duration-200 font-semibold flex items-center justify-center text-lg shadow-md hover:shadow-lg bg-blue-600 text-white hover:bg-blue-700"
              >
                <PlusIcon class="w-6 h-6 mr-2" />
                Ajukan Izin
              </button>
            </div>

            <!-- Info -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg mt-auto">
              <div class="flex items-start">
                <InfoIcon class="h-5 w-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
                <div class="text-sm text-blue-800">
                  <p class="font-semibold mb-1">Informasi:</p>
                  <ul class="list-disc list-inside space-y-1">
                    <li>Presensi dapat dilakukan setiap hari kerja</li>
                    <li>Pastikan Anda melakukan check-in pada waktu yang tepat</li>
                    <li>Riwayat presensi dapat dilihat di menu Laporan Presensi</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Location Information Card -->
        <div class="mb-8 bg-white rounded-2xl shadow-lg border-t-4 border-red-600 p-6">
            <div class="flex items-center mb-6">
              <div class="p-3 rounded-xl bg-gray-50 mr-4">
                <MapPinIcon class="w-8 h-8 text-red-600" />
              </div>
              <div>
                <h2 class="text-xl font-bold text-gray-900">Informasi Lokasi</h2>
                <p class="text-gray-600">Detail lokasi kantor dan posisi Anda</p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
               <div class="space-y-4">
                 <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="text-sm text-gray-600 mb-1">Lokasi Kantor</div>
                    <div class="font-medium text-lg text-gray-900">{{ officeLocation.lat }}, {{ officeLocation.lng }}</div>
                 </div>
                 
                 <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="text-sm text-gray-600 mb-1">Radius Presensi</div>
                    <div class="font-medium text-lg text-gray-900">{{ attendanceRadius }} meter</div>
                 </div>

                 <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="text-sm text-gray-600 mb-1">Lokasi Anda</div>
                    <div v-if="currentLocation">
                         <div class="font-medium text-lg text-gray-900 mb-2">{{ currentLocation.lat }}, {{ currentLocation.lng }}</div>
                         <div class="flex items-center" :class="isWithinOfficeRadius ? 'text-green-600' : 'text-red-600'">
                             <component :is="isWithinOfficeRadius ? CheckCircleIcon : XCircleIcon" class="w-5 h-5 mr-2" />
                             <span class="font-medium">{{ isWithinOfficeRadius ? 'Dalam Radius Kantor' : 'Diluar Radius Kantor' }}</span>
                         </div>
                    </div>
                     <div v-else class="text-red-500 flex items-center py-2">
                        <Loader2Icon v-if="gettingLocation" class="w-5 h-5 mr-2 animate-spin" />
                        {{ gettingLocation ? 'Mendeteksi lokasi...' : 'Lokasi tidak terdeteksi' }}
                    </div>
                 </div>
                 
                 <!-- Toggle Button -->
                  <div class="pt-2">
                    <button
                      @click="toggleLocationValidation"
                      class="w-full px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 flex items-center justify-center shadow-sm"
                      :class="locationValidationDisabled 
                        ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                        : 'bg-red-100 text-red-700 hover:bg-red-200'"
                    >
                      <component :is="locationValidationDisabled ? ToggleLeftIcon : ToggleRightIcon" class="w-5 h-5 mr-2" />
                      {{ locationValidationDisabled ? 'Validasi Radius: NONAKTIF' : 'Validasi Radius: AKTIF' }}
                    </button>
                    <p class="text-xs text-center mt-2 text-gray-500">{{ locationValidationDisabled ? 'Anda dapat melakukan presensi dari mana saja' : 'Anda hanya dapat melakukan presensi di dalam radius kantor' }}</p>
                  </div>
               </div>
               
               <div class="h-80 bg-gray-100 rounded-xl overflow-hidden border border-gray-200 relative shadow-inner">
                  <div ref="mapContainer" class="w-full h-full z-0"></div>
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ attendance.date_display || formatDate(attendance.tanggal) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatTime(attendance.waktu_masuk) }}</td>
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
  </AdminLayout>

  <!-- Permission Modal -->
  <div v-if="showPermissionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
      <div class="p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold text-gray-900">Ajukan Izin</h3>
          <button @click="showPermissionModal = false" class="text-gray-500 hover:text-gray-700">
            <XIcon class="h-6 w-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitPermissionRequest" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                Tanggal Mulai
              </label>
              <input
                id="start_date"
                type="date"
                v-model="permissionStartDate"
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-colors duration-200 shadow-sm"
                required
              />
            </div>
            
            <div>
              <label class="block text-gray-700 text-sm font-bold mb-2" for="end_date">
                Tanggal Selesai
              </label>
              <input
                id="end_date"
                type="date"
                v-model="permissionEndDate"
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-colors duration-200 shadow-sm"
                required
              />
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_reason">
              Keterangan Izin
            </label>
            <textarea
              id="permission_reason"
              v-model="permissionReason"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-colors duration-200 shadow-sm"
              rows="4"
              placeholder="Jelaskan alasan izin Anda..."
              required
            ></textarea>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_category">
              Kategori Izin
            </label>
            <select
              id="permission_category"
              v-model="permissionCategory"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-colors duration-200 shadow-sm"
              required
            >
              <option value="" disabled>Pilih Kategori</option>
              <option value="Izin">Izin</option>
              <option value="Sakit">Sakit</option>
              <option value="Cuti">Cuti</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          
          <!-- File Upload -->
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_file">
              Dokumen Pendukung (PDF/JPG/PNG)
            </label>
            <input
              id="permission_file"
              type="file"
              @change="handleFileChange"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-blue-600 focus:border-blue-600 transition-colors duration-200 shadow-sm"
              required
            />
            <p class="text-xs text-gray-500 mt-1">Unggah dokumen pendukung untuk izin (maks. 2MB)</p>
          </div>
          
          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showPermissionModal = false"
              class="flex-1 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors duration-200"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submittingPermission"
              class="flex-1 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center"
              :class="{ 'opacity-50 cursor-not-allowed': submittingPermission }"
            >
              <Loader2Icon v-if="submittingPermission" class="w-5 h-5 mr-2 animate-spin" />
              Ajukan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
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
  ToggleRightIcon,
  FileTextIcon,
  PlusIcon,
  XIcon
} from 'lucide-vue-next';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import Swal from 'sweetalert2';

const props = defineProps({
  todayAttendance: Object,
  todayIzin: Object,
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

// Permission Modal State
const showPermissionModal = ref(false);
const permissionStartDate = ref(new Date().toISOString().split('T')[0]);
const permissionEndDate = ref(new Date().toISOString().split('T')[0]);
const permissionReason = ref('');
const permissionCategory = ref('');
const permissionFile = ref(null);
const submittingPermission = ref(false);

// Late Arrival State
const lateArrivalReason = ref('');

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

// Check if user is late based on system settings and grace period
const isLateArrival = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const jamMasuk = props.systemSettings?.jam_masuk || '08:00:00';
  const gracePeriodMinutes = props.systemSettings?.grace_period_minutes || 10;
  
  // Calculate grace period end time (jam_masuk + grace_period_minutes)
  const gracePeriodEnd = new Date(`1970-01-01T${jamMasuk}`);
  gracePeriodEnd.setMinutes(gracePeriodEnd.getMinutes() + gracePeriodMinutes);
  const gracePeriodEndString = gracePeriodEnd.toTimeString().substring(0, 8);
  
  // User is considered late if current time is after grace period end time
  return currentTime > gracePeriodEndString;
});

// Check if current time is within presensi hours (start time to cutoff time)
const isWithinPresensiHours = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const startTime = props.systemSettings?.presensi_start_time || '06:00:00';
  const cutoffTime = props.systemSettings?.cutoff_time || '10:00:00';
  
  return currentTime >= startTime && currentTime <= cutoffTime;
});

const isCheckInDisabled = computed(() => {
  const isLeave = props.todayIzin && props.todayIzin.status === 'approved' && props.todayIzin.jenis_izin === 'penuh';
  return isSubmitting.value || !isWithinPresensiHours.value || isLeave;
});

const getCheckInButtonText = computed(() => {
  if (isSubmitting.value) return 'Memproses...';
  if (props.todayIzin && props.todayIzin.status === 'approved' && props.todayIzin.jenis_izin === 'penuh') return 'Sedang Izin';
  if (!isWithinPresensiHours.value) return 'Diluar Jam Presensi';
  return 'Check-in Sekarang';
});

// Check if current time is before start time
const isBeforeStartTime = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const startTime = props.systemSettings?.presensi_start_time || '06:00:00';
  
  return currentTime < startTime;
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

      // Check if user is trying to check in before start time
      if (isBeforeStartTime.value) {
        const startTime = props.systemSettings?.presensi_start_time || '06:00:00';
        Swal.fire({
          icon: 'warning',
          title: 'Belum Waktunya!',
          text: `Presensi dimulai pukul ${startTime.substring(0, 5)}.`,
        });
        return;
      }



      // Check if late and reason is provided
      if (isLateArrival.value && !lateArrivalReason.value.trim()) {
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan!',
          text: 'Anda terlambat. Mohon isi keterangan keterlambatan sebelum melakukan check-in.',
        });
        return;
      }

      isSubmitting.value = true;
      
      const checkinData = {
        lat: currentLocation.value ? currentLocation.value.lat : 0,
        lng: currentLocation.value ? currentLocation.value.lng : 0,
        disable_validation: locationValidationDisabled.value,
        keterangan: lateArrivalReason.value
      };
      
      router.post(route('admin.presensi.checkin'), checkinData, {
        onSuccess: (page) => {
          isSubmitting.value = false;
          
          if (page.props.flash.error) {
             Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: page.props.flash.error,
              confirmButtonColor: '#dc2626'
            });
          } else {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: 'Presensi berhasil dicatat',
              confirmButtonColor: '#dc2626'
            });
          }
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

const formatTime = (timeString) => {
  if (!timeString || timeString === '-') return '-';
  // Check if it's already in simplified format like "08:00:00"
  if (timeString.length <= 8) return timeString;
  
  // Try to parse as Date if it's an ISO string
  try {
    const date = new Date(timeString);
    if (!isNaN(date.getTime())) {
      return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      });
    }
  } catch (e) {
    return timeString;
  }
  return timeString;
};

const getStatusClass = (status) => {
  if (status === 'Hadir') return 'bg-green-100 text-green-800';
  if (status === 'Terlambat') return 'bg-yellow-100 text-yellow-800';
  if (status && (status.startsWith('Tidak Hadir') || status.includes('(Ditolak)'))) return 'bg-red-100 text-red-800';
  if (status && (['Izin', 'Sakit', 'Cuti', 'Lainnya'].some(s => status.includes(s)))) return 'bg-blue-100 text-blue-800';
  return 'bg-gray-100 text-gray-800';
};

const handlePermissionClick = () => {
  showPermissionModal.value = true;
};

// Permission Methods
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      Swal.fire({
        icon: 'error',
        title: 'File Terlalu Besar',
        text: 'Ukuran file maksimal 2MB',
      });
      event.target.value = '';
      permissionFile.value = null;
      return;
    }
    permissionFile.value = file;
  }
};

const submitPermissionRequest = () => {
  if (!permissionReason.value.trim()) {
    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Mohon isi keterangan izin',
    });
    return;
  }

  submittingPermission.value = true;

  const formData = new FormData();
  formData.append('jenis_izin', 'penuh');
  formData.append('catatan', permissionCategory.value);
  formData.append('tanggal_mulai', permissionStartDate.value);
  formData.append('tanggal_selesai', permissionEndDate.value);
  formData.append('keterangan', permissionReason.value);
  
  if (permissionFile.value) {
    formData.append('file', permissionFile.value);
  }

  router.post(route('admin.presensi.permission'), formData, {
    onSuccess: (page) => {
      submittingPermission.value = false;
      
      // Check if there's an error flash message
      if (page.props.flash && page.props.flash.error) {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: page.props.flash.error,
          confirmButtonColor: '#dc2626'
        });
        return;
      }
      
      // If no error, show success
      showPermissionModal.value = false;
      permissionReason.value = '';
      permissionCategory.value = '';
      permissionFile.value = null;
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Pengajuan izin berhasil dikirim',
        confirmButtonColor: '#2563eb'
      });
    },
    onError: (errors) => {
      submittingPermission.value = false;

      if (errors.message) {
        Swal.fire({
          icon: 'warning',
          title: 'Pengajuan Ditolak',
          text: errors.message,
          confirmButtonColor: '#d33'
        });
      }
    }
  });
};
</script>

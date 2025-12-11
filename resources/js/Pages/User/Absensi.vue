<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 overflow-x-hidden">
    <!-- Sidebar -->
     <Sidebar 
      ref="sidebarRef"
      :sidebar-open="sidebarOpen"
      @update:sidebarOpen="sidebarOpen = $event"
      @toggle-collapse="handleSidebarCollapse"
    />
    
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
      <!-- Header -->
      <Header 
        :user="user"
        :sidebar-collapsed="sidebarCollapsed"
        current-page="Absensi Harian" 
        mobile-breadcrumb="Absensi"
        @toggle-sidebar="toggleSidebar"
      />
      
      <!-- Main Content -->
     <main class="pt-8 p-4 sm:p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Page Title -->
          <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Absensi Harian</h1>
            <div class="flex items-center mt-2 text-gray-600">
              <CalendarIcon class="w-5 h-5 mr-2" />
              <span>{{ currentDate }}</span>
              <span class="mx-2">|</span>
              <ClockIcon class="w-5 h-5 mr-2" />
              <span class="font-mono font-bold text-lg text-[#dc2626]">{{ currentTime }}</span>
            </div>
          </div>

          <!-- Success/Error Messages -->
          <div v-if="$page.props.flash && $page.props.flash.success" class="mb-6 p-4 bg-green-100 text-green-800 rounded-lg">
            {{ $page.props.flash.success }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.error" class="mb-6 p-4 bg-red-100 text-red-800 rounded-lg">
            {{ $page.props.flash.error }}
          </div>
          <div v-if="$page.props.flash && $page.props.flash.prompt_keterangan" class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
            {{ $page.props.flash.prompt_keterangan }}
          </div>
          
          <!-- Cards Container -->
          <div class="flex flex-col lg:flex-row gap-6 mb-6">
            <!-- Attendance Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl flex-1">
              <div class="flex flex-col h-full">
                <div class="flex items-center mb-4">
                  <div class="p-3 rounded-xl bg-red-50 mr-4">
                    <ClockIcon class="w-6 h-6 text-[#dc2626]" />
                  </div>
                  <div>
                    <h2 class="text-xl font-semibold text-gray-900">Absensi</h2>
                    <p class="text-gray-600 text-sm">{{ getAttendanceCardTitle }}</p>
                  </div>
                </div>
                
                <div class="mb-4 flex-1">
                  <p class="text-gray-600 text-sm">
                    {{ getAttendanceCardDescription }}
                  </p>
                </div>
                
                <div v-if="hasCheckedIn" class="mb-4 p-3 bg-gray-50 rounded-lg">
                  <div class="text-sm text-gray-500">Check-in:</div>
                  <div class="font-medium">{{ todayAttendance.waktu_masuk }}</div>
                </div>
                
                <!-- Attendance Warnings Section -->
                <div class="mb-4 space-y-3">
                  <!-- Location Warning -->
                  <div v-if="currentLocation && !isWithinOfficeRadius && !locationValidationDisabled" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                      <MapPinIcon class="h-4 w-4 text-red-600 mt-0.5 mr-2 flex-shrink-0" />
                      <div class="text-sm text-red-700">
                        <span class="font-medium">Peringatan:</span> Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.
                      </div>
                    </div>
                  </div>
                  
                  <!-- Cutoff Time Warning -->
                  <div v-if="!isValidCheckInTime" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start">
                      <AlertTriangleIcon class="h-4 w-4 text-red-600 mt-0.5 mr-2 flex-shrink-0" />
                      <div class="text-sm text-red-700">
                        <span class="font-medium">Batas Waktu Check-in Telah Berakhir:</span> 
                        Anda tidak dapat melakukan absensi karena sudah melewati batas waktu yang ditentukan ({{ props.systemSettings?.cutoff_time || '10:00' }}).
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="mt-auto">
                  <!-- Late arrival reason input - shown when user is late and still within check-in time -->
                  <div v-if="isLateArrival && !hasCheckedIn && isValidCheckInTime" class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="late_reason">
                      Keterangan Keterlambatan
                    </label>
                    <textarea 
                      v-model="lateArrivalReason" 
                      id="late_reason"
                      class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm" 
                      rows="3" 
                      placeholder="Mohon berikan alasan keterlambatan Anda..."
                    ></textarea>
                    <p class="text-xs text-gray-500 mt-1">Harap isi keterangan keterlambatan Anda sebelum melakukan check-in.</p>
                  </div>
                  
                  <button
                    :disabled="isAttendanceButtonDisabled"
                    @click="handleAttendanceAction"
                    class="w-full py-4 rounded-xl font-semibold text-white transition-all duration-200 flex items-center justify-center transform hover:scale-[1.02]"
                    :class="getAttendanceButtonClass"
                  >
                    <Loader2Icon v-if="gettingLocation && !isAttendanceButtonDisabled" class="w-5 h-5 mr-2 animate-spin" />
                    {{ getAttendanceButtonText }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Leave Request Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl flex-1">
              <div class="flex flex-col h-full">
                <div class="flex items-center mb-4">
                  <div class="p-3 rounded-xl bg-blue-50 mr-4">
                    <FileTextIcon class="w-6 h-6 text-blue-600" />
                  </div>
                  <div>
                    <h2 class="text-xl font-semibold text-gray-900">Pengajuan Izin</h2>
                    <p class="text-gray-600 text-sm">Ajukan izin jika tidak dapat hadir</p>
                  </div>
                </div>
                
                <div class="mb-4 flex-1">
                  <div v-if="todayIzin" class="p-4 rounded-xl border mb-3" :class="getIzinStatusClass(todayIzin)">
                      <h3 class="font-bold text-sm uppercase mb-1 flex items-center">
                          <InfoIcon class="w-4 h-4 mr-2" />
                          {{ getIzinStatusTitle(todayIzin) }}
                      </h3>
                      <p class="text-sm font-medium">{{ getIzinStatusText(todayIzin) }}</p>
                      <p v-if="todayIzin.keterangan" class="text-xs mt-2 opacity-75">"{{ todayIzin.keterangan }}"</p>
                  </div>
                  <p v-else class="text-gray-600 text-sm">Ajukan izin jika tidak dapat hadir hari ini:</p>
                </div>
                
                <div class="mt-auto">
                  <button 
                    @click="showPermissionModal = true"
                    class="w-full py-4 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-colors duration-200 font-semibold flex items-center justify-center"
                  >
                    <PlusIcon class="w-5 h-5 mr-2" />
                    Ajukan Izin
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Location Information Card -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 transition-all duration-300 hover:shadow-xl">
            <div class="flex items-center mb-4">
              <div class="p-3 rounded-xl bg-green-50 mr-4">
                <MapPinIcon class="w-6 h-6 text-green-600" />
              </div>
              <div>
                <h2 class="text-xl font-semibold text-gray-900">Informasi Lokasi</h2>
                <p class="text-gray-600 text-sm">Detail lokasi kantor dan posisi Anda</p>
              </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Location Information -->
              <div class="space-y-3">
                <div class="px-4">
                  <div class="text-sm text-gray-700">Lokasi Kantor :</div>
                  <div class="mt-1 text-gray-900 font-medium">
                    {{ officeLocation.lat }}, {{ officeLocation.lng }}
                  </div>
                </div>
                
                <div class="px-4">
                  <div class="text-sm text-gray-700">Radius Presensi :</div>
                  <div class="mt-1 text-gray-900 font-medium">
                    {{ attendanceRadius }} meter
                  </div>
                </div>
                
                <div v-if="currentLocation" class="px-4">
                  <div class="text-sm text-gray-700">Lokasi Anda :</div>
                  <div class="mt-1 text-gray-900 font-medium">
                    {{ currentLocation.lat }}, {{ currentLocation.lng }}
                  </div>
                  <div class="mt-2 text-sm" :class="isWithinOfficeRadius ? 'text-green-600' : 'text-red-600'">
                    <CheckCircleIcon v-if="isWithinOfficeRadius" class="h-4 w-4 inline mr-1" />
                    <XCircleIcon v-else class="h-4 w-4 inline mr-1" />
                    {{ isWithinOfficeRadius ? 'Dalam radius kantor' : 'Diluar radius kantor' }}
                  </div>
                </div>
                
                <!-- Toggle Button for Radius Validation -->
                <div class="mt-4">
                  <button
                    @click="toggleLocationValidation"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center"
                    :class="locationValidationDisabled 
                      ? 'bg-green-100 text-green-700 hover:bg-green-200' 
                      : 'bg-red-100 text-red-700 hover:bg-red-200'"
                  >
                    <ToggleLeftIcon v-if="locationValidationDisabled" class="w-4 h-4 mr-2" />
                    <ToggleRightIcon v-else class="w-4 h-4 mr-2" />
                    {{ locationValidationDisabled ? 'Aktifkan Radius' : 'Nonaktifkan Radius' }}
                  </button>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ locationValidationDisabled 
                      ? 'Radius validasi dinonaktifkan' 
                      : 'Radius validasi diaktifkan' }}
                  </p>
                </div>
              </div>
              
              <!-- Map Container -->
              <div class="bg-gray-50 rounded-xl p-4 flex items-center justify-center">
                <div ref="mapContainer" class="w-full h-64 rounded-lg overflow-hidden bg-white border border-gray-200">
                  <!-- Map will be initialized here -->
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  
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
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
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
                class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
                required
              />
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_category">
              Kategori Izin
            </label>
            <select
              id="permission_category"
              v-model="permissionCategory"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
              required
            >
              <option value="" disabled>Pilih Kategori</option>
              <option value="Izin">Izin</option>
              <option value="Sakit">Sakit</option>
              <option value="Cuti">Cuti</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2" for="permission_reason">
              Keterangan Izin
            </label>
            <textarea
              id="permission_reason"
              v-model="permissionReason"
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
              rows="4"
              placeholder="Jelaskan alasan izin Anda..."
              required
            ></textarea>
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
              class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-[#dc2626] focus:border-[#dc2626] transition-colors duration-200 shadow-sm"
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
              class="flex-1 py-3 bg-[#dc2626] text-white rounded-xl hover:bg-[#b91c1c] transition-colors duration-200 flex items-center justify-center"
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
import Header from '@/Components/Header.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import Swal from 'sweetalert2';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { 
  CheckCircleIcon, 
  InfoIcon, 
  BanIcon, 
  ClockIcon, 
  AlertTriangleIcon, 
  MapPinIcon, 
  BuildingIcon,
  XIcon,
  XCircleIcon,
  AlertCircleIcon,
  LogInIcon,
  LogOutIcon,
  FileTextIcon,
  PlusIcon,
  Loader2Icon,
  CheckIcon,
  ToggleLeftIcon,
  ToggleRightIcon,
  CalendarIcon
} from 'lucide-vue-next';

const props = defineProps({
    user: Object,
    todayAttendance: Object,
    todayIzin: Object,
    systemSettings: Object,
    testingModeDisabled: {
      type: Boolean,
      default: false
    }
});

// Office location with fallback values
const officeLocation = {
  lat: props.systemSettings?.location_latitude || 0.5240005,
  lng: props.systemSettings?.location_longitude || 123.0604752
};

// Attendance radius with fallback value
const attendanceRadius = props.systemSettings?.location_radius || 100;

const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);
const gettingLocation = ref(false);
const currentLocation = ref(null);
const autoLocationDetected = ref(false);
const mapLoaded = ref(false);
const mapContainer = ref(null);

// Testing mode state - initialize from props
const locationValidationDisabled = ref(props.testingModeDisabled || false);

// Modal states
const showPermissionModal = ref(false);
const lateArrivalReason = ref('');
const permissionReason = ref('');
const permissionStartDate = ref(new Date().toISOString().split('T')[0]);
const permissionEndDate = ref(new Date().toISOString().split('T')[0]);
const permissionCategory = ref('');
const permissionFile = ref(null);
const submittingPermission = ref(false);

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

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));

// Update time every second
let timeInterval;
onMounted(() => {
  // Initialize map
  initMap();
  
  // Always auto-detect location when page loads
  setTimeout(() => {
    getCurrentLocation();
  }, 1000);
  
  // Start clock
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }, 1000);
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
const hasCheckedIn = computed(() => {
  return props.todayAttendance && props.todayAttendance.waktu_masuk;
});



// Computed properties for unified attendance card
const getAttendanceCardTitle = computed(() => {
  if (hasCheckedIn.value) {
    return 'Sudah Check-in';
  } else {
    return 'Belum Absen';
  }
});

const getAttendanceCardDescription = computed(() => {
  if (hasCheckedIn.value) {
    return 'Anda sudah melakukan check-in hari ini.';
  } else {
    return 'Silakan lakukan absensi hari ini.';
  }
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

// Check if current time is after cutoff time
const isAfterCutoff = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const cutoffTime = props.systemSettings?.cutoff_time || '10:00:00';
  
  // User is considered absent if current time is after cutoff time
  return currentTime > cutoffTime;
});

// Check if current time is within valid check-in window (before cutoff time)
const isValidCheckInTime = computed(() => {
  if (!props.systemSettings) return true;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const cutoffTime = props.systemSettings?.cutoff_time || '10:00:00';
  
  // Users can check in before cutoff time
  return currentTime <= cutoffTime;
});

// Check if current time is before start time
const isBeforeStartTime = computed(() => {
  if (!props.systemSettings) return false;
  
  const now = new Date();
  const currentTime = now.toTimeString().substring(0, 8); // HH:MM:SS format
  const startTime = props.systemSettings?.presensi_start_time || '06:00:00';
  
  return currentTime < startTime;
});

const getAttendanceButtonText = computed(() => {
  return 'Check-in';
});

const isAttendanceButtonDisabled = computed(() => {
  // Disable if user has full leave
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    return true;
  }
  
  // Disable if user has valid izin
  if (props.todayAttendance && props.todayAttendance.status === 'Izin (Valid)') {
    return true;
  }
  
  // Disable if already checked in
  if (hasCheckedIn.value) {
    return true;
  }
  
  return false;
});

const getAttendanceButtonClass = computed(() => {
  if (isAttendanceButtonDisabled.value) {
    return 'bg-gray-100 text-gray-400 cursor-not-allowed';
  } else {
    // Check-in button
    return 'bg-[#dc2626] hover:bg-[#b91c1c]';
  }
});

// Handle attendance action (check-in only)
const handleAttendanceAction = () => {
  checkIn();
};

// Check if current location is within office radius
const isWithinOfficeRadius = computed(() => {
  // If location validation is disabled for testing, always return true
  if (locationValidationDisabled.value) return true;
  
  if (!currentLocation.value || !props.systemSettings) return true;
  
  // Ensure we have valid coordinates
  const officeLat = props.systemSettings?.location_latitude;
  const officeLng = props.systemSettings?.location_longitude;
  
  if (officeLat === undefined || officeLng === undefined) return true;
  
  const lat1 = currentLocation.value.lat;
  const lng1 = currentLocation.value.lng;
  const lat2 = officeLat;
  const lng2 = officeLng;
  const radius = props.systemSettings?.location_radius || 100;
  
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
  
  return distance <= radius; // Use calculated radius
});

const getIzinStatusTitle = (izin) => {
  if (izin.status === 'approved') return 'Izin Disetujui';
  if (izin.status === 'rejected') return 'Izin Ditolak';
  return 'Menunggu Persetujuan';
};

const getIzinStatusText = (izin) => {
    if (izin.status === 'approved') return 'Pengajuan izin Anda telah disetujui.';
    if (izin.status === 'rejected') return 'Maaf, pengajuan izin Anda ditolak.';
    return 'Pengajuan izin Anda sedang diproses.';
};

const getIzinStatusClass = (izin) => {
    if (izin.status === 'approved') return 'bg-green-50 border-green-200 text-green-800';
    if (izin.status === 'rejected') return 'bg-red-50 border-red-200 text-red-800';
    return 'bg-yellow-50 border-yellow-200 text-yellow-800';
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
        autoLocationDetected.value = true;
        
        // Show success message only when manually triggered or on initial load
        // Skip showing message on automatic check-in to avoid interrupting user
        if (!hasCheckedIn.value) {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Lokasi Anda berhasil dideteksi.',
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
          });
        }
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

// Automatically get location when page loads - MOVED TO TOP
// onMounted(() => {
//   // Initialize map
//   initMap();
//   
//   // Always auto-detect location when page loads
//   // Small delay to ensure page is fully loaded
//   setTimeout(() => {
//     getCurrentLocation();
//   }, 1000);
// });

// Initialize map
const initMap = () => {
  // Small delay to ensure DOM is ready
  setTimeout(() => {
    if (mapContainer.value) {
      try {
        // Create map instance
        const map = L.map(mapContainer.value).setView([officeLocation.lat, officeLocation.lng], 15);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add marker for office location
        const marker = L.marker([officeLocation.lat, officeLocation.lng]).addTo(map);
        
        // Add circle for attendance radius
        const circle = L.circle([officeLocation.lat, officeLocation.lng], {
          color: '#dc2626',
          fillColor: '#dc2626',
          fillOpacity: 0.2,
          radius: attendanceRadius
        }).addTo(map);
        
        // Add popup to marker
        marker.bindPopup("Kantor POLDA TIK").openPopup();
        
        // Set map as loaded
        mapLoaded.value = true;
      } catch (error) {
        console.error('Error initializing map:', error);
        // Set map as loaded even if there's an error to hide the loading spinner
        mapLoaded.value = true;
      }
    }
  }, 100);
};

// Check-in function with automatic location
const checkIn = () => {
  // Check if user has full leave
  if (props.todayIzin && props.todayIzin.jenis_izin === 'penuh') {
    Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Anda sedang dalam status izin penuh. Tidak perlu melakukan absensi hari ini.',
    });
    return;
  }
  
  // Check if user is within office radius (unless disabled for testing)
  if (currentLocation.value && !isWithinOfficeRadius.value && !locationValidationDisabled.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Lokasi Anda berada di luar radius kantor. Absensi tidak dapat diproses.',
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
  
  // Check if user is trying to check in after cutoff time
  if (!isValidCheckInTime.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Batas waktu check-in telah berakhir. Anda tidak dapat melakukan absensi hari ini.',
      showCancelButton: false,
      confirmButtonText: 'OK'
    });
    return;
  }
  
  // If user is late and hasn't provided a reason, show error
  if (isLateArrival.value && !lateArrivalReason.value.trim() && !hasCheckedIn.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Peringatan!',
      text: 'Anda terlambat. Mohon isi keterangan keterlambatan sebelum melakukan check-in.',
    });
    return;
  }
  
  performCheckIn();
};

// Perform check-in with current location
const performCheckIn = () => {
  // Always get fresh location for check-in
  gettingLocation.value = true;
  
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        gettingLocation.value = false;
        currentLocation.value = {
          lat: position.coords.latitude,
          lng: position.coords.longitude
        };
        autoLocationDetected.value = true;
        
        // Send check-in request with the newly obtained location
        sendCheckInRequest(position.coords.latitude, position.coords.longitude);
      },
      (error) => {
        gettingLocation.value = false;
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Gagal mendapatkan lokasi: ' + error.message + '. Silakan coba lagi.',
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

// Send check-in request to server
const sendCheckInRequest = (lat, lng) => {
  const data = {
    lat: lat,
    lng: lng
  };
  
  // Add late arrival reason if provided
  if (lateArrivalReason.value.trim()) {
    data.keterangan = lateArrivalReason.value.trim();
  }
  
  router.post(route('user.absensi.checkin'), data, {
    onStart: () => {
      gettingLocation.value = true;
    },
    onFinish: () => {
      gettingLocation.value = false;
      lateArrivalReason.value = ''; // Clear the late arrival reason after submission
    },
    onSuccess: () => {
      // Page props are automatically updated by Inertia
      // No need to reload manually as it might clear flash messages
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat melakukan check-in. Silakan coba lagi.',
      });
    }
  });
};

// Handle file change for permission request
const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    // Validate file type and size
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    const maxSize = 2 * 1024 * 1024; // 2MB in bytes
    
    if (!allowedTypes.includes(file.type)) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'File harus berupa PDF, JPG, JPEG, atau PNG.',
      });
      event.target.value = ''; // Clear the input
      return;
    }
    
    if (file.size > maxSize) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Ukuran file maksimal 2MB.',
      });
      event.target.value = ''; // Clear the input
      return;
    }
    
    permissionFile.value = file;
  }
};

// Submit permission request
const submitPermissionRequest = () => {
  submittingPermission.value = true;
  
  const formData = new FormData();
  formData.append('keterangan', permissionReason.value);
  formData.append('jenis_izin', 'penuh'); // Default to 'penuh'
  formData.append('catatan', permissionCategory.value); // Send category as catatan
  formData.append('tanggal_mulai', permissionStartDate.value);
  formData.append('tanggal_selesai', permissionEndDate.value);
  
  // Add file if provided
  if (permissionFile.value) {
    formData.append('file', permissionFile.value);
  }
  
  router.post(route('user.absensi.permission'), formData, {
    onSuccess: () => {
      submittingPermission.value = false;
      showPermissionModal.value = false;
      permissionReason.value = '';
      permissionCategory.value = '';
      permissionStartDate.value = new Date().toISOString().split('T')[0];
      permissionEndDate.value = new Date().toISOString().split('T')[0];
      permissionFile.value = null;
      
      // Show success toast message
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Pengajuan izin berhasil diajukan dan sedang menunggu persetujuan.',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
      });
      
      // Reload the page to update the izin status
      router.reload();
    },
    onError: (errors) => {
      submittingPermission.value = false;
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat mengajukan izin. Silakan coba lagi.',
      });
    }
  });
};

// Toggle location validation for testing
const toggleLocationValidation = () => {
  // Call the API to toggle the testing mode
  router.post(route('api.system-settings.toggle-test'), {}, {
    onSuccess: (response) => {
      // Update the local state based on the response
      if (response.testing_mode_disabled !== undefined) {
        locationValidationDisabled.value = response.testing_mode_disabled;
      }
      
      Swal.fire({
        icon: 'success',
        title: locationValidationDisabled.value ? 'Mode Testing Diaktifkan' : 'Mode Testing Dinonaktifkan',
        text: locationValidationDisabled.value 
          ? 'Validasi radius lokasi telah dinonaktifkan untuk testing.' 
          : 'Validasi radius lokasi telah diaktifkan kembali.',
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Gagal mengubah mode testing.',
      });
    }
  });
};
</script>
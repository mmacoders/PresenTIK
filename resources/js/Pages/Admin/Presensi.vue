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

        <!-- Presensi Card -->
        <div class="bg-white rounded-2xl shadow-lg border-t-4 border-red-600 p-8">
          <div class="flex items-center mb-6">
            <div class="p-4 rounded-xl bg-red-50 mr-4">
              <ClockIcon class="w-8 h-8 text-red-600" />
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Presensi Admin</h2>
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

          <!-- Info -->
          <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
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
  </AdminLayout>
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
  ClipboardListIcon
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
  todayAttendance: Object,
  recentAttendance: Array,
});

const isSubmitting = ref(false);
const currentTime = ref(new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }));

// Update time every second
let timeInterval;
onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }, 1000);
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
      isSubmitting.value = true;
      
      router.post(route('admin.presensi.checkin'), {}, {
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

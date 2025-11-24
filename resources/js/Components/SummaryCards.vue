<script setup>
import { computed } from 'vue';
import { LogInIcon, Loader2Icon } from 'lucide-vue-next';

const props = defineProps({
    currentDate: String,
    currentTime: String,
    todayAttendance: Object,
    checkingIn: Boolean,
    canCheckIn: Boolean
});

const emit = defineEmits(['checkIn']);

const handleCheckIn = () => {
    emit('checkIn');
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Date Card -->
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-sm text-gray-500 mb-2">Tanggal Hari Ini</div>
            <div class="text-2xl font-bold text-gray-900">{{ currentDate }}</div>
        </div>

        <!-- Time Card -->
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="text-sm text-gray-500 mb-2">Waktu Sekarang</div>
            <div class="text-2xl font-bold text-gray-900">{{ currentTime }}</div>
        </div>
    </div>

    <!-- Check-in Button -->
    <div v-if="!todayAttendance" class="bg-white rounded-2xl shadow p-5 mb-6">
        <button
            @click="handleCheckIn"
            :disabled="!canCheckIn || checkingIn"
            class="w-full py-3 px-6 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl shadow-md flex items-center justify-center gap-2 transition duration-150"
        >
            <Loader2Icon v-if="checkingIn" class="h-5 w-5 animate-spin" />
            <LogInIcon v-else class="h-5 w-5" />
            <span v-if="checkingIn">Memproses...</span>
            <span v-else>Absen Masuk</span>
        </button>
        
        <div v-if="!canCheckIn && !checkingIn" class="mt-4 text-center text-sm text-red-600">
            Anda berada di luar area kantor
        </div>
    </div>

    <!-- Attendance Status -->
    <div v-else class="bg-white rounded-2xl shadow p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Status Presensi</h3>
            <span class="px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                Hadir
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="text-sm text-gray-500">Waktu Masuk</div>
                <div class="text-lg font-semibold text-gray-900">
                    {{ todayAttendance.waktu_masuk }}
                </div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Lokasi Masuk</div>
                <div class="text-lg font-semibold text-gray-900">
                    {{ todayAttendance.status_lokasi }}
                </div>
            </div>
        </div>
    </div>
</template>
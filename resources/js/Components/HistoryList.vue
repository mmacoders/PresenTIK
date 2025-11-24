<script setup>
import { computed } from 'vue';
import { ClockIcon, CheckCircleIcon, FileTextIcon } from 'lucide-vue-next';

const props = defineProps({
    attendanceHistory: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
};

const formatTime = (timeString) => {
    if (!timeString) return '-';
    return timeString.substring(0, 5);
};

const getStatusClass = (status) => {
    switch (status) {
        case 'hadir':
            return 'bg-green-100 text-green-800';
        case 'izin':
            return 'bg-blue-100 text-blue-800';
        case 'alpha':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusText = (status) => {
    switch (status) {
        case 'hadir':
            return 'Hadir';
        case 'izin':
            return 'Izin';
        case 'alpha':
            return 'Alpha';
        default:
            return 'Tidak Diketahui';
    }
};
</script>

<template>
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Presensi</h3>
        
        <div v-if="attendanceHistory && attendanceHistory.length > 0">
            <div 
                v-for="(attendance, index) in attendanceHistory" 
                :key="index"
                class="bg-gray-50 rounded-xl p-4 shadow-sm mb-3 last:mb-0"
            >
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center">
                            <ClockIcon class="h-4 w-4 text-gray-500 mr-1" />
                            <span class="text-sm font-medium text-gray-900">
                                {{ formatDate(attendance.tanggal) }}
                            </span>
                        </div>
                        <div class="text-lg font-semibold text-gray-900 mt-1">
                            {{ formatTime(attendance.waktu_masuk) }}
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-end">
                        <span 
                            class="px-2 py-1 rounded-full text-xs font-medium" 
                            :class="getStatusClass(attendance.status)"
                        >
                            {{ getStatusText(attendance.status) }}
                        </span>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ attendance.keterangan || '-' }}
                        </div>
                    </div>
                </div>
                
                <div v-if="attendance.status === 'izin'" class="mt-2 flex items-center text-xs text-blue-600">
                    <FileTextIcon class="h-3 w-3 mr-1" />
                    <span>Izin diajukan</span>
                </div>
            </div>
        </div>
        
        <div v-else class="text-center py-8 text-gray-500">
            <ClockIcon class="h-12 w-12 mx-auto text-gray-300 mb-2" />
            <p>Belum ada riwayat presensi</p>
        </div>
    </div>
</template>
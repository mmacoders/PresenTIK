<script setup>
import { computed } from 'vue';
import { MapPinIcon, BuildingIcon, CheckCircleIcon, XCircleIcon } from 'lucide-vue-next';

const props = defineProps({
    officeLocation: Object,
    userLocation: Object,
    systemSettings: Object,
    isWithinRadius: Boolean
});

const radius = computed(() => {
    return props.systemSettings?.location_radius || 100;
});
</script>

<template>
    <div class="bg-white rounded-2xl shadow p-5 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Lokasi</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center">
                    <MapPinIcon class="h-5 w-5 text-gray-500 mr-2" />
                    <div class="text-sm text-gray-500">Lokasi Kantor</div>
                </div>
                <div class="mt-2 text-gray-900">
                    {{ officeLocation?.lat }}, {{ officeLocation?.lng }}
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-xl p-4">
                <div class="flex items-center">
                    <BuildingIcon class="h-5 w-5 text-gray-500 mr-2" />
                    <div class="text-sm text-gray-500">Radius Presensi</div>
                </div>
                <div class="mt-2 text-gray-900">
                    {{ radius }} meter
                </div>
            </div>
        </div>
        
        <div v-if="userLocation" class="bg-blue-50 rounded-xl p-4">
            <div class="flex items-center">
                <MapPinIcon class="h-5 w-5 text-blue-500 mr-2" />
                <div class="text-sm text-blue-700">Lokasi Anda</div>
            </div>
            <div class="mt-2 text-blue-900">
                {{ userLocation.lat }}, {{ userLocation.lng }}
            </div>
            <div class="mt-2 text-sm" :class="isWithinRadius ? 'text-green-600' : 'text-red-600'">
                <CheckCircleIcon v-if="isWithinRadius" class="h-4 w-4 inline mr-1" />
                <XCircleIcon v-else class="h-4 w-4 inline mr-1" />
                {{ isWithinRadius ? 'Dalam radius kantor' : 'Diluar radius kantor' }}
            </div>
        </div>
        
        <!-- Map Container -->
        <div class="mt-6">
            <h4 class="text-md font-medium text-gray-900 mb-2">Peta Lokasi</h4>
            <div class="w-full h-64 rounded-xl overflow-hidden bg-gray-100 flex items-center justify-center">
                <div class="text-gray-500 text-center">
                    <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
                    <p>Memuat peta...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loader {
    border-top-color: #dc2626;
    animation: spinner 1s linear infinite;
}

@keyframes spinner {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
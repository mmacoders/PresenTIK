<template>
    <div v-if="show" class="bg-black/40 backdrop-blur-sm fixed inset-0 flex items-center justify-center z-50 p-4 transition-all duration-300 ease-in-out">
        <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md transform transition-all duration-300 ease-in-out scale-95 opacity-0" 
             :class="{ 'scale-100 opacity-100': show }">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <CheckCircleIcon class="w-5 h-5 text-[#dc2626]" />
                    <h2 class="text-lg font-semibold text-gray-900">Verifikasi Permintaan Izin</h2>
                </div>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                    <XIcon class="w-5 h-5" />
                </button>
            </div>
            
            <div v-if="izin" class="mb-6">
                <!-- Informasi Permintaan Izin (Read-only) -->
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 mb-4 space-y-2 text-sm">
                    <div><span class="font-medium text-gray-700">Pegawai:</span> {{ izin.user.name }}</div>
                    <div><span class="font-medium text-gray-700">Tanggal:</span> {{ formatDateRange(izin.tanggal_mulai, izin.tanggal_selesai) }}</div>
                    <div><span class="font-medium text-gray-700">Jenis Izin:</span> {{ izin.jenis_izin === 'penuh' ? 'Izin Penuh' : 'Izin Parsial' }}</div>
                    <div><span class="font-medium text-gray-700">Keterangan:</span> {{ izin.keterangan || '-' }}</div>
                </div>
                
                <!-- Form Keputusan (Admin Action) -->
                <form @submit.prevent="submitForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keputusan</label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    v-model="form.status" 
                                    value="approved" 
                                    class="text-green-600 focus:ring-green-600" 
                                    :disabled="form.processing"
                                />
                                <span class="text-gray-800 font-medium">Terima</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    v-model="form.status" 
                                    value="rejected" 
                                    class="text-[#dc2626] focus:ring-[#dc2626]" 
                                    :disabled="form.processing"
                                />
                                <span class="text-gray-800 font-medium">Tolak</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Catatan Tambahan -->
                    <div v-if="form.status === 'rejected'" class="mt-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Penolakan</label>
                        <textarea 
                            v-model="form.catatan"
                            placeholder="Tuliskan alasan penolakan izin..."
                            class="w-full border-gray-300 focus:ring-[#dc2626] focus:border-[#dc2626] rounded-lg text-gray-800 h-24 resize-none"
                            :disabled="form.processing"
                        ></textarea>
                    </div>
                    
                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button 
                            type="button" 
                            @click="closeModal"
                            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
                            :disabled="form.processing"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-[#dc2626] text-white font-medium hover:bg-red-700 transition flex items-center justify-center"
                            :disabled="form.processing || !form.status"
                        >
                            <Spinner v-if="form.processing" class="w-4 h-4 mr-2" />
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { CheckCircleIcon, XIcon } from 'lucide-vue-next';
import Spinner from '@/Components/Spinner.vue';

const props = defineProps({
    show: Boolean,
    izin: Object,
});

const emit = defineEmits(['close', 'updated']);

const form = useForm({
    status: '',
    catatan: '',
});

const formatDateRange = (startDate, endDate) => {
    if (startDate === endDate) {
        return new Date(startDate).toLocaleDateString('id-ID');
    }
    return `${new Date(startDate).toLocaleDateString('id-ID')} - ${new Date(endDate).toLocaleDateString('id-ID')}`;
};

const submitForm = () => {
    router.patch(route('admin.izin.update', props.izin.id), form.data(), {
        onSuccess: () => {
            closeModal();
            emit('updated');
        },
    });
};

const closeModal = () => {
    form.reset();
    emit('close');
};

// Reset form when modal is opened
watch(() => props.show, (newVal) => {
    if (newVal) {
        form.status = '';
        form.catatan = '';
    }
});

// Handle escape key to close modal
const handleEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        closeModal();
    }
};

document.addEventListener('keydown', handleEscape);

// Cleanup event listener
// This would typically be handled in a lifecycle hook in the parent component
</script>
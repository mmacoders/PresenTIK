<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { 
  XIcon, 
  FileTextIcon, 
  CalendarIcon, 
  UserIcon,
  ChevronDownIcon,
  InfoIcon
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    show: Boolean,
    onClose: Function
});

const emit = defineEmits(['close']);

const form = ref({
    jenis_izin: 'kedukaan',
    hubungan_keluarga: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    keperluan: '',
    catatan_tambahan: '',
    dokumen: null
});

const previewUrl = ref(null);
const validationErrors = ref({});
const isSubmitting = ref(false);

const resetForm = () => {
    form.value = {
        jenis_izin: 'kedukaan',
        hubungan_keluarga: '',
        tanggal_mulai: '',
        tanggal_selesai: '',
        keperluan: '',
        catatan_tambahan: '',
        dokumen: null
    };
    previewUrl.value = null;
    validationErrors.value = {};
    isSubmitting.value = false;
};

// Watch for changes in tanggal_mulai to auto-set tanggal_selesai
watch(() => form.value.tanggal_mulai, (newVal) => {
    if (newVal && !form.value.tanggal_selesai) {
        form.value.tanggal_selesai = newVal;
    }
});

// Watch for changes in jenis_izin to reset conditional fields
watch(() => form.value.jenis_izin, (newVal) => {
    if (newVal !== 'kedukaan') {
        form.value.hubungan_keluarga = '';
    }
    if (newVal !== 'keperluan_mendesak' && newVal !== 'lainnya') {
        form.value.keperluan = '';
    }
});

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.value.dokumen = file;
        
        // Generate preview for images
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewUrl.value = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            previewUrl.value = null;
        }
    }
};

const removeFile = () => {
    form.value.dokumen = null;
    previewUrl.value = null;
    // Reset the file input
    const fileInput = document.getElementById('dokumen');
    if (fileInput) {
        fileInput.value = '';
    }
};

const validateForm = () => {
    const errors = {};
    
    // Validate jenis_izin
    if (!form.value.jenis_izin) {
        errors.jenis_izin = 'Jenis izin wajib dipilih';
    }
    
    // Validate hubungan_keluarga for kedukaan
    if (form.value.jenis_izin === 'kedukaan' && !form.value.hubungan_keluarga.trim()) {
        errors.hubungan_keluarga = 'Hubungan keluarga wajib diisi';
    }
    
    // Validate keperluan for keperluan_mendesak and lainnya
    if ((form.value.jenis_izin === 'keperluan_mendesak' || form.value.jenis_izin === 'lainnya') && !form.value.keperluan.trim()) {
        errors.keperluan = 'Deskripsi keperluan wajib diisi';
    }
    
    // Validate tanggal_mulai
    if (!form.value.tanggal_mulai) {
        errors.tanggal_mulai = 'Tanggal mulai wajib diisi';
    }
    
    // Validate tanggal_selesai
    if (!form.value.tanggal_selesai) {
        errors.tanggal_selesai = 'Tanggal selesai wajib diisi';
    }
    
    // Validate tanggal logic
    if (form.value.tanggal_mulai && form.value.tanggal_selesai) {
        const startDate = new Date(form.value.tanggal_mulai);
        const endDate = new Date(form.value.tanggal_selesai);
        if (endDate < startDate) {
            errors.tanggal_selesai = 'Tanggal selesai tidak boleh sebelum tanggal mulai';
        }
    }
    
    // Validate dokumen
    if (!form.value.dokumen) {
        errors.dokumen = 'Dokumen pendukung wajib diupload';
    }
    
    validationErrors.value = errors;
    return Object.keys(errors).length === 0;
};

const submitForm = () => {
    if (isSubmitting.value) return;
    
    console.log('Form submission started');
    console.log('Form data:', form.value);
    
    if (!validateForm()) {
        console.log('Form validation failed');
        return;
    }
    
    console.log('Form validation passed');
    
    isSubmitting.value = true;
    
    const formData = new FormData();
    formData.append('jenis_izin', form.value.jenis_izin);
    
    if (form.value.jenis_izin === 'kedukaan') {
        formData.append('hubungan_keluarga', form.value.hubungan_keluarga);
    }
    
    if (form.value.jenis_izin === 'keperluan_mendesak' || form.value.jenis_izin === 'lainnya') {
        formData.append('keperluan', form.value.keperluan);
    }
    
    formData.append('tanggal_mulai', form.value.tanggal_mulai);
    formData.append('tanggal_selesai', form.value.tanggal_selesai);
    formData.append('catatan_tambahan', form.value.catatan_tambahan);
    formData.append('dokumen', form.value.dokumen);
    
    // Log formData for debugging
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
    
    router.post(route('user.izin.store'), formData, {
        onSuccess: () => {
            console.log('Form submission successful');
            resetForm();
            emit('close');
            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Pengajuan izin berhasil dikirim',
                confirmButtonColor: '#dc2626'
            });
        },
        onError: (errors) => {
            console.log('Form submission error:', errors);
            validationErrors.value = errors;
            isSubmitting.value = false;
        },
        onFinish: () => {
            console.log('Form submission finished');
            isSubmitting.value = false;
        }
    });
};

const closeModal = () => {
    resetForm();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Ajukan Izin Presensi</h3>
                    <p class="text-sm text-gray-500">Isi formulir berikut untuk mengajukan izin presensi Anda.</p>
                </div>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                    <XIcon class="h-5 w-5" />
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <form @submit.prevent="submitForm">
                    <!-- Jenis Izin -->
                    <div class="mb-6">
                        <InputLabel for="jenis_izin" value="Jenis Izin *" class="mb-2" />
                        <div class="relative">
                            <select
                                id="jenis_izin"
                                v-model="form.jenis_izin"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-3 pr-10 text-left shadow-sm focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 sm:text-sm"
                            >
                                <option value="kedukaan">Kedukaan</option>
                                <option value="sakit">Sakit</option>
                                <option value="keperluan_mendesak">Keperluan Mendesak</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            <ChevronDownIcon class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
                        </div>
                        <InputError :message="validationErrors.jenis_izin" class="mt-2" />
                    </div>
                    
                    <!-- Hubungan Keluarga (conditional for kedukaan) -->
                    <div v-if="form.jenis_izin === 'kedukaan'" class="mb-6">
                        <InputLabel for="hubungan_keluarga" value="Hubungan Keluarga *" class="mb-2" />
                        <TextInput
                            id="hubungan_keluarga"
                            v-model="form.hubungan_keluarga"
                            type="text"
                            class="w-full"
                            placeholder="Contoh: Ayah, Ibu, Saudara"
                        />
                        <InputError :message="validationErrors.hubungan_keluarga" class="mt-2" />
                    </div>
                    
                    <!-- Keperluan (conditional for keperluan_mendesak and lainnya) -->
                    <div v-if="form.jenis_izin === 'keperluan_mendesak' || form.jenis_izin === 'lainnya'" class="mb-6">
                        <InputLabel for="keperluan" value="Deskripsi Singkat Keperluan *" class="mb-2" />
                        <Textarea
                            id="keperluan"
                            v-model="form.keperluan"
                            class="w-full"
                            placeholder="Jelaskan keperluan Anda secara singkat"
                            rows="3"
                        />
                        <InputError :message="validationErrors.keperluan" class="mt-2" />
                    </div>
                    
                    <!-- Tanggal Range -->
                    <div class="mb-6">
                        <div class="flex justify-between mb-2">
                            <InputLabel for="tanggal_mulai" value="Tanggal Mulai *" />
                            <InputLabel for="tanggal_selesai" value="Tanggal Selesai *" />
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="relative">
                                    <CalendarIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <TextInput
                                        id="tanggal_mulai"
                                        v-model="form.tanggal_mulai"
                                        type="date"
                                        class="w-full pl-10"
                                    />
                                </div>
                                <InputError :message="validationErrors.tanggal_mulai" class="mt-2" />
                            </div>
                            <div>
                                <div class="relative">
                                    <CalendarIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-400" />
                                    <TextInput
                                        id="tanggal_selesai"
                                        v-model="form.tanggal_selesai"
                                        type="date"
                                        class="w-full pl-10"
                                    />
                                </div>
                                <InputError :message="validationErrors.tanggal_selesai" class="mt-2" />
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 flex items-center">
                            <InfoIcon class="h-3 w-3 mr-1" />
                            Jika izin hanya 1 hari, tanggal selesai otomatis mengikuti tanggal mulai
                        </div>
                    </div>
                    
                    <!-- Upload Dokumen -->
                    <div class="mb-6">
                        <InputLabel for="dokumen" value="Dokumen Pendukung *" class="mb-2" />
                        <div class="flex items-center justify-center w-full">
                            <label for="dokumen" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <FileTextIcon class="h-8 w-8 text-gray-400 mb-2" />
                                    <p class="text-sm text-gray-500">
                                        <span class="font-semibold">Klik untuk upload</span> atau drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">PDF, JPG, PNG (MAX. 5MB)</p>
                                </div>
                                <input 
                                    id="dokumen" 
                                    type="file" 
                                    class="hidden" 
                                    @change="handleFileUpload"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                />
                            </label>
                        </div>
                        <InputError :message="validationErrors.dokumen" class="mt-2" />
                    </div>
                    
                    <!-- File Preview -->
                    <div v-if="previewUrl || form.dokumen" class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <p class="text-sm text-gray-600">Preview Dokumen:</p>
                            <button 
                                type="button"
                                @click="removeFile"
                                class="text-xs text-red-600 hover:text-red-800"
                            >
                                Hapus
                            </button>
                        </div>
                        <div v-if="previewUrl" class="border rounded-lg p-2">
                            <img :src="previewUrl" alt="Preview" class="max-h-40 rounded-lg mx-auto" />
                        </div>
                        <div v-else-if="form.dokumen" class="border rounded-lg p-4 text-center">
                            <FileTextIcon class="h-12 w-12 text-gray-400 mx-auto mb-2" />
                            <p class="text-sm text-gray-600">{{ form.dokumen.name }}</p>
                            <p class="text-xs text-gray-500">{{ (form.dokumen.size / 1024 / 1024).toFixed(2) }} MB</p>
                        </div>
                    </div>
                    
                    <!-- Catatan Tambahan -->
                    <div class="mb-6">
                        <InputLabel for="catatan_tambahan" value="Catatan Tambahan (Opsional)" class="mb-2" />
                        <Textarea
                            id="catatan_tambahan"
                            v-model="form.catatan_tambahan"
                            class="w-full"
                            placeholder="Tambahkan catatan tambahan untuk HR (opsional)"
                            rows="3"
                        />
                    </div>
                </form>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end p-4 border-t bg-gray-50">
                <SecondaryButton @click="closeModal" class="mr-2">
                    Batal
                </SecondaryButton>
                <PrimaryButton 
                    type="submit"
                    :disabled="isSubmitting"
                    class="bg-red-600 hover:bg-red-700 disabled:opacity-75"
                >
                    <span v-if="isSubmitting">Mengirim...</span>
                    <span v-else>Kirim Pengajuan</span>
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
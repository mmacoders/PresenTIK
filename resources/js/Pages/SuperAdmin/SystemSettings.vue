<template>
    <div class="flex min-h-screen bg-[#F5F6FA]">
        <!-- Sidebar -->
        <SuperAdminSidebar 
          :sidebar-open="sidebarOpen"
          @update:sidebarOpen="sidebarOpen = $event"
          @toggle-collapse="handleSidebarCollapse"
        />
        
        <!-- Main Content -->
        <div class="flex-1" :class="sidebarCollapsed ? 'md:ml-20' : 'md:ml-64'">
            <!-- Header -->
            <SuperAdminHeader 
                title="Pengaturan Sistem"
                :user-profile-pic="$page.props.auth.user.profile_pict_url"
                @toggle-sidebar="toggleSidebar"
            />

            <main class="py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Success/Error Messages -->
                    <div v-if="$page.props.flash && $page.props.flash.success" class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg">
                        {{ $page.props.flash.success }}
                    </div>
                    <div v-if="$page.props.flash && $page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 rounded-lg">
                        {{ $page.props.flash.error }}
                    </div>

                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">Pengaturan Sistem</h1>
                        <p class="text-gray-600">Konfigurasi pengaturan global aplikasi absensi</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Settings Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                                <form @submit.prevent="saveSettings">
                                    <!-- Location Settings -->
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lokasi Kantor</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="location_latitude" value="Latitude" />
                                                <TextInput
                                                    id="location_latitude"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    v-model="settingsForm.location_latitude"
                                                    required
                                                />
                                                <InputError class="mt-2" :message="settingsForm.errors.location_latitude" />
                                            </div>
                                            
                                            <div>
                                                <InputLabel for="location_longitude" value="Longitude" />
                                                <TextInput
                                                    id="location_longitude"
                                                    type="text"
                                                    class="mt-1 block w-full"
                                                    v-model="settingsForm.location_longitude"
                                                    required
                                                />
                                                <InputError class="mt-2" :message="settingsForm.errors.location_longitude" />
                                            </div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <InputLabel for="location_radius" value="Radius Presensi (meter)" />
                                            <input
                                                id="location_radius"
                                                type="range"
                                                min="50"
                                                max="500"
                                                step="10"
                                                class="w-full mt-1"
                                                v-model="settingsForm.location_radius"
                                            />
                                            <div class="flex justify-between text-sm text-gray-600 mt-1">
                                                <span>50m</span>
                                                <span class="font-medium">{{ settingsForm.location_radius }}m</span>
                                                <span>500m</span>
                                            </div>
                                            <InputError class="mt-2" :message="settingsForm.errors.location_radius" />
                                        </div>
                                        
                                        <!-- Toggle for disabling location validation -->
                                        <div class="mt-4 flex items-center">
                                            <input
                                                id="disable_location_validation"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-[#C62828] shadow-sm focus:ring-[#C62828]"
                                                v-model="settingsForm.disable_location_validation"
                                            />
                                            <label for="disable_location_validation" class="ml-2 block text-sm text-gray-900">
                                                Nonaktifkan validasi lokasi (memungkinkan absensi dari lokasi mana pun)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <!-- Work Hours Settings -->
                                    <div class="mb-8">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jam Kerja</h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <InputLabel for="work_start_time" value="Jam Masuk" />
                                                <input
                                                    id="work_start_time"
                                                    type="time"
                                                    class="mt-1 block w-full border-gray-300 focus:border-[#C62828] focus:ring-[#C62828] rounded-md shadow-sm"
                                                    v-model="settingsForm.work_start_time"
                                                    required
                                                />
                                                <InputError class="mt-2" :message="settingsForm.errors.work_start_time" />
                                            </div>
                                            
                                            <div>
                                                <InputLabel for="work_end_time" value="Jam Pulang" />
                                                <input
                                                    id="work_end_time"
                                                    type="time"
                                                    class="mt-1 block w-full border-gray-300 focus:border-[#C62828] focus:ring-[#C62828] rounded-md shadow-sm"
                                                    v-model="settingsForm.work_end_time"
                                                    required
                                                />
                                                <InputError class="mt-2" :message="settingsForm.errors.work_end_time" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Save Button -->
                                    <div class="flex items-center justify-end">
                                        <PrimaryButton 
                                            :disabled="settingsForm.processing"
                                            class="bg-[#C62828] hover:bg-[#b71c1c] px-6 py-3"
                                        >
                                            <SaveIcon class="h-5 w-5 mr-2" />
                                            Simpan Pengaturan
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Map Preview -->
                        <div>
                            <MapPreview 
                                :latitude="settingsForm.location_latitude"
                                :longitude="settingsForm.location_longitude"
                                :radius="settingsForm.location_radius"
                            />
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SuperAdminSidebar from '@/Components/SuperAdminSidebar.vue';
import SuperAdminHeader from '@/Components/SuperAdminHeader.vue';
import MapPreview from '@/Components/MapPreview.vue';
import { SaveIcon } from 'lucide-vue-next';

// Props
const props = defineProps({
    settings: Object,
});

// State
const sidebarOpen = ref(true);
const sidebarCollapsed = ref(false);

// Form
const settingsForm = useForm({
    location_latitude: props.settings.location_latitude || '0.524000504793017',
    location_longitude: props.settings.location_longitude || '123.06047523547292',
    location_radius: props.settings.location_radius || '100',
    disable_location_validation: props.settings.disable_location_validation || false,
    work_start_time: props.settings.work_start_time || '08:00:00',
    work_end_time: props.settings.work_end_time || '16:00:00',
});

// Methods
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

const handleSidebarCollapse = (collapsed) => {
    sidebarCollapsed.value = collapsed;
};

// Logout function
const logout = () => {
    router.post(route('logout'));
};

const saveSettings = () => {
    settingsForm.post(route('superadmin.settings.update'), {
        onSuccess: () => {
            // Settings saved successfully
        },
    });
};
</script>
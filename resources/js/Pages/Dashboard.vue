<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { 
  UserIcon, 
  ShieldIcon, 
  CrownIcon 
} from 'lucide-vue-next';

// Get user data from the session
const user = usePage().props.auth.user;

// Determine user role and redirect URL
const getUserRoleInfo = () => {
  if (user.is_super_admin) {
    return {
      role: 'Super Admin',
      icon: CrownIcon,
      redirectUrl: '/superadmin/dashboard',
      color: 'text-purple-600',
      bgColor: 'bg-purple-100'
    };
  } else if (user.is_admin) {
    return {
      role: 'Admin',
      icon: ShieldIcon,
      redirectUrl: '/admin/dashboard',
      color: 'text-blue-600',
      bgColor: 'bg-blue-100'
    };
  } else {
    return {
      role: 'User',
      icon: UserIcon,
      redirectUrl: '/user/dashboard',
      color: 'text-green-600',
      bgColor: 'bg-green-100'
    };
  }
};

const roleInfo = getUserRoleInfo();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h1>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-lg sm:rounded-2xl overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-xl">
                    <div class="p-8 md:p-10 text-gray-900">
                        <div class="text-center">
                            <div :class="['w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6', roleInfo.bgColor]">
                                <component :is="roleInfo.icon" :class="['w-10 h-10', roleInfo.color]" />
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 mb-3">Selamat Datang, {{ user.name }}!</h2>
                            <p class="text-gray-600 mb-2">Anda masuk sebagai <span :class="['font-semibold', roleInfo.color]">{{ roleInfo.role }}</span></p>
                            <p class="text-gray-500 mt-8 max-w-md mx-auto">Sistem Manajemen Absensi POLDA TIK membantu Anda mengelola dan memantau kehadiran pegawai dengan mudah dan efisien.</p>
                            
                            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8 max-w-md mx-auto mt-8">
                                <p class="text-gray-700 mb-6">Anda akan diarahkan ke dashboard yang sesuai dengan peran Anda.</p>
                                <a 
                                    :href="roleInfo.redirectUrl"
                                    class="inline-flex items-center px-6 py-3.5 bg-[#dc2626] text-white font-medium rounded-xl hover:bg-[#b91c1c] transition-colors duration-200 shadow-md transform hover:scale-[1.02]"
                                >
                                    Lanjut ke Dashboard {{ roleInfo.role }}
                                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
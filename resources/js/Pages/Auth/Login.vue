<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import { UserIcon, LockIcon, EyeIcon, EyeOffIcon } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    nrp: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Presensi TIK - Login" />

    <div class="min-h-screen flex bg-white">
        <!-- Left Side - Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gray-900 relative overflow-hidden items-center justify-center">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <path d="M0 100 C 20 0 50 0 100 100 Z" fill="#dc2626" />
                </svg>
            </div>
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-80 h-80 rounded-full bg-red-600 blur-3xl opacity-20"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 rounded-full bg-red-600 blur-3xl opacity-20"></div>

            <div class="relative z-10 text-center px-10">
                <img src="/images/logo-tik-polri.png" alt="Logo Polda TIK" class="h-48 w-auto mx-auto mb-8 drop-shadow-2xl animate-fade-in-up" />
                <h1 class="text-4xl font-bold text-white tracking-wider mb-4">PRESENSI TIK</h1>
                <p class="text-gray-400 text-lg tracking-wide">POLDA GORONTALO</p>
                <div class="mt-8 w-24 h-1 bg-red-600 mx-auto rounded-full"></div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 lg:p-16 relative">
            <div class="w-full max-w-md space-y-8">
                <!-- Mobile Logo (Visible only on small screens) -->
                <div class="lg:hidden text-center mb-8">
                    <img src="/images/logo-tik-polri.png" alt="Logo Polda TIK" class="h-24 w-auto mx-auto mb-4" />
                    <h2 class="text-2xl font-bold text-gray-900">PRESENTIK</h2>
                    <p class="text-gray-500 text-sm">POLDA GORONTALO</p>
                </div>

                <div class="text-center lg:text-left">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Selamat Datang</h2>
                    <p class="mt-2 text-gray-600">Silahkan login untuk mengakses akun Anda</p>
                </div>

                <div v-if="status" class="p-4 rounded-lg bg-green-50 text-green-700 text-sm font-medium border border-green-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div class="space-y-5">
                        <div>
                            <label for="nrp" class="block text-sm font-semibold text-gray-700 mb-1">NRP / NIP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <UserIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="nrp"
                                    type="text"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent transition duration-200 sm:text-sm bg-gray-50 focus:bg-white"
                                    v-model="form.nrp"
                                    required
                                    autofocus
                                    placeholder="Masukkan NRP atau NIP"
                                />
                            </div>
                            <InputError class="mt-1" :message="form.errors.nrp" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <LockIcon class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    id="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-transparent transition duration-200 sm:text-sm bg-gray-50 focus:bg-white"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Masukkan Password"
                                />
                                <button 
                                    type="button" 
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <EyeIcon v-if="!showPassword" class="h-5 w-5" />
                                    <EyeOffIcon v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <InputError class="mt-1" :message="form.errors.password" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer group">
                            <div class="relative flex items-center">
                                <input 
                                    type="checkbox" 
                                    name="remember" 
                                    v-model="form.remember" 
                                    class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded cursor-pointer transition-colors"
                                />
                            </div>
                            <span class="ms-2 text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ingat Saya</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm font-medium text-red-600 hover:text-red-800 transition-colors"
                        >
                            Lupa Password?
                        </Link>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                            :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-red-500 group-hover:text-red-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            MASUK
                        </button>
                    </div>
                </form>
                
                <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-widest">
                        &copy; {{ new Date().getFullYear() }} POLDA GORONTALO
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.8s ease-out;
}
</style>
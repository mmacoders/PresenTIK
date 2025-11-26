<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

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

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Presensi TIK - Login" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <!-- Decorative Background Elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
            <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] bg-blue-600/20 rounded-full blur-3xl"></div>
            <div class="absolute top-[60%] -right-[10%] w-[40%] h-[60%] bg-yellow-500/10 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-md w-full space-y-8 bg-white/95 backdrop-blur-sm p-8 rounded-2xl shadow-2xl border-t-4 border-yellow-500 z-10 relative">
            <div class="flex flex-col items-center">
                <img src="/images/logo-tik-polri.png" alt="Logo Polda TIK" class="h-28 w-auto drop-shadow-lg transform hover:scale-105 transition-transform duration-300" />
                <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-900 tracking-tight">
                    PRESENSI POLDA TIK
                </h2>
                <p class="mt-2 text-center text-sm text-slate-600 font-medium">
                    Silahkan login untuk melanjutkan
                </p>
            </div>

            <div v-if="status" class="mb-4 p-3 rounded bg-green-50 text-green-600 text-sm font-medium text-center border border-green-200">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="mt-8 space-y-6">
                <div class="space-y-5">
                    <div>
                        <label for="nrp" class="block text-sm font-semibold text-slate-700 mb-1">NRP</label>
                        <input
                            id="nrp"
                            type="text"
                            class="appearance-none block w-full px-3 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-200"
                            v-model="form.nrp"
                            required
                            autofocus
                            placeholder="Nomor Registrasi Pokok"
                        />
                        <InputError class="mt-1" :message="form.errors.nrp" />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                        <input
                            id="password"
                            type="password"
                            class="appearance-none block w-full px-3 py-2.5 border border-slate-300 rounded-lg shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 sm:text-sm transition duration-200"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan Password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            v-model="form.remember" 
                            class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded cursor-pointer"
                        />
                        <span class="ms-2 text-sm text-slate-600">Ingat Saya</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-blue-700 hover:text-blue-900 transition-colors"
                    >
                        Lupa Password?
                    </Link>
                </div>

                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-slate-900 bg-yellow-500 hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                        :class="{ 'opacity-75 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <!-- Heroicon name: solid/lock-closed -->
                            <svg class="h-5 w-5 text-slate-800 group-hover:text-slate-900" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        MASUK
                    </button>
                </div>
            </form>
        </div>
        
        <div class="absolute bottom-6 text-center w-full z-10">
            <p class="text-slate-400 text-xs tracking-wider">
                &copy; 2024 POLDA TIK. ALL RIGHTS RESERVED.
            </p>
        </div>
    </div>
</template>
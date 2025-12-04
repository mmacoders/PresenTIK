<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-red-900 to-gray-900 flex flex-col items-center justify-center p-4 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-96 h-96 bg-red-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-gray-500 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-8 left-20 w-96 h-96 bg-red-700 rounded-full mix-blend-multiply filter blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="relative z-10 w-full max-w-4xl">
            <!-- Logo & Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center p-4 bg-white/10 backdrop-blur-lg rounded-3xl mb-6 shadow-2xl border border-white/10">
                    <img src="/images/logo-tik-polri.png" alt="Logo TIK POLRI" class="w-24 h-24 object-contain drop-shadow-lg" />
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tight mb-2 drop-shadow-lg">
                    PRESEN<span class="text-red-500">TIK</span>
                </h1>
                <p class="text-gray-300 text-lg md:text-xl font-light tracking-wide">Sistem Absensi Digital TIK POLRI</p>
            </div>

            <!-- Selection Cards -->
            <div class="grid md:grid-cols-2 gap-6 md:gap-8 max-w-3xl mx-auto">
                <!-- Mobile Option -->
                <a href="/pwa-guide" class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-red-600 to-red-800 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-500"></div>
                    <div class="relative bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 h-full flex flex-col items-center text-center hover:transform hover:-translate-y-2 transition-all duration-300">
                        <div class="w-16 h-16 bg-red-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-red-500 group-hover:text-white transition-colors duration-300">
                            <SmartphoneIcon class="w-8 h-8 text-red-500 group-hover:text-white transition-colors duration-300" />
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Versi Mobile</h3>
                        <p class="text-gray-400 mb-6 flex-grow">Akses presensi melalui aplikasi mobile dengan panduan instalasi PWA.</p>
                        <span class="inline-flex items-center text-red-400 font-semibold group-hover:text-red-300">
                            Lihat Panduan <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </span>
                    </div>
                </a>

                <!-- Desktop Option -->
                <a href="/login" class="group relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-600 to-gray-800 rounded-2xl blur opacity-25 group-hover:opacity-75 transition duration-500"></div>
                    <div class="relative bg-gray-900/80 backdrop-blur-xl border border-white/10 rounded-2xl p-8 h-full flex flex-col items-center text-center hover:transform hover:-translate-y-2 transition-all duration-300">
                        <div class="w-16 h-16 bg-gray-500/20 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-gray-500 group-hover:text-white transition-colors duration-300">
                            <MonitorIcon class="w-8 h-8 text-gray-400 group-hover:text-white transition-colors duration-300" />
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3">Versi Desktop</h3>
                        <p class="text-gray-400 mb-6 flex-grow">Login ke dashboard administrator untuk pengelolaan data dan laporan.</p>
                        <span class="inline-flex items-center text-gray-400 font-semibold group-hover:text-gray-300">
                            Masuk Sekarang <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </span>
                    </div>
                </a>
            </div>

            <!-- Footer -->
            <div class="mt-16 text-center">
                <p class="text-gray-500 text-sm">© {{ new Date().getFullYear() }} Bidang TIK Polda Gorontalo. All rights reserved.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { SmartphoneIcon, MonitorIcon, ArrowRightIcon } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
</script>

<style scoped>
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
</style>

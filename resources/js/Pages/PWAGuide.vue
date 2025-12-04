<template>
    <div class="min-h-screen bg-gray-50 flex flex-col max-w-md mx-auto shadow-2xl overflow-hidden relative">
        <Head title="Panduan Instalasi" />
        
        <!-- Header -->
        <div class="bg-gradient-to-br from-red-700 to-red-900 p-6 pb-12 rounded-b-[2.5rem] shadow-lg relative z-10">
            <div class="flex items-center justify-between mb-6">
                <a href="/" class="p-2 bg-white/10 rounded-full text-white hover:bg-white/20 transition">
                    <ArrowLeftIcon class="w-6 h-6" />
                </a>
                <img src="/images/logo-tik-polri.png" alt="Logo" class="w-10 h-10 object-contain" />
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Panduan Instalasi</h1>
            <p class="text-red-100 text-sm leading-relaxed">Ikuti langkah-langkah berikut untuk menginstal aplikasi PresenTIK di perangkat Anda.</p>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto -mt-6 px-4 pb-24 relative z-20">
            <!-- Platform Selector -->
            <div class="bg-white rounded-2xl p-2 shadow-md mb-6 flex">
                <button 
                    @click="activeTab = 'android'"
                    :class="['flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2', 
                    activeTab === 'android' ? 'bg-red-50 text-red-600 shadow-sm' : 'text-gray-400 hover:text-gray-600']"
                >
                    <SmartphoneIcon class="w-4 h-4" /> Android
                </button>
                <button 
                    @click="activeTab = 'ios'"
                    :class="['flex-1 py-3 px-4 rounded-xl text-sm font-bold transition-all duration-300 flex items-center justify-center gap-2', 
                    activeTab === 'ios' ? 'bg-blue-50 text-blue-600 shadow-sm' : 'text-gray-400 hover:text-gray-600']"
                >
                    <AppleIcon class="w-4 h-4" /> iOS
                </button>
            </div>

            <!-- Android Steps -->
            <div v-if="activeTab === 'android'" class="space-y-4 animate-fade-in">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">1</div>
                    <h3 class="font-bold text-gray-800 mb-2">Buka Menu Browser</h3>
                    <p class="text-sm text-gray-500">Ketuk ikon titik tiga <span class="inline-block px-1.5 py-0.5 bg-gray-100 rounded text-xs font-mono text-gray-600">⋮</span> di pojok kanan atas browser Chrome Anda.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">2</div>
                    <h3 class="font-bold text-gray-800 mb-2">Install Aplikasi</h3>
                    <p class="text-sm text-gray-500">Cari dan pilih menu <span class="font-semibold text-gray-700">"Install App"</span> atau <span class="font-semibold text-gray-700">"Tambahkan ke Layar Utama"</span>.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-red-100 text-red-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">3</div>
                    <h3 class="font-bold text-gray-800 mb-2">Konfirmasi</h3>
                    <p class="text-sm text-gray-500">Ketuk <span class="font-semibold text-gray-700">"Install"</span> atau <span class="font-semibold text-gray-700">"Tambah"</span> pada pop-up yang muncul.</p>
                </div>
            </div>

            <!-- iOS Steps -->
            <div v-if="activeTab === 'ios'" class="space-y-4 animate-fade-in">
                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">1</div>
                    <h3 class="font-bold text-gray-800 mb-2">Menu Share</h3>
                    <p class="text-sm text-gray-500">Ketuk ikon Share <span class="inline-block px-1.5 py-0.5 bg-gray-100 rounded text-xs font-mono text-gray-600">⎋</span> di bagian bawah browser Safari.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">2</div>
                    <h3 class="font-bold text-gray-800 mb-2">Add to Home Screen</h3>
                    <p class="text-sm text-gray-500">Gulir ke bawah dan pilih menu <span class="font-semibold text-gray-700">"Add to Home Screen"</span> atau <span class="font-semibold text-gray-700">"Tambah ke Layar Utama"</span>.</p>
                </div>

                <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-bold text-sm mb-3">3</div>
                    <h3 class="font-bold text-gray-800 mb-2">Selesai</h3>
                    <p class="text-sm text-gray-500">Ketuk <span class="font-semibold text-gray-700">"Add"</span> atau <span class="font-semibold text-gray-700">"Tambah"</span> di pojok kanan atas.</p>
                </div>
            </div>
        </div>

        <!-- Bottom Action -->
        <div class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-100 z-30">
            <a href="/login" class="flex items-center justify-center w-full bg-red-600 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-red-700 transition active:scale-95">
                Lanjut ke Login
                <ArrowRightIcon class="w-5 h-5 ml-2" />
            </a>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ArrowLeftIcon, ArrowRightIcon, SmartphoneIcon, AppleIcon } from 'lucide-vue-next';

const activeTab = ref('android');
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

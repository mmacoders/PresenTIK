<template>
    <AdminLayout page-title="Laporan Kedisiplinan" mobile-page-title="Disiplin">
        <div class="p-4 sm:p-6 bg-[#F5F6FA] min-h-screen">
            <!-- Header section -->
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                        <AwardIcon class="text-red-600" />
                        Laporan Kedisiplinan Pegawai
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Sistem Pendukung Keputusan (SPK) penilaian kedisiplinan objektif.
                    </p>
                </div>

                <!-- Filter Month/Year -->
                <div class="flex gap-2">
                    <select v-model="filterForm.month" @change="applyFilter" class="border border-gray-300 rounded-lg px-3 py-2 bg-white text-sm focus:ring-red-500 focus:border-red-500">
                        <option v-for="m in 12" :key="m" :value="m">
                            {{ new Date(0, m-1).toLocaleString('id-ID', { month: 'long' }) }}
                        </option>
                    </select>
                    <select v-model="filterForm.year" @change="applyFilter" class="border border-gray-300 rounded-lg px-3 py-2 bg-white text-sm focus:ring-red-500 focus:border-red-500">
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>

            <!-- Content Card -->
            <div class="bg-white rounded-2xl shadow-md overflow-hidden">
                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#ad1f32]">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider w-16">Rank</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Pegawai</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Kehadiran (30%)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Ketepatan (20%)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Bebas Telat (20%)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Bebas Alpha (20%)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider">Konsistensi (10%)</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider cursor-pointer hover:bg-red-700 transition-colors group" @click="toggleSort">
                                    <div class="flex items-center justify-center gap-1">
                                        Nilai Akhir
                                        <ArrowUpDownIcon class="w-4 h-4 text-red-200 group-hover:text-white" :class="{'rotate-180': sortOrder === 'asc'}" />
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-bold text-white uppercase tracking-wider cursor-pointer hover:bg-red-700 transition-colors group" @click="toggleSort">
                                    <div class="flex items-center justify-center gap-1">
                                        Kategori
                                        <ArrowUpDownIcon class="w-4 h-4 text-red-200 group-hover:text-white" :class="{'rotate-180': sortOrder === 'asc'}" />
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in sortedData" :key="item.user.id" class="hover:bg-gray-50 transition-colors cursor-pointer" @click="showDetails(item)">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-500">
                                    #{{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900">{{ item.user.name }}</span>
                                        <span class="text-xs text-gray-500">{{ item.user.nip || 'NIP -' }}</span>
                                        <span class="text-xs text-gray-500 italic">{{ item.user.jabatan || 'Jabatan -' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ item.scores.attendance }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ item.scores.punctuality }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ item.scores.late_freq }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ item.scores.alpha }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                    {{ item.scores.consistency }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-lg font-bold text-gray-900">{{ item.final_score }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span :class="getCategoryClass(item.category)" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ item.category }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="sortedData.length === 0">
                                <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Belum ada data untuk periode ini.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Details Modal -->
            <div v-if="selectedItem" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="selectedItem = null"></div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Detail Evaluasi: {{ selectedItem.user.name }}
                                    </h3>
                                    <div class="mt-4 space-y-3">
                                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <div class="text-sm text-gray-500">Nilai Akhir</div>
                                            <div class="text-3xl font-bold" :class="getScoreColor(selectedItem.final_score)">
                                                {{ selectedItem.final_score }}
                                            </div>
                                            <div class="text-sm font-medium mt-1" :class="getCategoryTextClass(selectedItem.category)">
                                                {{ selectedItem.category }}
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Hari Kerja (s.d. ini)</span>
                                                <span class="font-semibold">{{ selectedItem.metrics.working_days }} Hari</span>
                                            </div>
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Hadir</span>
                                                <span class="font-semibold text-green-600">{{ selectedItem.metrics.present }} Hari</span>
                                            </div>
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Izin (Approved)</span>
                                                <span class="font-semibold text-blue-600">{{ selectedItem.metrics.izin }} Hari</span>
                                            </div>
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Alpha (Tanpa Ket.)</span>
                                                <span class="font-semibold text-red-600">{{ selectedItem.metrics.alpha }} Hari</span>
                                            </div>
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Terlambat</span>
                                                <span class="font-semibold text-yellow-600">{{ selectedItem.metrics.late_count }} Kali</span>
                                            </div>
                                            <div class="border rounded p-2">
                                                <span class="text-xs text-gray-500 block">Rata-rata Terlambat</span>
                                                <span class="font-semibold text-yellow-600">{{ selectedItem.metrics.avg_late_min }} Menit</span>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Penjelasan Kriteria</h4>
                                            <ul class="text-xs text-gray-600 space-y-1 list-disc pl-4">
                                                <li><b>Kehadiran (30%)</b>: Persentase hari hadir dibanding total hari kerja.</li>
                                                <li><b>Ketepatan Waktu (20%)</b>: Berdasarkan rata-rata menit keterlambatan. Semakin kecil semakin baik.</li>
                                                <li><b>Bebas Keterlambatan (20%)</b>: Frekuensi keterlambatan. Jika sering terlambat, nilai turun drastis.</li>
                                                <li><b>Bebas Alpha (20%)</b>: Hukuman berat untuk ketidakhadiran tanpa keterangan.</li>
                                                <li><b>Konsistensi (10%)</b>: Tingkat kehadiran efektif saat tidak sedang izin.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" @click="selectedItem = null">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { AwardIcon, ArrowUpDownIcon } from 'lucide-vue-next';

const props = defineProps({
    rankingData: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const filterForm = useForm({
    month: props.filters.month,
    year: props.filters.year
});

const years = computed(() => {
    const currentYear = new Date().getFullYear();
    const list = [];
    for (let i = 0; i < 5; i++) {
        list.push(currentYear - i);
    }
    return list;
});

const selectedItem = ref(null);
const sortOrder = ref('desc'); // 'desc' (Highest first) or 'asc' (Lowest first)

const toggleSort = () => {
    sortOrder.value = sortOrder.value === 'desc' ? 'asc' : 'desc';
};

const sortedData = computed(() => {
    // Return a shallow copy sorted by final_score
    return [...props.rankingData].sort((a, b) => {
        if (sortOrder.value === 'desc') {
            return b.final_score - a.final_score;
        } else {
            return a.final_score - b.final_score;
        }
    });
});

const applyFilter = () => {
    router.visit(route('admin.laporan-disiplin'), {
        data: {
            month: filterForm.month,
            year: filterForm.year
        },
        preserveState: true,
        preserveScroll: true,
        only: ['rankingData', 'filters']
    });
};

const showDetails = (item) => {
    selectedItem.value = item;
};

const getCategoryClass = (category) => {
    switch(category) {
        case 'Sangat Disiplin': return 'bg-green-100 text-green-800';
        case 'Cukup Disiplin': return 'bg-blue-100 text-blue-800';
        case 'Kurang Disiplin': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getCategoryTextClass = (category) => {
    switch(category) {
        case 'Sangat Disiplin': return 'text-green-600';
        case 'Cukup Disiplin': return 'text-blue-600';
        case 'Kurang Disiplin': return 'text-red-600';
        default: return 'text-gray-600';
    }
};

const getScoreColor = (score) => {
    if (score >= 85) return 'text-green-600';
    if (score >= 70) return 'text-blue-600';
    return 'text-red-600';
};
</script>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'

import {
    WalletIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ScaleIcon
} from '@heroicons/vue/24/outline'

const kasChartOptions = computed(() => ({
    chart: {
        toolbar: { show: true },
    },
    xaxis: {
        categories: kasChart.value.categories,
    },
    dataLabels: {
        enabled: false,
    },
    plotOptions: {
        bar: {
            borderRadius: 5,
        },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => formatRupiah(val),
        },
    },
}))

const penjualanChartOptions = computed(() => ({
    chart: {
        toolbar: { show: true },
    },
    stroke: {
        curve: 'smooth',
        width: 3,
    },
    xaxis: {
        categories: penjualanChart.value.categories,
    },
    dataLabels: {
        enabled: false,
    },
    yaxis: {
        labels: {
            formatter: (val: number) => formatRupiah(val),
        },
    },
}))

interface Summary {
    total_penjualan_rongsok: number
    total_berat_rongsok: number
    total_pemasukan: number
    total_pengeluaran: number
    saldo_kas: number
    total_galeri: number
}

interface ChartData {
    categories: string[]
    series: {
        name: string
        data: number[]
    }[]
}

const loading = ref(false)
const errorMessage = ref('')

const summary = ref<Summary>({
    total_penjualan_rongsok: 0,
    total_berat_rongsok: 0,
    total_pemasukan: 0,
    total_pengeluaran: 0,
    saldo_kas: 0,
    total_galeri: 0,
})

const kasChart = ref<ChartData>({
    categories: [],
    series: [],
})

const penjualanChart = ref<ChartData>({
    categories: [],
    series: [],
})

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value)
}

interface PenjualanRongsok {
    id: number
    tanggal: string
    total_berat: number
    total_pendapatan: number
    tempat_jual: string
}

interface PengeluaranKas {
    id: number
    tanggal: string
    keperluan: string
    nominal: number
}

interface GaleriKegiatan {
    id: number
    judul: string
    kategori: string
    tanggal: string
    foto: string
    sumber: string
    real_id: number | null
}
const galeriTerbaru = ref<GaleriKegiatan[]>([])

const tigaGaleriTerbaru = computed(() => {
    return [...galeriTerbaru.value]
        .sort(
            (a, b) =>
                new Date(b.tanggal).getTime() -
                new Date(a.tanggal).getTime()
        )
        .slice(0, 3)
})
const penjualanTerbaru = ref<PenjualanRongsok[]>([])
const pengeluaranTerbaru = ref<PengeluaranKas[]>([])
const isChartReady = computed(() => {
    return kasChart.value.categories.length > 0 && kasChart.value.series.length > 0
})
const isPenjualanChartReady = computed(() => {
    return penjualanChart.value.categories.length > 0 && penjualanChart.value.series.length > 0
})

const loadDashboard = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/dashboard')

        summary.value = res.data.summary
        kasChart.value = res.data.charts.kas_sosial
        penjualanChart.value = res.data.charts.penjualan_rongsok
        penjualanTerbaru.value = res.data.latest.penjualan_rongsok
        pengeluaranTerbaru.value = res.data.latest.pengeluaran_kas
        // galeriTerbaru.value = res.data.latest.galeri_kegiatan
        console.log(res.data.charts)
    } catch (error) {
        console.error(error)
        errorMessage.value = 'Gagal memuat data dashboard'
    } finally {
        loading.value = false
    }
}

const loadGaleri = async () => {
    try {
        const res = await api.get('/galeri-semua-bukti')
        galeriTerbaru.value = res.data.data
    } catch (error) {
        console.error(error)
    }
}

const getFotoUrl = (foto: string) => {
    return `http://localhost:8000/storage/${foto}`
}

const formatKategori = (kategori: string) => {
    return kategori.replaceAll('_', ' ')
}

onMounted(() => {
    loadDashboard()
    loadGaleri()
})
</script>

<template>
    <AdminLayout>
        <div v-if="loading" class="rounded-3xl bg-white p-8 shadow-sm">
            Memuat data dashboard...
        </div>

        <div v-else-if="errorMessage" class="rounded-3xl bg-red-50 p-5 text-red-600">
            {{ errorMessage }}
        </div>

        <div v-else class="space-y-6">
            <!-- Hero -->
            <section class="rounded-3xl bg-linear-to-r from-emerald-600 to-sky-600 p-6 text-white shadow-lg">
                <p class="text-sm opacity-90">Bank Rongsok Sosial</p>
                <h1 class="mt-2 text-2xl font-bold md:text-3xl">
                    Transparansi Dana Sosial dari Hasil Jual Rongsok
                </h1>
                <p class="mt-2 max-w-3xl text-sm opacity-90">
                    Semua hasil penjualan rongsok dikumpulkan untuk membantu kegiatan sosial dan saudara yang
                    membutuhkan.
                </p>
            </section>

            <!-- Stats -->
            <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

                <!-- Saldo Kas -->
                <div class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Saldo Kas Sosial</p>
                            <h2 class="mt-3 text-2xl font-bold text-emerald-600">
                                {{ formatRupiah(summary.saldo_kas) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-emerald-100 p-3 ml-2 mt-5">
                            <WalletIcon class="h-7 w-7 text-emerald-600" />
                        </div>
                    </div>
                </div>

                <!-- Pemasukan -->
                <div class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Pemasukan</p>
                            <h2 class="mt-3 text-2xl font-bold text-sky-600">
                                {{ formatRupiah(summary.total_pemasukan) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-sky-100 p-3 ml-2 mt-5">
                            <ArrowTrendingUpIcon class="h-7 w-7 text-sky-600" />
                        </div>
                    </div>
                </div>

                <!-- Pengeluaran -->
                <div class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Total Pengeluaran</p>
                            <h2 class="mt-3 text-2xl font-bold text-amber-600">
                                {{ formatRupiah(summary.total_pengeluaran) }}
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-amber-100 p-3 ml-2 mt-5">
                            <ArrowTrendingDownIcon class="h-7 w-7 text-amber-600" />
                        </div>
                    </div>
                </div>

                <!-- Rongsok -->
                <div class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-500">Rongsok Terkumpul</p>
                            <h2 class="mt-3 text-2xl font-bold text-slate-800">
                                {{ summary.total_berat_rongsok }} Kg
                            </h2>
                        </div>

                        <div class="rounded-2xl bg-slate-100 p-3 ml-2 mt-5">
                            <ScaleIcon class="h-7 w-7 text-slate-700" />
                        </div>
                    </div>
                </div>

            </section>

            <!-- Charts -->
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-slate-800">Kas Sosial</h3>
                        <p class="text-sm text-slate-500">Pemasukan dan pengeluaran per bulan</p>
                    </div>

                    <VueApexCharts v-if="isChartReady" type="bar" height="330" :options="kasChartOptions"
                        :series="kasChart.series" />

                    <div v-else class="flex h-[330px] items-center justify-center text-sm text-slate-500">
                        Memuat grafik...
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-lg font-bold text-slate-800">Penjualan Rongsok</h3>
                        <p class="text-sm text-slate-500">Pendapatan hasil jual rongsok per bulan</p>
                    </div>

                    <VueApexCharts v-if="isPenjualanChartReady" type="area" height="330"
                        :options="penjualanChartOptions" :series="penjualanChart.series" />
                </div>
            </section>

            <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Penjualan Rongsok Terbaru</h3>
                            <p class="text-sm text-slate-500">Riwayat penjualan terakhir</p>
                        </div>
                        <RouterLink to="/admin/penjualan-rongsok"
                            class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            Kelola Penjualan
                        </RouterLink>
                    </div>

                    <div class="max-h-50 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in penjualanTerbaru" :key="item.id"
                            class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                            <div>
                                <p class="font-semibold text-slate-800">{{ item.tempat_jual }}</p>
                                <p class="text-sm text-slate-500">{{ item.tanggal }} • {{ item.total_berat }} Kg</p>
                            </div>

                            <p class="font-bold text-emerald-600">
                                {{ formatRupiah(item.total_pendapatan) }}
                            </p>
                        </div>

                        <p v-if="penjualanTerbaru.length === 0" class="text-sm text-slate-500">
                            Belum ada data penjualan rongsok.
                        </p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5 flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Pemasukan Kas Terbaru</h3>
                            <p class="text-sm text-slate-500">Riwayat pemasukan kas terakhir</p>
                        </div>
                        <RouterLink to="/admin/pengeluaran-kas"
                            class="rounded-2xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                            Kelola Pengeluaran
                        </RouterLink>
                    </div>
                    <div class="max-h-50 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in pengeluaranTerbaru" :key="item.id"
                            class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                            <div>
                                <p class="font-semibold text-slate-800">{{ item.keperluan }}</p>
                                <p class="text-sm text-slate-500">{{ item.tanggal }}</p>
                            </div>

                            <p class="font-bold text-amber-600">
                                {{ formatRupiah(item.nominal) }}
                            </p>
                        </div>

                        <p v-if="pengeluaranTerbaru.length === 0" class="text-sm text-slate-500">
                            Belum ada data pengeluaran kas.
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Galeri Kegiatan Terbaru</h3>
                        <p class="text-sm text-slate-500">
                            Dokumentasi pengambilan rongsok dan penyaluran dana sosial
                        </p>
                    </div>

                    <RouterLink to="/admin/galeri-kegiatan"
                        class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        Kelola Galeri
                    </RouterLink>
                </div>

                <div v-if="tigaGaleriTerbaru.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    <div v-for="item in tigaGaleriTerbaru" :key="item.id"
                        class="overflow-hidden rounded-3xl border border-slate-100 bg-slate-50">
                        <img :src="getFotoUrl(item.foto)" :alt="item.judul" class="h-48 w-full object-cover" />

                        <div class="p-4">
                            <span
                                class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold capitalize text-emerald-700">
                                {{ formatKategori(item.kategori) }}
                            </span>

                            <h4 class="mt-3 font-bold text-slate-800">
                                {{ item.judul }}
                            </h4>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ item.tanggal }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="rounded-3xl border border-dashed border-slate-300 p-8 text-center">
                    <p class="text-sm text-slate-500">
                        Belum ada galeri kegiatan.
                    </p>
                </div>
            </section>

            <!-- Info ringkas -->
            <section class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Total Hasil Jual Rongsok</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-800">
                        {{ formatRupiah(summary.total_penjualan_rongsok) }}
                    </h3>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <p class="text-sm text-slate-500">Dokumentasi Galeri</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-800">
                        {{ summary.total_galeri }} Foto Kegiatan
                    </h3>
                </div>

                <div class="rounded-3xl bg-emerald-50 p-6">
                    <p class="text-sm font-medium text-emerald-700">Prinsip Sistem</p>
                    <h3 class="mt-2 text-lg font-bold text-emerald-900">
                        Tidak ada laba dibagikan. Seluruh dana untuk kegiatan sosial.
                    </h3>
                </div>
            </section>

        </div>
    </AdminLayout>
</template>
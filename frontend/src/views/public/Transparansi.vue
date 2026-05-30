<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import AOS from 'aos'
import 'aos/dist/aos.css'
import {
    WalletIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
    ScaleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

const isLoggedIn = computed(() => !!localStorage.getItem('token'))

const showBackToTop = ref(false)

const handleScroll = () => {
    showBackToTop.value = window.scrollY > 400
}

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    })
}

const formatSumber = (sumber: string) => {
    return sumber
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase())
}

const logout = async () => {
    try {
        await api.post('/logout')
    } catch (error) {
        console.error(error)
    }

    localStorage.removeItem('token')
    localStorage.removeItem('user')

    router.push('/login')
}

interface Summary {
    total_hasil_jual_rongsok: number
    total_berat_rongsok: number
    total_pemasukan: number
    total_pengeluaran: number
    saldo_kas: number
    total_kegiatan: number
}

interface PenjualanRongsok {
    id: number
    tanggal: string
    periode_mulai: string | null
    periode_selesai: string | null
    total_berat: number
    total_pendapatan: number
    tempat_jual: string | null
    keterangan: string | null
    foto_bukti: string | null

    pemasukan_kas?: {
        id: number
        nominal: number
    }
}

interface PengeluaranKas {
    id: number
    tanggal: string
    keperluan: string
    deskripsi: string | null
    nominal: number
    foto: string | null
}

interface PemasukanKas {
    id: number
    tanggal: string
    sumber: string
    keterangan: string
    nominal: number
    foto: string | null
}

interface GaleriKegiatan {
    id: number
    judul: string
    deskripsi: string | null
    kategori: string
    tanggal: string
    foto: string
}

interface LaporanBulanan {
    tahun: number
    bulan: number
    total_berat: number
    total_pendapatan: number
}

const loading = ref(false)
const errorMessage = ref('')

const summary = ref<Summary>({
    total_hasil_jual_rongsok: 0,
    total_berat_rongsok: 0,
    total_pemasukan: 0,
    total_pengeluaran: 0,
    saldo_kas: 0,
    total_kegiatan: 0,
})

const penjualanTerbaru = ref<PenjualanRongsok[]>([])
const pengeluaranTerbaru = ref<PengeluaranKas[]>([])
const pemasukanTerbaru = ref<PemasukanKas[]>([])
const galeriTerbaru = ref<GaleriKegiatan[]>([])
const laporanBulanan = ref<LaporanBulanan[]>([])

const namaBulan = [
    '',
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember',
]

const formatRupiah = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value)
}

const getFotoUrl = (foto: string | null) => {
    if (!foto) return ''
    return `http://localhost:8000/storage/${foto}`
}

const formatKategori = (kategori: string) => {
    return kategori.replaceAll('_', ' ')
}

const loadData = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/public/transparansi')

        summary.value = res.data.summary ?? summary.value

        penjualanTerbaru.value = res.data.nominal ?? []
        pemasukanTerbaru.value = res.data.pemasukan_terbaru ?? []
        pengeluaranTerbaru.value = res.data.pengeluaran_terbaru ?? []
        galeriTerbaru.value = res.data.galeri_terbaru ?? []
        laporanBulanan.value = res.data.laporan_bulanan ?? []
    } catch (error) {
        console.error(error)
        errorMessage.value = 'Gagal memuat data transparansi'
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    loadData()
    window.addEventListener('scroll', handleScroll)
    AOS.init({
        duration: 800,
        once: true,
    })
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
    <button v-if="showBackToTop" @click="scrollToTop"
        class="fixed bottom-10 right-15 z-50 rounded-full text-2xl bg-emerald-400 p-5 text-white shadow-lg hover:bg-emerald-600">
        ^
    </button>
    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Navbar -->
        <header class="sticky top-0 z-30 border-b border-white/60 bg-white/80 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 lg:px-8">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-18 w-18 items-center justify-center rounded-2xl text-xl text-white shadow- shadow-emerald-200">
                        <img class="h-full w-full object-contain mt-2" src="/public/logo/Loopit.png" alt="Logo">
                    </div>
                    <div>
                        <h1 class="text-base font-bold leading-tight text-slate-900">
                            Bank Rongsok Sosial
                        </h1>
                        <p class="text-xs text-slate-500">
                            Transparansi dana sosial
                        </p>
                    </div>
                </div>

                <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 md:flex">
                    <a href="#tentang" class="hover:text-emerald-600">Tentang</a>
                    <a href="#transparansi" class="hover:text-emerald-600">Data</a>
                    <a href="#laporan" class="hover:text-emerald-600">Laporan</a>
                    <a href="#galeri" class="hover:text-emerald-600">Galeri</a>
                </nav>

                <!-- <a href="#transparansi"
                    class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Lihat Data
                </a> -->

                <button v-if="isLoggedIn" @click="logout"
                    class="rounded-2xl bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-100">
                    Logout
                </button>

                <RouterLink v-else to="/login"
                    class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold shadow-emerald-200 text-white hover:bg-emerald-700">
                    Login
                </RouterLink>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-linear-to-br from-emerald-50 via-sky-50 to-white" />
            <div class="absolute -right-20 top-20 h-72 w-72 rounded-full bg-emerald-200/40 blur-3xl" />
            <div class="absolute -left-20 bottom-10 h-72 w-72 rounded-full bg-sky-200/40 blur-3xl" />

            <div class="relative mx-auto grid max-w-7xl grid-cols-1 gap-10 px-5 py-16 lg:grid-cols-2 lg:px-8 lg:py-24">
                <div data-aos="fade-right" class="flex flex-col justify-center">
                    <span
                        class="mb-5 inline-flex w-fit rounded-full bg-emerald-100 px-4 py-2 text-sm font-semibold text-emerald-700">
                        Rongsok Dikumpulkan, Dana Disalurkan
                    </span>

                    <h2 class="text-4xl font-extrabold tracking-tight text-slate-900 md:text-5xl">
                        Transparansi hasil jual rongsok untuk kegiatan sosial.
                    </h2>

                    <p class="mt-5 max-w-xl text-base leading-8 text-slate-600">
                        Program ini mengumpulkan rongsok yang masih bernilai jual, kemudian hasil penjualannya
                        dimasukkan ke kas sosial untuk membantu saudara yang sakit, kegiatan sosial, dan kebutuhan
                        kemanusiaan lainnya.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="#transparansi"
                            class="rounded-2xl bg-emerald-600 px-6 py-3 text-center text-sm font-bold text-white shadow-lg shadow-emerald-200 hover:bg-emerald-700">
                            Lihat Transparansi
                        </a>

                        <a href="#galeri"
                            class="rounded-2xl border border-slate-200 bg-white px-6 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">
                            Lihat Kegiatan
                        </a>
                    </div>
                </div>

                <div data-aos="fade-left" class="rounded-[2rem] bg-white p-5 shadow-2xl shadow-slate-200">
                    <div class="rounded-[1.5rem] bg-linear-to-br from-emerald-600 to-sky-600 p-6 text-white">
                        <p class="text-sm opacity-90">Saldo Kas Sosial Saat Ini</p>
                        <h3 class="mt-3 text-4xl font-extrabold">
                            {{ formatRupiah(summary.saldo_kas) }}
                        </h3>
                        <p class="mt-3 text-sm opacity-90">
                            Dana ini berasal dari hasil penjualan rongsok dan pemasukan sosial lainnya.
                        </p>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-4">
                        <div class="rounded-3xl bg-emerald-50 p-4">
                            <p class="text-xs text-slate-500">Hasil Jual</p>
                            <h4 class="mt-2 text-lg font-bold text-emerald-700">
                                {{ formatRupiah(summary.total_hasil_jual_rongsok) }}
                            </h4>
                        </div>

                        <div class="rounded-3xl bg-sky-50 p-4">
                            <p class="text-xs text-slate-500">Rongsok</p>
                            <h4 class="mt-2 text-lg font-bold text-sky-700">
                                {{ summary.total_berat_rongsok }} Kg
                            </h4>
                        </div>

                        <div class="rounded-3xl bg-amber-50 p-4">
                            <p class="text-xs text-slate-500">Pengeluaran</p>
                            <h4 class="mt-2 text-lg font-bold text-amber-700">
                                {{ formatRupiah(summary.total_pengeluaran) }}
                            </h4>
                        </div>

                        <div class="rounded-3xl bg-slate-100 p-4">
                            <p class="text-xs text-slate-500">Kegiatan</p>
                            <h4 class="mt-2 text-lg font-bold text-slate-800">
                                {{ summary.total_kegiatan }} Foto
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Loading / Error -->
        <section v-if="loading" class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
            <div class="rounded-3xl bg-white p-6 text-sm text-slate-500 shadow-sm">
                Memuat data transparansi...
            </div>
        </section>

        <section v-else-if="errorMessage" class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
            <div class="rounded-3xl bg-red-50 p-6 text-sm text-red-700">
                {{ errorMessage }}
            </div>
        </section>

        <template v-else>
            <!-- Tentang -->
            <section id="tentang" class="mx-auto max-w-7xl px-5 py-16 lg:px-8">
                <div data-aos="fade-up" class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <div class="lg:col-span-1">
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                            Tentang Program
                        </p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            Apa itu Bank Rongsok Sosial?
                        </h3>
                    </div>

                    <div class="grid gap-5 md:grid-cols-3 lg:col-span-2">
                        <div class="rounded-3xl bg-white p-6 shadow-sm">
                            <div class="mb-4 text-3xl">📦</div>
                            <h4 class="font-bold text-slate-900">Kumpulkan Rongsok</h4>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Rongsok dikumpulkan secara berkala dari anggota atau masyarakat.
                            </p>
                        </div>

                        <div class="rounded-3xl bg-white p-6 shadow-sm">
                            <div class="mb-4 text-3xl">🚚</div>
                            <h4 class="font-bold text-slate-900">Dijual ke Pengepul</h4>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Rongsok dijual secara global per periode, bukan dihitung sebagai saldo pribadi.
                            </p>
                        </div>

                        <div class="rounded-3xl bg-white p-6 shadow-sm">
                            <div class="mb-4 text-3xl">🤝</div>
                            <h4 class="font-bold text-slate-900">Untuk Dana Sosial</h4>
                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Hasil jual masuk ke kas sosial dan digunakan untuk membantu yang membutuhkan.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Data Transparansi -->
            <section id="transparansi" class="bg-emerald-50 py-16">
                <div data-aos="fade-up" class="mx-auto max-w-7xl px-5 lg:px-8">
                    <div class="mb-8 max-w-2xl">
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                            Data Transparansi
                        </p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            Ringkasan Kas Sosial
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Semua data ditampilkan secara global agar masyarakat dapat melihat hasil penjualan,
                            pengeluaran, dan sisa saldo kas sosial.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
                        <div
                            class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Saldo Kas Sosial</p>
                                    <h2 class="mt-3 text-2xl font-bold text-sky-600">
                                        {{ formatRupiah(summary.saldo_kas) }}
                                    </h2>
                                </div>

                                <div class="rounded-2xl bg-sky-100 p-3 ml-2 mt-5">
                                    <WalletIcon class="h-7 w-7 text-sky-600" />
                                </div>
                            </div>
                        </div>

                        <!-- Pemasukan -->
                        <div
                            class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-slate-500">Total Pemasukan</p>
                                    <h2 class="mt-3 text-2xl font-bold text-emerald-600">
                                        {{ formatRupiah(summary.total_pemasukan) }}
                                    </h2>
                                </div>

                                <div class="rounded-2xl bg-emerald-100 p-3 ml-2 mt-5">
                                    <ArrowTrendingUpIcon class="h-7 w-7 text-emerald-600" />
                                </div>
                            </div>
                        </div>

                        <!-- Pengeluaran -->
                        <div
                            class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
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
                        <div
                            class="rounded-3xl bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
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
                    </div>
                </div>
            </section>

            <!-- Pemasukan -->
            <section data-aos="fade-up"
                class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-5 py-16 lg:grid-cols-2 lg:px-8">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-slate-900">Pemasukan Terbaru</h3>
                        <p class="text-sm text-slate-500">Riwayat pemasukan dari donasi dan penjualan rongsok.</p>
                    </div>

                    <div class="max-h-50 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in pemasukanTerbaru.slice(0, 5)" :key="item.id"
                            class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ item.keterangan }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ item.tanggal }}
                                </p>

                                <span
                                    class="mt-2 inline-block rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-700">
                                    {{ formatSumber (item.sumber) }}
                                </span>
                            </div>

                            <p class="font-bold text-sky-600">
                                {{ formatRupiah(item.nominal) }}
                            </p>
                        </div>

                        <p v-if="!pemasukanTerbaru?.length" class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-3xl p-4 text-center">
                            Belum ada data pemasukan.
                        </p>
                    </div>
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-slate-900">Penjualan Rongsok Terbaru</h3>
                        <p class="text-sm text-slate-500">Riwayat penjualan rongsok secara global.</p>
                    </div>

                    <div class="max-h-50 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in penjualanTerbaru" :key="item.id" class="rounded-2xl bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-slate-800">
                                        {{ item.tempat_jual ?? 'Tempat jual tidak dicantumkan' }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ item.tanggal }} • {{ item.total_berat }} Kg
                                    </p>
                                </div>

                                <p class="text-right font-extrabold text-emerald-600">
                                    {{ formatRupiah(item.total_pendapatan) }}
                                </p>
                            </div>
                        </div>

                        <p v-if="penjualanTerbaru.length === 0" class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-3xl p-4 text-center">
                            Belum ada data penjualan rongsok.
                        </p>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="mb-5">
                        <h3 class="text-xl font-bold text-slate-900">Pengeluaran Sosial Terbaru</h3>
                        <p class="text-sm text-slate-500">Dana yang digunakan untuk kegiatan sosial.</p>
                    </div>

                    <div class="max-h-50 space-y-4 overflow-y-auto pr-2">
                        <div v-for="item in pengeluaranTerbaru" :key="item.id" class="rounded-2xl bg-slate-50 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-slate-800">
                                        {{ item.keperluan }}
                                    </p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ item.tanggal }}
                                    </p>
                                    <p class="mt-1 line-clamp-1 text-xs text-slate-400">
                                        {{ item.deskripsi ?? 'Tidak ada deskripsi.' }}
                                    </p>
                                </div>

                                <p class="text-right font-extrabold text-amber-600">
                                    {{ formatRupiah(item.nominal) }}
                                </p>
                            </div>
                        </div>

                        <p v-if="pengeluaranTerbaru.length === 0" class="text-sm text-slate-500 border border-dashed border-slate-300 rounded-3xl p-4 text-center">
                            Belum ada data pengeluaran sosial.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Laporan -->
            <section id="laporan" class="bg-white py-16">
                <div data-aos="fade-up" class="mx-auto max-w-7xl px-5 lg:px-8">
                    <div class="mb-8">
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                            Laporan Bulanan
                        </p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            Rekap Hasil Jual Rongsok
                        </h3>
                    </div>

                    <div class="overflow-x-auto rounded-3xl border border-slate-100">
                        <table class="w-full min-w-[720px] bg-white text-left text-sm">
                            <thead class="bg-emerald-50 text-slate-500">
                                <tr>
                                    <th class="px-5 py-4">Bulan</th>
                                    <th class="px-5 py-4">Total Berat</th>
                                    <th class="px-5 py-4">Total Pendapatan</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="item in laporanBulanan" :key="`${item.tahun}-${item.bulan}`"
                                    class="border-t border-slate-100">
                                    <td class="px-5 py-4 font-bold text-slate-800">
                                        {{ namaBulan[item.bulan] }} {{ item.tahun }}
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ item.total_berat }} Kg
                                    </td>
                                    <td class="px-5 py-4 font-bold text-emerald-600">
                                        {{ formatRupiah(item.total_pendapatan) }}
                                    </td>
                                </tr>

                                <tr v-if="laporanBulanan.length === 0">
                                    <td colspan="3" class="px-5 py-8 text-center text-slate-500">
                                        Belum ada laporan bulanan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Galeri -->
            <section data-aos="fade-up" id="galeri" class="py-16 bg-emerald-50">
                <div class="mb-8 mx-auto max-w-7xl px-5 lg:px-8">
                    <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                        Galeri Kegiatan
                    </p>
                    <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                        Dokumentasi Kegiatan Sosial
                    </h3>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                        Dokumentasi pengambilan rongsok, penjualan rongsok, kegiatan sosial,
                        dan penyaluran dana untuk masyarakat yang membutuhkan.
                    </p>
                </div>

                <div v-if="galeriTerbaru.length > 0"
                    class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-8 mx-auto max-w-7xl px-5 lg:px-8">
                    <div v-for="item in galeriTerbaru" :key="item.id"
                        class="overflow-hidden rounded-3xl bg-white shadow-sm">
                        <img :src="getFotoUrl(item.foto)" :alt="item.judul" class="h-56 w-full object-cover" />

                        <div class="p-5">
                            <span
                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold capitalize text-emerald-700">
                                {{ formatKategori(item.kategori) }}
                            </span>

                            <h4 class="mt-3 font-bold text-slate-900">
                                {{ item.judul }}
                            </h4>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ item.tanggal }}
                            </p>

                            <p class="mt-3 line-clamp-2 text-sm leading-6 text-slate-500">
                                {{ item.deskripsi ?? 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else
                    class="mx-auto max-w-6xl px-5 lg:px-8 rounded-3xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                    Belum ada galeri kegiatan.
                </div>
            </section>
        </template>

        <!-- Footer -->
        <footer class="border-t bg-white">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-slate-500 md:flex-row md:items-center md:justify-between lg:px-8">
                <p>© 2026 Bank Rongsok Sosial. Transparansi dana untuk kegiatan sosial.</p>
                <p>Tidak ada laba dibagikan. Seluruh dana digunakan untuk sosial.</p>
            </div>
        </footer>
    </div>
</template>
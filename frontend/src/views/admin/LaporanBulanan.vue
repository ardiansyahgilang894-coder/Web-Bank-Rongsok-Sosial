<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import { DocumentArrowDownIcon } from '@heroicons/vue/24/outline'

interface LaporanItem {
  tahun: number
  bulan: number
  total_berat?: number
  total_pendapatan?: number
  total_pemasukan?: number
  total_pengeluaran?: number
}

const loading = ref(false)
const errorMessage = ref('')

const penjualan = ref<LaporanItem[]>([])
const pemasukan = ref<LaporanItem[]>([])
const pengeluaran = ref<LaporanItem[]>([])

const search = ref('')
const currentPage = ref(1)
const perPage = ref(5)

const namaBulan = [
  '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const filteredPenjualan = computed(() => {
  if (!search.value) {
    return penjualan.value
  }

  const keyword = search.value.toLowerCase()

  return penjualan.value.filter((item) => {
    const bulan = namaBulan[item.bulan]?.toLowerCase() || ''
    const tahun = String(item.tahun)

    return (
      bulan.includes(keyword) ||
      tahun.includes(keyword) ||
      `${bulan} ${tahun}`.includes(keyword)
    )
  })
})

const totalPages = computed(() => {
  return Math.ceil(filteredPenjualan.value.length / perPage.value)
})

const paginatedPenjualan = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value

  return filteredPenjualan.value.slice(start, end)
})

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

watch(search, () => {
  currentPage.value = 1
})

watch(perPage, () => {
  currentPage.value = 1
})

const formatRupiah = (value: number = 0) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(value)
}

const loadLaporan = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const res = await api.get('/laporan/bulanan')

    penjualan.value = res.data.penjualan_rongsok
    pemasukan.value = res.data.pemasukan_kas
    pengeluaran.value = res.data.pengeluaran_kas
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Gagal memuat laporan bulanan'
  } finally {
    loading.value = false
  }
}

const exportExcel = async () => {
  const token = localStorage.getItem('token')

  const res = await fetch('http://localhost:8000/api/laporan/bulanan/export-excel', {
    headers: {
      Authorization: `Bearer ${token}`,
    },
  })

  const blob = await res.blob()
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')

  link.href = url
  link.download = 'laporan-bulanan-bank-rongsok.xlsx'
  link.click()

  window.URL.revokeObjectURL(url)
}

const getPemasukan = (tahun: number, bulan: number) => {
  return pemasukan.value.find(
    item => item.tahun === tahun && item.bulan === bulan
  )?.total_pemasukan ?? 0
}

const getPengeluaran = (tahun: number, bulan: number) => {
  return pengeluaran.value.find(
    item => item.tahun === tahun && item.bulan === bulan
  )?.total_pengeluaran ?? 0
}

onMounted(loadLaporan)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Laporan Bulanan</h1>
        <p class="text-sm text-slate-500">
          Rekap penjualan rongsok, pemasukan kas, pengeluaran sosial, dan sisa saldo per bulan.
        </p>
      </div>

      <div v-if="loading" class="rounded-3xl bg-white p-6 text-sm text-slate-500 shadow-sm">
        Memuat laporan...
      </div>

      <div v-else-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <section v-else class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h2 class="text-lg font-bold text-slate-800">Rekap Laporan</h2>
            <p class="text-sm text-slate-500">Data dikelompokkan berdasarkan bulan.</p>
          </div>

          <div class="flex gap-2">
            <input v-model="search" type="text" placeholder="Cari bulan / tahun..."
              class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-64" />

            <select v-model="perPage"
              class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
              <option :value="5">5 data</option>
              <option :value="10">10 data</option>
              <option :value="25">25 data</option>
            </select>
            <button @click="exportExcel"
              class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
              <DocumentArrowDownIcon class="h-5 w-5" />
              Export Excel
            </button>
          </div>
        </div>

        <div class="overflow-x-auto border rounded-2xl border-slate-200">
          <table class="w-full min-w-237.5 text-left text-sm">
            <thead>
              <tr class="bg-emerald-100 text-slate-600">
                <th class="px-4 py-3">Bulan</th>
                <th class="px-4 py-3">Total Berat</th>
                <th class="px-4 py-3">Hasil Jual Rongsok</th>
                <th class="px-4 py-3">Total Pemasukan</th>
                <th class="px-4 py-3">Total Pengeluaran</th>
                <th class="px-4 py-3">Sisa Saldo</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="item in paginatedPenjualan" :key="`${item.tahun}-${item.bulan}`" class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">
                  {{ namaBulan[item.bulan] }} {{ item.tahun }}
                </td>

                <td class="px-4 py-3 text-slate-700">
                  {{ item.total_berat ?? 0 }} Kg
                </td>

                <td class="px-4 py-3 font-bold text-emerald-600">
                  {{ formatRupiah(item.total_pendapatan ?? 0) }}
                </td>

                <td class="px-4 py-3 font-bold text-sky-600">
                  {{ formatRupiah(getPemasukan(item.tahun, item.bulan)) }}
                </td>

                <td class="px-4 py-3 font-bold text-amber-600">
                  {{ formatRupiah(getPengeluaran(item.tahun, item.bulan)) }}
                </td>

                <td class="px-4 py-3 font-bold text-slate-800">
                  {{ formatRupiah(getPemasukan(item.tahun, item.bulan) - getPengeluaran(item.tahun, item.bulan)) }}
                </td>
              </tr>

              <tr v-if="paginatedPenjualan.length === 0">
                <td colspan="6" class="py-3 px-4 text-center text-slate-500">
                  Data laporan tidak ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <p class="text-sm text-slate-500">
            Menampilkan {{ paginatedPenjualan.length }} dari {{ filteredPenjualan.length }} data
          </p>

          <div class="flex items-center gap-2">
            <button type="button" @click="prevPage" :disabled="currentPage === 1"
              class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
              Previous
            </button>

            <span class="text-sm font-semibold text-slate-600">
              {{ currentPage }} / {{ totalPages || 1 }}
            </span>

            <button type="button" @click="nextPage" :disabled="currentPage === totalPages || totalPages === 0"
              class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
              Next
            </button>
          </div>
        </div>
      </section>
    </div>
  </AdminLayout>
</template>
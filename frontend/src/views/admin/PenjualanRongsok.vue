<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import Swal from 'sweetalert2'
import { Toast } from '@/utils/toast'

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
}

const data = ref<PenjualanRongsok[]>([])
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
    tanggal: '',
    periode_mulai: '',
    periode_selesai: '',
    total_berat: '',
    total_pendapatan: '',
    tempat_jual: '',
    keterangan: '',
    foto_bukti: null as File | null,
})

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

const search = ref('')
const currentPage = ref(1)
const perPage = ref(5)

const filteredData = computed(() => {
    if (!search.value) return data.value

    const keyword = search.value.toLowerCase()

    return data.value.filter((item) =>
        JSON.stringify(item).toLowerCase().includes(keyword)
    )
})

const totalPages = computed(() => {
    return Math.ceil(filteredData.value.length / perPage.value)
})

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value

    return filteredData.value.slice(start, end)
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

const resetForm = () => {
    form.value = {
        tanggal: '',
        periode_mulai: '',
        periode_selesai: '',
        total_berat: '',
        total_pendapatan: '',
        tempat_jual: '',
        keterangan: '',
        foto_bukti: null,
    }

    isEdit.value = false
    editId.value = null
    oldFoto.value = null
}

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement
    form.value.foto_bukti = target.files?.[0] ?? null
}

const loadData = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/penjualan-rongsok')
        data.value = res.data.data
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal memuat data penjualan rongsok.',
        })
    } finally {
        loading.value = false
    }
}

const submitForm = async () => {
    saving.value = true
    errorMessage.value = ''
    successMessage.value = ''

    try {
        const payload = new FormData()

        payload.append('tanggal', form.value.tanggal)
        if (form.value.periode_mulai) {
            payload.append('periode_mulai', form.value.periode_mulai)
        }

        if (form.value.periode_selesai) {
            payload.append('periode_selesai', form.value.periode_selesai)
        }
        payload.append('total_berat', form.value.total_berat)
        payload.append('total_pendapatan', form.value.total_pendapatan)
        if (form.value.tempat_jual) {
            payload.append('tempat_jual', form.value.tempat_jual)
        }

        if (form.value.keterangan) {
            payload.append('keterangan', form.value.keterangan)
        }

        if (form.value.foto_bukti) {
            payload.append('foto_bukti', form.value.foto_bukti)
        }

        if (isEdit.value && editId.value) {
            payload.append('_method', 'PUT')

            await api.post(`/penjualan-rongsok/${editId.value}`, payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })

            Toast.fire({
                icon: 'success',
                title: 'Data penjualan rongsok berhasil diperbarui',
            })
        } else {
            await api.post('/penjualan-rongsok', payload, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })

            Toast.fire({
                icon: 'success',
                title: 'Data penjualan rongsok berhasil ditambahkan',
            })
        }

        resetForm()
        await loadData()
    } catch (error: any) {
        console.error(error.response?.data)

        const errors = error.response?.data?.errors

        if (errors) {
            errorMessage.value = Object.values(errors).flat().join(', ')
        } else {
            Toast.fire({
                icon: 'error',
                title: 'Gagal',
                text: isEdit.value
                    ? 'Gagal memperbarui data penjualan rongsok.'
                    : 'Gagal menambahkan data penjualan rongsok.',
            })
        }
    } finally {
        saving.value = false
    }
}

const isEdit = ref(false)
const editId = ref<number | null>(null)
const oldFoto = ref<string | null>(null)

const editData = (item: PenjualanRongsok) => {
    isEdit.value = true
    editId.value = item.id
    oldFoto.value = item.foto_bukti

    form.value = {
        tanggal: item.tanggal,
        periode_mulai: item.periode_mulai ?? '',
        periode_selesai: item.periode_selesai ?? '',
        total_berat: String(item.total_berat),
        total_pendapatan: String(item.total_pendapatan),
        tempat_jual: item.tempat_jual ?? '',
        keterangan: item.keterangan ?? '',
        foto_bukti: null,
    }

    window.scrollTo({
        top: 0,
        behavior: 'smooth',
    })
}

const deleteData = async (id: number) => {
    const confirmed = await Swal.fire({
        icon: 'warning',
        title: 'Yakin ingin menghapus?',
        text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
    }).then(result => result.isConfirmed)

    if (!confirmed) return

    try {
        await api.delete(`/penjualan-rongsok/${id}`)
        await Swal.fire({
            icon: 'success',
            title: 'Terhapus!',
            text: 'Data penjualan rongsok berhasil dihapus.',
            timer: 1500,
            showConfirmButton: false,
        })
        Toast.fire({
            icon: 'success',
            title: 'Data penjualan rongsok berhasil dihapus',
        })
        await loadData()
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menghapus data penjualan rongsok.',
        })
    }
}

onMounted(loadData)
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Penjualan Rongsok</h1>
                <p class="text-sm text-slate-500">
                    Catat hasil penjualan rongsok per periode untuk kas sosial.
                </p>
            </div>

            <div v-if="successMessage" class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <!-- Form -->
            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-bold text-slate-800">
                    {{ isEdit ? 'Edit Penjualan Rongsok' : 'Tambah Penjualan Rongsok' }}
                </h2>

                <form class="grid grid-cols-1 gap-5 md:grid-cols-2" @submit.prevent="submitForm">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Tanggal Penjualan</label>
                        <input v-model="form.tanggal" type="date"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                            required />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Tempat Jual</label>
                        <input v-model="form.tempat_jual" type="text" placeholder="Contoh: Pengepul Madura"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Periode Mulai</label>
                        <input v-model="form.periode_mulai" type="date"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Periode Selesai</label>
                        <input v-model="form.periode_selesai" type="date"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Total Berat / Kg</label>
                        <input v-model="form.total_berat" type="number" min="0" step="0.01" placeholder="Contoh: 120"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                            required />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Total Pendapatan</label>
                        <input v-model="form.total_pendapatan" type="number" min="0" placeholder="Contoh: 850000"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                            required />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-600">Foto Bukti</label>
                        <input type="file" accept="image/*"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" @change="handleFile" />
                    </div>
                    <div v-if="isEdit && oldFoto" class="mt-3">
                        <p class="mb-2 text-sm text-slate-500">Foto bukti saat ini:</p>
                        <img :src="getFotoUrl(oldFoto)" class="h-32 w-48 rounded-2xl object-cover" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-600">Keterangan</label>
                        <textarea v-model="form.keterangan" rows="3" placeholder="Catatan tambahan..."
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-3">
                        <button v-if="isEdit" type="button"
                            class="rounded-2xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                            @click="resetForm">
                            Batal
                        </button>

                        <button type="submit" :disabled="saving"
                            class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                            {{ saving ? 'Menyimpan...' : isEdit ? 'Update Data' : 'Simpan Data' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Table -->
            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="mb-2">
                        <h2 class="text-lg font-bold text-slate-800">Data Penjualan</h2>
                        <p class="text-sm text-slate-500">Riwayat hasil jual rongsok</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="loadData"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Refresh
                        </button>
                        <input v-model="search" type="text" placeholder="Cari data..."
                            class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-72" />

                        <select v-model="perPage"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
                            <option :value="5">5 data</option>
                            <option :value="10">10 data</option>
                            <option :value="25">25 data</option>
                        </select>
                    </div>
                </div>

                <div v-if="loading" class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                    Memuat data...
                </div>

                <div v-else class="overflow-x-auto border rounded-2xl border-slate-200">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead>
                            <tr class="bg-emerald-100 text-slate-500">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Periode</th>
                                <th class="px-4 py-3">Tempat</th>
                                <th class="px-4 py-3">Berat</th>
                                <th class="px-4 py-3">Pendapatan</th>
                                <th class="px-4 py-3">Bukti</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in paginatedData" :key="item.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">{{ index + 1 }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ item.tanggal }}</td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ item.periode_mulai ?? '-' }} s/d {{ item.periode_selesai ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ item.tempat_jual ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">{{ item.total_berat }} Kg</td>
                                <td class="px-4 py-3 font-bold text-emerald-600">
                                    {{ formatRupiah(item.total_pendapatan) }}
                                </td>
                                <td class="px-4 py-3">
                                    <a v-if="item.foto_bukti" :href="getFotoUrl(item.foto_bukti)" target="_blank"
                                        class="text-emerald-600 hover:underline">
                                        <img :src="getFotoUrl(item.foto_bukti)"
                                            class="h-14 w-20 rounded-xl object-cover" />
                                    </a>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-center gap-2">
                                        <button
                                            class="rounded-xl bg-sky-50 px-4 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100"
                                            @click="editData(item)">
                                            Edit
                                        </button>

                                        <button
                                            class="rounded-xl bg-red-50 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                                            @click="deleteData(item.id)">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="paginatedData.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    Belum ada data penjualan rongsok.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ paginatedData.length }} dari {{ filteredData.length }} data
                    </p>

                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevPage" :disabled="currentPage === 1"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Previous
                        </button>

                        <span class="text-sm font-semibold text-slate-600">
                            {{ currentPage }} / {{ totalPages || 1 }}
                        </span>

                        <button type="button" @click="nextPage"
                            :disabled="currentPage === totalPages || totalPages === 0"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
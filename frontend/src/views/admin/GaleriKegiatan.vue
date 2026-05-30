<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import Swal from 'sweetalert2'
import { Toast } from '@/utils/toast'

interface GaleriKegiatan {
    id: number
    judul: string
    deskripsi: string | null
    kategori: string
    tanggal: string
    foto: string
    sumber: string
    real_id: number | null
}

const data = ref<GaleriKegiatan[]>([])
const search = ref('')
const currentPage = ref(1)
const perPage = ref(6)

const filteredGaleri = computed(() => {
    if (!search.value) return data.value

    const keyword = search.value.toLowerCase()

    return data.value.filter((item) => {
        return (
            item.judul.toLowerCase().includes(keyword) ||
            item.kategori.toLowerCase().includes(keyword) ||
            item.sumber.toLowerCase().includes(keyword) ||
            item.tanggal.toLowerCase().includes(keyword) ||
            (item.deskripsi ?? '').toLowerCase().includes(keyword)
        )
    })
})

const totalPages = computed(() => {
    return Math.ceil(filteredGaleri.value.length / perPage.value)
})

const paginatedGaleri = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value

    return filteredGaleri.value.slice(start, end)
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
const loading = ref(false)
const saving = ref(false)
const isEdit = ref(false)
const editId = ref<number | null>(null)
const oldFoto = ref<string | null>(null)

const successMessage = ref('')
const errorMessage = ref('')

const form = ref({
    judul: '',
    deskripsi: '',
    kategori: 'pengambilan_rongsok',
    tanggal: '',
    foto: null as File | null,
    sumber: 'Galeri Kegiatan',
})

const kategoriOptions = [
    { value: 'pengambilan_rongsok', label: 'Pengambilan Rongsok' },
    { value: 'penjualan_rongsok', label: 'Penjualan Rongsok' },
    { value: 'kegiatan_sosial', label: 'Kegiatan Sosial' },
    { value: 'penyaluran_dana', label: 'Penyaluran Dana' },
]

const getFotoUrl = (foto: string | null) => {
    if (!foto) return ''
    return `http://localhost:8000/storage/${foto}`
}

const formatKategori = (kategori: string) => {
    return kategori.replaceAll('_', ' ')
}

const resetForm = () => {
    form.value = {
        judul: '',
        deskripsi: '',
        kategori: 'pengambilan_rongsok',
        tanggal: '',
        foto: null,
        sumber: 'Galeri Kegiatan',
    }

    isEdit.value = false
    editId.value = null
    oldFoto.value = null
}

const handleFile = (event: Event) => {
    const target = event.target as HTMLInputElement
    form.value.foto = target.files?.[0] ?? null
}

const loadData = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/galeri-semua-bukti')
        data.value = res.data.data
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal memuat data galeri kegiatan.',
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

        payload.append('judul', form.value.judul)
        payload.append('deskripsi', form.value.deskripsi)
        payload.append('kategori', form.value.kategori)
        payload.append('tanggal', form.value.tanggal)

        if (form.value.foto) {
            payload.append('foto', form.value.foto)
        }

        if (isEdit.value && editId.value) {
            payload.append('_method', 'PUT')

            await api.post(`/galeri-kegiatan/${editId.value}`, payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })

            Toast.fire({
                icon: 'success',
                title: 'Galeri kegiatan berhasil diperbarui',
            })
        } else {
            await api.post('/galeri-kegiatan', payload, {
                headers: { 'Content-Type': 'multipart/form-data' },
            })

            Toast.fire({
                icon: 'success',
                title: 'Galeri kegiatan berhasil ditambahkan',
            })
        }

        resetForm()
        await loadData()
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: isEdit.value
                ? 'Gagal memperbarui galeri kegiatan.'
                : 'Gagal menyimpan galeri kegiatan.',
        })
    } finally {
        saving.value = false
    }
}

const editData = (item: GaleriKegiatan) => {
    if (item.sumber !== 'Galeri Kegiatan' || !item.real_id) return

    isEdit.value = true
    editId.value = item.real_id
    oldFoto.value = item.foto

    form.value = {
        judul: item.judul,
        deskripsi: item.deskripsi ?? '',
        kategori: item.kategori,
        tanggal: item.tanggal,
        foto: null,
        sumber: 'Galeri Kegiatan',
    }

    window.scrollTo({ top: 0, behavior: 'smooth' })
}

const deleteData = async (id: number | null) => {
    if (!id) return

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
        await api.delete(`/galeri-kegiatan/${id}`)
        Toast.fire({
            icon: 'success',
            title: 'Galeri kegiatan berhasil dihapus',
        })
        await loadData()
    } catch (error) {
        console.error(error)
            await Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal menghapus galeri kegiatan.',
            })
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menghapus galeri kegiatan.',
        })
    }
}

onMounted(loadData)
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Galeri Kegiatan</h1>
                <p class="text-sm text-slate-500">
                    Dokumentasi pengambilan rongsok, penjualan rongsok, kegiatan sosial, dan penyaluran dana.
                </p>
            </div>

            <div v-if="successMessage" class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-bold text-slate-800">
                    {{ isEdit ? 'Edit Galeri Kegiatan' : 'Tambah Galeri Kegiatan' }}
                </h2>

                <form class="grid grid-cols-1 gap-5 md:grid-cols-2" @submit.prevent="submitForm">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Judul</label>
                        <input v-model="form.judul" type="text" required
                            placeholder="Contoh: Pengambilan rongsok periode Mei"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Tanggal</label>
                        <input v-model="form.tanggal" type="date" required
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Kategori</label>
                        <select v-model="form.kategori" required
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500">
                            <option v-for="item in kategoriOptions" :key="item.value" :value="item.value">
                                {{ item.label }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-600">Foto</label>
                        <input type="file" accept="image/*" :required="!isEdit"
                            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm" @change="handleFile" />
                    </div>

                    <div v-if="isEdit && oldFoto" class="md:col-span-2">
                        <p class="mb-2 text-sm text-slate-500">Foto saat ini:</p>
                        <img :src="getFotoUrl(oldFoto)" class="h-40 w-64 rounded-2xl object-cover" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-600">Deskripsi</label>
                        <textarea v-model="form.deskripsi" rows="3" placeholder="Keterangan kegiatan..."
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
                            {{ saving ? 'Menyimpan...' : isEdit ? 'Update Galeri' : 'Simpan Galeri' }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-800">Data Galeri</h2>
                    <p class="text-sm text-slate-500">Foto dokumentasi kegiatan sosial dan rongsok.</p>
                </div>
                <div class="mt-4 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input v-model="search" type="text" placeholder="Cari judul, kategori, sumber, tanggal..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-80" />

                    <select v-model="perPage"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        <option :value="6">6 data</option>
                        <option :value="9">9 data</option>
                        <option :value="12">12 data</option>
                    </select>
                </div>

                <div v-if="loading" class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                    Memuat galeri...
                </div>

                <div v-else-if="filteredGaleri.length > 0" class="mt-5 rounded-2xl custom-scroll max-h-[720px] overflow-y-auto pr-2">
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div v-for="item in paginatedGaleri" :key="item.id"
                            class="mt-5 mb-5 overflow-hidden rounded-2xl shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
                            <a v-if="item.foto" :href="getFotoUrl(item.foto)" target="_blank"
                                class="text-emerald-600 hover:underline">
                                <img :src="getFotoUrl(item.foto)" :alt="item.judul" class="h-52 w-full object-cover" />
                            </a>

                            <div class="space-y-3 p-4 bg-white">
                                <span
                                    class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold capitalize text-emerald-700">
                                    {{ formatKategori(item.kategori) }}
                                </span>
                                <span
                                    class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-600">
                                    {{ item.sumber }}
                                </span>

                                <div>
                                    <h3 class="font-bold text-slate-800">{{ item.judul }}</h3>
                                    <p class="text-sm text-slate-500">{{ item.tanggal }}</p>
                                </div>

                                <p class="line-clamp-2 text-sm text-slate-500">
                                    {{ item.deskripsi ?? 'Tidak ada deskripsi.' }}
                                </p>

                                <div v-if="item.sumber === 'Galeri Kegiatan'" class="flex justify-end gap-2 pt-2">
                                    <button
                                        class="rounded-xl bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100"
                                        @click="editData(item)">
                                        Edit
                                    </button>

                                    <button
                                        class="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                                        @click="deleteData(item.real_id)">
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else-if="!loading && data.length === 0"
                    class="rounded-3xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                    Belum ada data galeri kegiatan.
                </div>
                <div v-else
                    class="rounded-3xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                    Belum ada data galeri kegiatan.
                </div>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ paginatedGaleri.length }} dari {{ filteredGaleri.length }} data
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
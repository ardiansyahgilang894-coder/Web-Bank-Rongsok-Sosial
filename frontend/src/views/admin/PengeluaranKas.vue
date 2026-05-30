<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import Swal from 'sweetalert2'
import {Toast} from '@/utils/toast'

interface PengeluaranKas {
  id: number
  tanggal: string
  keperluan: string
  deskripsi: string | null
  nominal: number
  foto: string | null
}

const data = ref<PengeluaranKas[]>([])
const loading = ref(false)
const saving = ref(false)
const isEdit = ref(false)
const editId = ref<number | null>(null)
const oldFoto = ref<string | null>(null)

const successMessage = ref('')
const errorMessage = ref('')

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

const form = ref({
  tanggal: '',
  keperluan: '',
  deskripsi: '',
  nominal: '',
  foto: null as File | null,
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

const resetForm = () => {
  form.value = {
    tanggal: '',
    keperluan: '',
    deskripsi: '',
    nominal: '',
    foto: null,
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
    const res = await api.get('/pengeluaran-kas')
    data.value = res.data.data
  } catch (error) {
    console.error(error)
    Toast.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal memuat data pengeluaran kas.',
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
    payload.append('keperluan', form.value.keperluan)
    payload.append('deskripsi', form.value.deskripsi)
    payload.append('nominal', form.value.nominal)

    if (form.value.foto) {
      payload.append('foto', form.value.foto)
    }

    if (isEdit.value && editId.value) {
      payload.append('_method', 'PUT')

      await api.post(`/pengeluaran-kas/${editId.value}`, payload, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      Toast.fire({
        icon: 'success',
        title: 'Data pengeluaran kas berhasil diperbarui',
      })
    } else {
      await api.post('/pengeluaran-kas', payload, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      })

      Toast.fire({
        icon: 'success',
        title: 'Data pengeluaran kas berhasil ditambahkan',
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
          ? 'Gagal memperbarui data pengeluaran kas.'
          : 'Gagal menambahkan data pengeluaran kas.',
      })
    }
  } finally {
    saving.value = false
  }
}

const editData = (item: PengeluaranKas) => {
  isEdit.value = true
  editId.value = item.id
  oldFoto.value = item.foto

  form.value = {
    tanggal: item.tanggal,
    keperluan: item.keperluan,
    deskripsi: item.deskripsi ?? '',
    nominal: String(item.nominal),
    foto: null,
  }

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const deleteData = async (id: number) => {
  const confirmed = await Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data pengeluaran kas yang dihapus tidak dapat dikembalikan.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => result.isConfirmed)

  if (!confirmed) return

  try {
    await api.delete(`/pengeluaran-kas/${id}`)

    Toast.fire({
      icon: 'success',
      title: 'Data pengeluaran kas berhasil dihapus',
    })
    await loadData()
  } catch (error) {
    console.error(error)
    Toast.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal menghapus data pengeluaran kas.',
    })
  }
}

onMounted(loadData)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Pengeluaran Kas Sosial</h1>
        <p class="text-sm text-slate-500">
          Catat penggunaan dana sosial untuk bantuan, kegiatan sosial, dan keperluan lainnya.
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
          {{ isEdit ? 'Edit Pengeluaran Kas' : 'Tambah Pengeluaran Kas' }}
        </h2>

        <form class="grid grid-cols-1 gap-5 md:grid-cols-2" @submit.prevent="submitForm">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-600">Tanggal</label>
            <input v-model="form.tanggal" type="date" required
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
          </div>

          <div>
            <label class="mb-2 block text-sm font-medium text-slate-600">Nominal</label>
            <input v-model="form.nominal" type="number" min="0" required placeholder="Contoh: 500000"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-600">Keperluan</label>
            <input v-model="form.keperluan" type="text" required placeholder="Contoh: Sumbangan untuk warga sakit"
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-600">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="3" placeholder="Keterangan detail pengeluaran..."
              class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2 block text-sm font-medium text-slate-600">Foto Bukti</label>
            <input type="file" accept="image/*" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm"
              @change="handleFile" />

            <div v-if="isEdit && oldFoto" class="mt-3">
              <p class="mb-2 text-sm text-slate-500">Foto bukti saat ini:</p>
              <img :src="getFotoUrl(oldFoto)" class="h-32 w-48 rounded-2xl object-cover" />
            </div>
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

      <section class="rounded-3xl bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="mb-2">
            <h2 class="text-lg font-bold text-slate-800">Data Pengeluaran Kas</h2>
            <p class="text-sm text-slate-500">Riwayat penggunaan dana sosial</p>
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
          <table class="w-full min-w-[850px] text-left text-sm">
            <thead>
              <tr class="bg-emerald-100 text-slate-600">
                <th class="px-4 py-3">No</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Keperluan</th>
                <th class="px-4 py-3">Nominal</th>
                <th class="px-4 py-3">Bukti</th>
                <th class="px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(item, index) in paginatedData" :key="item.id" class="hover:bg-slate-50">
                <td class="px-4 py-3">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-slate-700">{{ item.tanggal }}</td>
                <td class="px-4 py-3">
                  <p class="font-semibold text-slate-800">{{ item.keperluan }}</p>
                  <p class="line-clamp-1 text-xs text-slate-500">
                    {{ item.deskripsi ?? '-' }}
                  </p>
                </td>
                <td class="px-4 py-3 font-bold text-amber-600">
                  {{ formatRupiah(item.nominal) }}
                </td>
                <td class="px-4 py-3">
                  <a v-if="item.foto" :href="getFotoUrl(item.foto)" target="_blank">
                    <img :src="getFotoUrl(item.foto)" class="h-14 w-20 rounded-xl object-cover" />
                  </a>
                  <span v-else>Tidak ada foto</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-center gap-2">
                    <button class="rounded-xl bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-600 hover:bg-sky-100"
                      @click="editData(item)">
                      Edit
                    </button>

                    <button class="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100"
                      @click="deleteData(item.id)">
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="paginatedData.length === 0">
                <td colspan="6" class="py-8 text-center text-slate-500">
                  Belum ada data pengeluaran kas.
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
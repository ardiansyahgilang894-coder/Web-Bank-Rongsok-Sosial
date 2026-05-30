<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import Swal from 'sweetalert2'
import { Toast } from '@/utils/toast'

interface User {
    id: number
    name: string
    email: string
    role: 'admin' | 'petugas' | 'user'
    status: 'aktif' | 'nonaktif'
    email_verified_at: string | null
    created_at: string
}

const currentUser = JSON.parse(localStorage.getItem('user') || '{}')

const users = ref<User[]>([])
const search = ref('')
const currentPage = ref(1)
const perPage = ref(5)

const filteredUsers = computed(() => {
    if (!search.value) return users.value

    const keyword = search.value.toLowerCase()

    return users.value.filter((user) =>
        JSON.stringify(user).toLowerCase().includes(keyword)
    )
})

const totalPages = computed(() => {
    return Math.ceil(filteredUsers.value.length / perPage.value)
})

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    const end = start + perPage.value

    return filteredUsers.value.slice(start, end)
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
const errorMessage = ref('')
const successMessage = ref('')

const loadUsers = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.get('/users')
        users.value = res.data.data
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal memuat data user.',
        })
    } finally {
        loading.value = false
    }
}


const updateRole = async (id: number, role: string) => {
    try {
        await api.patch(`/users/${id}/role`, { role })
        Toast.fire({
            icon: 'success',
            title: 'Role user berhasil diperbarui'
        })
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal memperbarui role user.',
        })
    }
}

const updateStatus = async (id: number, status: string) => {
    errorMessage.value = ''
    successMessage.value = ''

    try {
        await api.patch(`/users/${id}/status`, { status })

        Toast.fire({
            icon: 'success',
            title: 'Status user berhasil diperbarui'
        })

    } catch (error: any) {
        console.error(error)

        errorMessage.value =
            error.response?.data?.message || 'Gagal memperbarui status'
        await loadUsers()
    }
}

const deleteUser = async (id: number) => {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: 'Data user akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal',
    }).then(async (result) => {
        if (result.isConfirmed) {
            await performDelete(id)
        }
    })
}

const performDelete = async (id: number) => {

    try {
        await api.delete(`/users/${id}`)
        Toast.fire({
            icon: 'success',
            title: 'User berhasil dihapus'
        })
    } catch (error) {
        console.error(error)
        Toast.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menghapus user.',
        })
    }
}

onMounted(loadUsers)
</script>

<template>
    <AdminLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Manajemen User</h1>
                <p class="text-sm text-slate-500">
                    Kelola akun, role, dan status pengguna sistem.
                </p>
            </div>

            <div v-if="successMessage" class="rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ successMessage }}
            </div>

            <div v-if="errorMessage" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                {{ errorMessage }}
            </div>

            <section class="rounded-3xl bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <input v-model="search" type="text" placeholder="Cari nama, email, atau role..."
                        class="w-full rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 sm:w-80" />

                    <select v-model="perPage"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        <option :value="5">5 data</option>
                        <option :value="10">10 data</option>
                        <option :value="25">25 data</option>
                    </select>
                </div>
                <div v-if="loading" class="rounded-2xl bg-slate-50 p-5 text-sm text-slate-500">
                    Memuat data user...
                </div>

                <div v-else class="overflow-x-auto border rounded-2xl border-slate-200">
                    <table class="w-full min-w-225 text-sm">
                        <thead class="bg-emerald-100 text-slate-600 text-left">
                            <tr class="text-slate-500">
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Verifikasi</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(user, index) in paginatedUsers" :key="user.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">
                                    {{ index + 1 + (currentPage - 1) * perPage }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 text-sm font-medium capitalize">
                                    {{ user.name }}
                                    <span :class="user.status === 'aktif'
                                        ? 'bg-emerald-100 text-emerald-700'
                                        : 'bg-red-100 text-red-700'"
                                        class="ml-2 rounded-full px-2 text-xs font-semibold">
                                        {{ user.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ user.email }}
                                </td>

                                <td class="px-4 py-3">
                                    <select v-model="user.role" :disabled="user.id === currentUser.id"
                                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 disabled:text-slate-400"
                                        @change="updateRole(user.id, user.role)">
                                        <option value="admin">Admin</option>
                                        <option value="petugas">Petugas</option>
                                        <option value="user">User</option>
                                    </select>
                                </td>

                                <td class="px-4 py-3">
                                    <select v-model="user.status" :disabled="user.id === currentUser.id"
                                        class="rounded-xl border border-slate-200 px-3 py-2 text-sm disabled:bg-slate-100 disabled:text-slate-400"
                                        @change="(e) => updateStatus(user.id, (e.target as HTMLSelectElement).value)">
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </td>

                                <td class="px-4 py-3">
                                    <span v-if="user.email_verified_at"
                                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        Terverifikasi
                                    </span>

                                    <span v-else
                                        class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        Belum
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <button :disabled="user.id === currentUser.id"
                                        class="rounded-xl bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-40"
                                        @click="deleteUser(user.id)">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="paginatedUsers.length === 0">
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Data user tidak ditemukan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-500">
                        Menampilkan {{ paginatedUsers.length }} dari {{ filteredUsers.length }} data
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
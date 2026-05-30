<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Swal from 'sweetalert2'

const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')

const form = ref({
    email: '',
    password: '',
})

const login = async () => {
    loading.value = true
    errorMessage.value = ''

    try {
        const res = await api.post('/login', form.value)

        localStorage.setItem('token', res.data.token)
        localStorage.setItem('user', JSON.stringify(res.data.user))

        const user = res.data.user

        if (user.role === 'admin' || user.role === 'petugas') {
            router.push('/admin/dashboard')
        } else {
            router.push('/')
        }
    } catch (error: any) {
    console.error(error.response?.data)

    if (
        error.response?.status === 403 &&
        error.response?.data?.status === 'inactive'
    ) {
        Swal.fire({
            icon: 'warning',
            title: 'Akun Dinonaktifkan',
            text: error.response.data.message,
            confirmButtonColor: '#10b981',
        })

        return
    }

    errorMessage.value = 'Email atau password salah'
}
    finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
            <section
                class="hidden bg-linear-to-br from-emerald-600 to-sky-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-2xl">
                        <img src="/public/logo/Loopit.png" alt="Logo" class="h-full mt-1 w-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">Bank Rongsok Sosial</h1>
                        <p class="text-sm opacity-80">Transparansi Dana Sosial</p>
                    </div>
                </div>

                <div>
                    <h2 class="max-w-xl text-4xl font-extrabold leading-tight">
                        Kelola hasil jual rongsok untuk kegiatan sosial secara transparan.
                    </h2>
                    <p class="mt-5 max-w-lg text-sm leading-7 opacity-90">
                        Admin dan Petugas dapat mencatat penjualan rongsok, pemasukan kas, pengeluaran sosial,
                        galeri kegiatan, dan laporan bulanan.
                    </p>
                </div>

                <p class="text-sm opacity-80">
                    Tidak ada laba dibagikan. Seluruh dana digunakan untuk kegiatan sosial.
                </p>
            </section>

            <section class="flex items-center justify-center px-5 py-10">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center lg:text-center">
                        <div
                            class="mx-auto flex h-30 w-30 items-center justify-center shadow-emerald-500 rounded-2xl text-2xl text-white">
                            <img src="/public/logo/Loopit.png" alt="Logo" class="h-full w-full object-contain">
                        </div>
                        <h2 class="text-3xl font-extrabold text-slate-900">
                            Masuk Admin & Petugas
                        </h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Silakan login untuk mengelola data Bank Rongsok Sosial.
                        </p>
                    </div>

                    <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>

                    <form class="rounded-3xl bg-white p-6 shadow-xl shadow-slate-200" @submit.prevent="login">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-600">
                                Email
                            </label>
                            <input v-model="form.email" type="email" required placeholder="admin@email.com"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-medium text-slate-600">
                                Password
                            </label>
                            <input v-model="form.password" type="password" required placeholder="Masukkan password"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <button type="submit" :disabled="loading"
                            class="mt-6 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60">
                            {{ loading ? 'Memproses...' : 'Masuk Dashboard' }}
                        </button>

                        <RouterLink to="/forgot-password"
                            class="mt-3 flex text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                            Lupa password?
                        </RouterLink>
                        <RouterLink to="/register"
                            class="mt-3 flex text-sm font-semibold text-slate-500 hover:text-emerald-600">
                            Belum punya akun? Daftar
                        </RouterLink>
                        <RouterLink to="/"
                            class="mt-5 block text-center text-sm font-semibold text-emerald-600 hover:text-emerald-700">
                            Kembali ke halaman publik
                        </RouterLink>
                    </form>
                </div>
            </section>
        </div>
    </div>
</template>
<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import Swal from 'sweetalert2'

const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const register = async () => {
    loading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    if (form.value.password !== form.value.password_confirmation) {
        errorMessage.value = 'Konfirmasi password tidak sama'
        loading.value = false
        return
    }

    try {
        await api.post('/register', form.value)

        localStorage.setItem('otp_email', form.value.email)
        localStorage.setItem('otp_mode', 'register')

        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Kode OTP berhasil dikirim',
            timer: 1500,
            showConfirmButton: false,
        })

        setTimeout(() => {
            router.push('/verify-otp')
        }, 1000)
    } catch (error: any) {
        console.log(error.response?.data)
        console.log(error.response?.data?.message)
        console.log(error.response?.data?.error)
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <div class="grid min-h-screen grid-cols-1 lg:grid-cols-2">
            <section
                class="hidden bg-gradient-to-br from-emerald-600 to-sky-600 p-10 text-white lg:flex lg:flex-col lg:justify-between">
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
                        Bergabung untuk mendukung transparansi dana sosial.
                    </h2>
                    <p class="mt-5 max-w-lg text-sm leading-7 opacity-90">
                        Akun digunakan untuk mengakses sistem sesuai role yang diberikan admin.
                    </p>
                </div>

                <p class="text-sm opacity-80">
                    Seluruh hasil jual rongsok digunakan untuk kegiatan sosial.
                </p>
            </section>

            <section class="flex items-center justify-center px-5 py-10">
                <div class="w-full max-w-md">
                    <div class="mb-8 text-center lg:text-left">
                        <div
                            class="mx-auto flex h-30 w-30 items-center justify-center shadow-emerald-500 rounded-2xl text-2xl text-white">
                            <img src="/public/logo/Loopit.png" alt="Logo" class="h-full w-full object-contain">
                        </div>
                        <h2 class="text-3xl font-extrabold text-slate-900 text-center">
                            Buat Akun
                        </h2>
                        <p class="mt-2 text-sm text-slate-500 text-center">
                            Daftar akun dan verifikasi email menggunakan OTP.
                        </p>
                    </div>

                    <div v-if="successMessage" class="mb-5 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-700">
                        {{ successMessage }}
                    </div>

                    <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-50 p-4 text-sm text-red-700">
                        {{ errorMessage }}
                    </div>

                    <form class="rounded-3xl bg-white p-6 shadow-xl shadow-slate-200" @submit.prevent="register">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-600">Nama</label>
                            <input v-model="form.name" type="text" required placeholder="Nama lengkap"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-medium text-slate-600">Email</label>
                            <input v-model="form.email" type="email" required placeholder="email@example.com"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-medium text-slate-600">Password</label>
                            <input v-model="form.password" type="password" required minlength="8"
                                placeholder="Minimal 8 karakter"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-medium text-slate-600">Konfirmasi Password</label>
                            <input v-model="form.password_confirmation" type="password" required minlength="8"
                                placeholder="Ulangi password"
                                class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500" />
                        </div>

                        <button type="submit" :disabled="loading"
                            class="mt-6 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60">
                            {{ loading ? 'Memproses...' : 'Daftar & Kirim OTP' }}
                        </button>

                        <RouterLink to="/login"
                            class="mt-5 block text-center text-sm font-semibold text-emerald-600 hover:underline">
                            Sudah punya akun? Login
                        </RouterLink>
                    </form>
                </div>
            </section>
        </div>
    </div>
</template>
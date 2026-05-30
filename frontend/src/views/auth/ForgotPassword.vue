<script setup lang="ts">
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import Swal from 'sweetalert2'
import api from '@/services/api'
import { EnvelopeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
import { useRouter } from 'vue-router'

const router = useRouter()

const email = ref('')
const loading = ref(false)

const forgotPassword = async () => {
  try {
    await api.post('/forgot-password', {
      email: email.value,
    })

    // SIMPAN DATA UNTUK HALAMAN OTP
    localStorage.setItem('otp_email', email.value)
    localStorage.setItem('otp_mode', 'reset')

    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Kode OTP berhasil dikirim',
      timer: 1500,
      showConfirmButton: false,
    })

    setTimeout(() => {
      router.push('/verify-otp')
    }, 1500)

  } catch (error) {
    console.error(error)
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-sm">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-100">
          <EnvelopeIcon class="h-8 w-8 text-emerald-600" />
        </div>

        <h1 class="text-2xl font-bold text-slate-800">
          Lupa Password
        </h1>

        <p class="mt-2 text-sm text-slate-500">
          Masukkan email akun kamu untuk mendapatkan instruksi reset password.
        </p>
      </div>

      <form class="space-y-5" @submit.prevent="forgotPassword">
        <div>
          <label class="mb-2 block text-sm font-medium text-slate-600">
            Email
          </label>

          <input
            v-model="email"
            type="email"
            placeholder="Masukkan email kamu"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60"
        >
          {{ loading ? 'Mengirim...' : 'Kirim Instruksi Reset' }}
        </button>
      </form>

      <div class="mt-6 text-center">
        <RouterLink
          to="/login"
          class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-600 hover:text-emerald-700"
        >
          <ArrowLeftIcon class="h-4 w-4" />
          Kembali ke Login
        </RouterLink>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const loading = ref(false)
const resending = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const email = ref('')
const otp = ref('')

onMounted(() => {
  email.value = localStorage.getItem('otp_email') ?? ''
  mode.value = (localStorage.getItem('otp_mode') as 'register' | 'reset') ?? 'register'

  if (!email.value) {
    router.push('/login')
  }
})

const mode = ref<'register' | 'reset'>('register')

const verifyOtp = async () => {
  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    if (mode.value === 'register') {
      await api.post('/verify-otp', {
        email: email.value,
        otp: otp.value,
      })

      localStorage.removeItem('otp_email')
      localStorage.removeItem('otp_mode')

      successMessage.value = 'Verifikasi berhasil. Silakan login.'

      setTimeout(() => {
        router.push('/login')
      }, 1000)

      return
    }

    if (mode.value === 'reset') {
      const res = await api.post('/verify-reset-otp', {
        email: email.value,
        otp: otp.value,
      })

      sessionStorage.setItem('reset_email', email.value)
      sessionStorage.setItem('reset_token', res.data.reset_token)

      localStorage.removeItem('otp_email')
      localStorage.removeItem('otp_mode')

      successMessage.value = 'OTP valid. Silakan buat password baru.'

      setTimeout(() => {
        router.push('/reset-password')
      }, 1000)

      return
    }
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Kode OTP salah atau sudah kedaluwarsa.'
  } finally {
    loading.value = false
  }
}

const resendOtp = async () => {
  resending.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.post('/resend-otp', {
      email: email.value,
    })

    successMessage.value = 'Kode OTP baru berhasil dikirim.'
  } catch (error) {
    console.error(error)
    errorMessage.value = 'Gagal mengirim ulang OTP.'
  } finally {
    resending.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-50 px-5">
    <div class="w-full max-w-md">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-2xl text-white">
          ✉️
        </div>
        <h1 class="text-3xl font-extrabold text-slate-900">
          Verifikasi OTP
        </h1>
        <p class="mt-2 text-sm text-slate-500">
          Masukkan kode OTP yang dikirim ke:
        </p>
        <p class="mt-1 font-semibold text-emerald-600">
          {{ email }}
        </p>
      </div>

      <div v-if="successMessage" class="mb-5 rounded-2xl bg-emerald-50 p-4 text-sm text-emerald-700">
        {{ successMessage }}
      </div>

      <div v-if="errorMessage" class="mb-5 rounded-2xl bg-red-50 p-4 text-sm text-red-700">
        {{ errorMessage }}
      </div>

      <form class="rounded-3xl bg-white p-6 shadow-xl shadow-slate-200" @submit.prevent="verifyOtp">
        <label class="mb-2 block text-sm font-medium text-slate-600">
          Kode OTP
        </label>

        <input
          v-model="otp"
          type="text"
          maxlength="6"
          required
          placeholder="Masukkan 6 digit OTP"
          class="w-full rounded-2xl border border-slate-200 px-4 py-4 text-center text-2xl font-bold tracking-[0.5em] outline-none focus:border-emerald-500"
        />

        <button
          type="submit"
          :disabled="loading"
          class="mt-6 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60"
        >
          {{ loading ? 'Memverifikasi...' : 'Verifikasi Akun' }}
        </button>

        <button
          type="button"
          :disabled="resending"
          class="mt-4 w-full rounded-2xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 disabled:opacity-60"
          @click="resendOtp"
        >
          {{ resending ? 'Mengirim...' : 'Kirim Ulang OTP' }}
        </button>

        <RouterLink
          to="/login"
          class="mt-5 block text-center text-sm font-semibold text-emerald-600 hover:underline"
        >
          Kembali ke Login
        </RouterLink>
      </form>
    </div>
  </div>
</template>
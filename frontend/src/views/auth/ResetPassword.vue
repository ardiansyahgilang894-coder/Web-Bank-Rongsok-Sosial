<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import Swal from 'sweetalert2'
import api from '@/services/api'
import { LockClosedIcon } from '@heroicons/vue/24/outline'

const router = useRouter()

const email = ref('')
const resetToken = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)

onMounted(() => {
  email.value = sessionStorage.getItem('reset_email') ?? ''
  resetToken.value = sessionStorage.getItem('reset_token') ?? ''

  if (!email.value || !resetToken.value) {
    router.push('/forgot-password')
  }
})

const resetPassword = async () => {
  if (!password.value || !passwordConfirmation.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Password wajib diisi',
    })
    return
  }

  if (password.value !== passwordConfirmation.value) {
    Swal.fire({
      icon: 'warning',
      title: 'Konfirmasi password tidak sama',
    })
    return
  }

  try {
    loading.value = true

    await api.post('/reset-password', {
      email: email.value,
      reset_token: resetToken.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })

    sessionStorage.removeItem('reset_email')
    sessionStorage.removeItem('reset_token')

    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Password berhasil diubah. Silakan login.',
      timer: 1500,
      showConfirmButton: false,
    })

    setTimeout(() => {
      router.push('/login')
    }, 1500)
  } catch (error) {
    console.error(error)

    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Token tidak valid atau sudah kedaluwarsa.',
    })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-50 px-5">
    <div class="w-full max-w-md">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-600 text-white">
          <LockClosedIcon class="h-7 w-7" />
        </div>

        <h1 class="text-3xl font-extrabold text-slate-900">
          Reset Password
        </h1>

        <p class="mt-2 text-sm text-slate-500">
          Buat password baru untuk akun:
        </p>

        <p class="mt-1 font-semibold text-emerald-600">
          {{ email }}
        </p>
      </div>

      <form
        class="rounded-3xl bg-white p-6 shadow-xl shadow-slate-200"
        @submit.prevent="resetPassword"
      >
        <div>
          <label class="mb-2 block text-sm font-medium text-slate-600">
            Password Baru
          </label>

          <input
            v-model="password"
            type="password"
            required
            placeholder="Masukkan password baru"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500"
          />
        </div>

        <div class="mt-4">
          <label class="mb-2 block text-sm font-medium text-slate-600">
            Konfirmasi Password
          </label>

          <input
            v-model="passwordConfirmation"
            type="password"
            required
            placeholder="Ulangi password baru"
            class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-500"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="mt-6 w-full rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60"
        >
          {{ loading ? 'Menyimpan...' : 'Reset Password' }}
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
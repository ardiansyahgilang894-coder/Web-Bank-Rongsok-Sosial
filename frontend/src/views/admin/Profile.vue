<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api'
import Swal from 'sweetalert2'
import {
  UserCircleIcon,
  EnvelopeIcon,
  LockClosedIcon,
  ShieldCheckIcon,
} from '@heroicons/vue/24/outline'

const loading = ref(false)
const saving = ref(false)

const form = ref({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
})

const loadProfile = async () => {
  loading.value = true

  try {
    const res = await api.get('/profile')

    form.value.name = res.data.data.name
    form.value.email = res.data.data.email
    form.value.role = res.data.data.role
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const updateProfile = async () => {
  saving.value = true

  try {
    const payload: any = {
      name: form.value.name,
      email: form.value.email,
    }

    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    const res = await api.put('/profile', payload)

    localStorage.setItem('user', JSON.stringify(res.data.data))

    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Profile berhasil diperbarui',
      timer: 1500,
      showConfirmButton: false,
    })

    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (error: any) {
    console.error(error.response?.data)

    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: error.response?.data?.message || 'Profile gagal diperbarui',
    })
  } finally {
    saving.value = false
  }
}

onMounted(loadProfile)
</script>

<template>
  <AdminLayout>
    <div class="space-y-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Edit Profile</h1>
        <p class="text-sm text-slate-500">
          Kelola informasi akun yang sedang login.
        </p>
      </div>

      <section class="rounded-3xl bg-white p-6 shadow-sm">
        <div v-if="loading" class="text-sm text-slate-500">
          Memuat profile...
        </div>

        <form v-else class="space-y-5" @submit.prevent="updateProfile">
          <div class="flex items-center gap-4 border-b border-slate-100 pb-6">
            <UserCircleIcon class="h-20 w-20 text-emerald-600" />

            <div>
              <h2 class="text-xl font-bold text-slate-800">
                {{ form.name }}
              </h2>

              <div class="mt-2 inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-sm font-semibold text-sky-700 capitalize">
                <ShieldCheckIcon class="h-4 w-4" />
                {{ form.role }}
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-medium text-slate-600">
                Nama
              </label>

              <div class="relative">
                <UserCircleIcon class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" />

                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 pl-11 text-sm outline-none focus:border-emerald-500"
                />
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-600">
                Email
              </label>

              <div class="relative">
                <EnvelopeIcon class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" />

                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 pl-11 text-sm outline-none focus:border-emerald-500"
                />
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-600">
                Password Baru
              </label>

              <div class="relative">
                <LockClosedIcon class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" />

                <input
                  v-model="form.password"
                  type="password"
                  placeholder="Kosongkan jika tidak diganti"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 pl-11 text-sm outline-none focus:border-emerald-500"
                />
              </div>
            </div>

            <div>
              <label class="mb-2 block text-sm font-medium text-slate-600">
                Konfirmasi Password Baru
              </label>

              <div class="relative">
                <LockClosedIcon class="absolute left-4 top-3.5 h-5 w-5 text-slate-400" />

                <input
                  v-model="form.password_confirmation"
                  type="password"
                  placeholder="Ulangi password baru"
                  class="w-full rounded-2xl border border-slate-200 px-4 py-3 pl-11 text-sm outline-none focus:border-emerald-500"
                />
              </div>
            </div>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="saving"
              class="rounded-2xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </AdminLayout>
</template>
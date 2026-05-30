<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const sidebarOpen = ref(false)

import Swal from 'sweetalert2'

import {
    HomeIcon,
    ShoppingCartIcon,
    BanknotesIcon,
    ArrowDownCircleIcon,
    PhotoIcon,
    DocumentChartBarIcon,
    UsersIcon,
    UserCircleIcon,
    ChevronDownIcon,
    UserIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

const user = JSON.parse(localStorage.getItem('user') || '{}')

const profileDropdownOpen = ref(false)

const toggleProfileDropdown = () => {
    profileDropdownOpen.value = !profileDropdownOpen.value
}

const logout = async () => {
    const result = await Swal.fire({
        title: 'Logout?',
        text: 'Kamu yakin ingin keluar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, logout',
    })

    if (!result.isConfirmed) return

    try {
        await api.post('/logout')
    } catch (error) {
        console.error(error)
    }

    localStorage.removeItem('token')
    localStorage.removeItem('user')

    router.push('/login')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Mobile Overlay -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="sidebarOpen = false" />

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 z-40 h-screen w-72 transform bg-white shadow-xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
            <div class="flex h-20 items-center gap-3 px-6">
                <div class="flex h-18 w-18 items-center mt-2 justify-center rounded-2xl text-white">
                    <img src="/public/logo/Loopit.png" alt="Logo">
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Bank Rongsok</h1>
                    <p class="text-xs text-slate-500">Dana Sosial Transparan</p>
                </div>
            </div>

            <nav class="space-y-5 p-6">

                <RouterLink to="/admin/dashboard" class="menu-link group" active-class="menu-active">
                    <HomeIcon class="icon-menu" />
                    Dashboard
                </RouterLink>

                <RouterLink to="/admin/penjualan-rongsok" class="menu-link group" active-class="menu-active">
                    <ShoppingCartIcon class="icon-menu" />
                    Penjualan Rongsok
                </RouterLink>

                <RouterLink to="/admin/pemasukan-kas" class="menu-link group" active-class="menu-active">
                    <BanknotesIcon class="icon-menu" />
                    Pemasukan Kas
                </RouterLink>

                <RouterLink to="/admin/pengeluaran-kas" class="menu-link group" active-class="menu-active">
                    <ArrowDownCircleIcon class="icon-menu" />
                    Pengeluaran Kas
                </RouterLink>

                <RouterLink to="/admin/galeri-kegiatan" class="menu-link group" active-class="menu-active">
                    <PhotoIcon class="icon-menu" />
                    Galeri Kegiatan
                </RouterLink>

                <RouterLink to="/admin/laporan" class="menu-link group" active-class="menu-active">
                    <DocumentChartBarIcon class="icon-menu" />
                    Laporan Bulanan
                </RouterLink>

                <RouterLink v-if="user.role === 'admin'" to="/admin/users" class="menu-link group"
                    active-class="menu-active">
                    <UsersIcon class="icon-menu" />
                    Manajemen User
                </RouterLink>

            </nav>
        </aside>

        <!-- Main -->
        <div class="lg:ml-72">
            <!-- Navbar -->
            <header
                class="sticky top-0 z-20 flex h-20 items-center justify-between bg-white/80 px-5 backdrop-blur lg:px-8">
                <div class="flex items-center gap-3">
                    <button class="rounded-xl border p-2 lg:hidden" @click="sidebarOpen = true">
                        ☰
                    </button>

                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Dashboard</h2>
                        <p class="text-sm text-slate-500">Transparansi hasil jual rongsok untuk dana sosial</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="hidden rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 md:block">
                        Divisi Sosial
                    </div>
                    <div
                        class="hidden rounded-2xl bg-sky-50 px-4 py-2 text-sm font-medium text-sky-700 md:block capitalize">
                        {{ user.role }} | {{ user.name }}
                    </div>
                    <div class="relative">
                        <button type="button" @click="toggleProfileDropdown"
                            class="flex items-center gap-2 rounded-2xl bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <UserCircleIcon class="h-8 w-8 text-emerald-600" />

                            <span class="hidden md:block">
                                Profile
                            </span>

                            <ChevronDownIcon class="h-4 w-4 text-slate-500" />
                        </button>

                        <div v-if="profileDropdownOpen"
                            class="absolute right-0 z-50 mt-3 w-48 rounded-2xl border border-slate-100 bg-white p-2 shadow-xl">
                            <RouterLink to="/admin/profile"
                                class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-emerald-50 hover:text-emerald-600">
                                <UserIcon class="h-4 w-4" />
                                Profile
                            </RouterLink>
                            <RouterLink to="/admin"
                                class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-emerald-50 hover:text-emerald-600">
                                <HomeIcon class="h-4 w-4" />
                                Dashboard User
                            </RouterLink>

                            <button type="button" @click="logout"
                                class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-left text-sm font-medium text-red-600 hover:bg-red-50">
                                <ArrowRightOnRectangleIcon class="h-4 w-4" />
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-5 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
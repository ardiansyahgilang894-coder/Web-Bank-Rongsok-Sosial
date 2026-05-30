import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/auth/LoginView.vue'

import DashboardAdmin from '../views/admin/DashboardAdmin.vue'
import DashboardPetugas from '../views/petugas/DashboardPetugas.vue'
import DashboardUser from '../views/public/DashboardUser.vue'

import { useAuthStore } from '../stores/auth'

const routes = [

    {
        path: '/',
        name: 'public.transparansi',
        component: () => import('@/views/public/Transparansi.vue'),
    },

    {
        path: '/login',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
        meta: {
            guest: true,
        },
    },

    {
        path: '/register',
        name: 'register',
        component: () => import('@/views/auth/RegisterView.vue'),
        meta: {
            guest: true,
        },
    },

    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('@/views/auth/ForgotPassword.vue'),  
    },

    {
        path: '/verify-otp',
        name: 'verify-otp',
        component: () => import('@/views/auth/VerifyOTP.vue'), 
    },

    {
        path: '/reset-password',
        name: 'reset-password',
        component: () => import('@/views/auth/ResetPassword.vue'),
    },


    // ADMIN ROUTES
    {
        path: '/admin/profile',
        name: 'admin.profile',
        component: () => import('@/views/admin/Profile.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: () => import('@/views/admin/DashboardAdmin.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/penjualan-rongsok',
        name: 'admin.penjualan-rongsok',
        component: () => import('@/views/admin/PenjualanRongsok.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/pengeluaran-kas',
        name: 'admin.pengeluaran-kas',
        component: () => import('@/views/admin/PengeluaranKas.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/pemasukan-kas',
        name: 'admin.pemasukan-kas',
        component: () => import('@/views/admin/PemasukanKas.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/galeri-kegiatan',
        name: 'admin.galeri-kegiatan',
        component: () => import('@/views/admin/GaleriKegiatan.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/laporan',
        name: 'admin.laporan',
        component: () => import('@/views/admin/LaporanBulanan.vue'),
        meta: {
            requiresAuth: true,
            role: ['admin', 'petugas']
        }
    },

    {
        path: '/admin/users',
        name: 'admin.users',
        component: () => import('@/views/admin/ManajemenUser.vue'),
        meta: {
            requiresAuth: true,
            role: 'admin'
        }
    },

    {
        path: '/petugas',
        component: DashboardPetugas,
        meta: {
            requiresAuth: true,
            role: 'petugas'
        }
    },

    {
        path: '/user',
        component: DashboardUser,
        meta: {
            requiresAuth: true,
            role: 'user'
        }
    },

    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const userRaw = localStorage.getItem('user')
  const user = userRaw ? JSON.parse(userRaw) : null

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.guest && token && user) {
    if (user.role === 'admin' || user.role === 'petugas') {
      return next('/admin/dashboard')
    }

    return next('/')
  }

  if (to.meta.roles && !to.meta.roles.includes(user?.role)) {
    return next('/')
  }

  next()
})

export default router

<script setup lang="ts">

import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const logout = () => {

    auth.logout()

    router.push('/')
}

</script>

<template>

<div class="bg-white shadow px-6 py-4 flex justify-between items-center">

    <div>
        <h1 class="text-xl font-bold">
            Dashboard Admin
        </h1>
    </div>

    <div class="flex items-center gap-4">

        <span class="font-semibold">
            {{ auth.user?.name }}
        </span>

        <button
            @click="logout"
            class="bg-red-500 text-white px-4 py-2 rounded-lg"
        >
            Logout
        </button>

    </div>

</div>

</template>
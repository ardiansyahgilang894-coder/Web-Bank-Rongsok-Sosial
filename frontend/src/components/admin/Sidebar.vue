<script setup lang="ts">
</script>

<template>

<aside class="w-64 bg-slate-900 text-white min-h-screen p-5">

    <h1 class="text-2xl font-bold mb-10">
        Rongsok Social
    </h1>

    <nav class="space-y-3">

        <router-link
            to="/admin"
            class="block px-4 py-3 rounded-lg hover:bg-slate-800"
        >
            Dashboard
        </router-link>

        <router-link
            to="/pemasukan"
            class="block px-4 py-3 rounded-lg hover:bg-slate-800"
        >
            Pemasukan Kas
        </router-link>

        <router-link
            to="/pengeluaran"
            class="block px-4 py-3 rounded-lg hover:bg-slate-800"
        >
            Pengeluaran Kas
        </router-link>

        <router-link
            to="/galeri"
            class="block px-4 py-3 rounded-lg hover:bg-slate-800"
        >
            Galeri
        </router-link>

    </nav>

</aside>

</template>
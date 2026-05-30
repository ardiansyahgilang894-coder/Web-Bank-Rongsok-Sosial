<script setup lang="ts">

defineProps({
    title: String,
    value: String
})

</script>

<template>

<div class="bg-white p-6 rounded-2xl shadow">

    <h3 class="text-gray-500 mb-2">
        {{ title }}
    </h3>

    <h1 class="text-3xl font-bold">
        {{ value }}
    </h1>

</div>

</template>
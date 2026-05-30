<script setup lang="ts">
import type { ApexOptions } from 'apexcharts'
import VueApexCharts from 'vue3-apexcharts'

const chartOptions: ApexOptions = {
  chart: {
    type: 'bar' as const,
  },
  xaxis: {
    categories: ['Jan', 'Feb', 'Mar'],
  },
}

const series = [
  {
    name: 'Pemasukan',
    data: [1000, 2000, 3000],
  },
]
</script>

<template>

<div class="bg-white p-6 rounded-2xl shadow">

    <VueApexCharts
        type="bar"
        height="350"
        :options="chartOptions"
        :series="series"
    />

</div>

</template>
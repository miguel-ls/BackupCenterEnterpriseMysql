<template>

<MainLayout>

    <!-- KPI -->

    <div class="grid grid-cols-4 gap-6">

        <h1 class="text-3xl font-bold mb-8">
            Dashboard
        </h1>

        <StatCard title="Trabajos" :value="status.jobs ?? 0"/>

        <StatCard title="Conexiones" :value="status.connections ?? 0"/>

        <StatCard title="En Cola" :value="status.queue ?? 0"/>



    </div>

    <!-- Ejecuciones -->

    <div class="grid grid-cols-3 gap-6 mt-6">

        <div class="col-span-2">
           <BackupChart/>
        </div> 

        <!-- <QueueWidget/> -->
        <OperationsCenter/>
    </div>

    <!-- Gráfico -->

    <div class="grid grid-cols-1 gap-6 mt-6">
        
         <LastExecutions/>
    </div>

</MainLayout>

</template>

<script setup>

import { ref, computed, onMounted } from "vue"
import MainLayout from "../components/layout/MainLayout.vue"
import StatCard from "../components/cards/StatCard.vue"
import LastExecutions from "../components/dashboard/LastExecutions.vue"
import QueueWidget from "../components/dashboard/widgets/QueueWidget.vue"
import BackupChart from "../components/dashboard/widgets/BackupChart.vue"
import OperationsCenter from "../components/dashboard/widgets/OperationsCenter.vue"

import {
    getStatus,
    getStatistics,
    getVersion,
    getUpdates
} from "../api/client"

import { useAutoRefresh } from "../composables/useAutoRefresh"

const status = ref({})
const statistics = ref({})
const version = ref({})
const updateInfo = ref(null)

async function load() {
    try {
        const [s, st, v] = await Promise.all([
            getStatus(),
            getStatistics(),
            getVersion()
        ])

        status.value = s?.data ?? {}
        statistics.value = st?.data ?? {}
        version.value = v?.data ?? v ?? {}
    } catch (error) {
        console.error("Failed loading dashboard data", error)
        status.value = {}
        statistics.value = {}
        version.value = {}
    }
}

async function loadUpdates() {
    try {
        const response = await getUpdates()
        updateInfo.value = response?.success ? response.data ?? response : null
    } catch (error) {
        console.error("Failed loading updates", error)
        updateInfo.value = null
    }
}

onMounted(() => {
    load()
    loadUpdates()
})

useAutoRefresh(load, 20000)

const currentVersion = computed(() => version.value?.version ?? "N/A")

const ramPercent = computed(() => {
    if (!version.value) return 0
    return 0
})

const diskPercent = computed(() => {
    if (!version.value) return 0
    return 0
})

const cpuColor = computed(() => "#22c55e")
const ramColor = computed(() => "#22c55e")

</script>

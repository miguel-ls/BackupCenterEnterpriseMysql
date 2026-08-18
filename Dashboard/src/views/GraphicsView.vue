<template>

<MainLayout>

    <div class="mb-8 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold">Graficos</h1>
            <p class="mt-2 text-neutral-500">Analisis de actividad de backups</p>
        </div>

        <button
            type="button"
            class="rounded-lg bg-blue-600 px-5 py-3 text-white hover:bg-blue-700 disabled:opacity-60"
            :disabled="loading"
            @click="loadGraphics"
        >
            {{ loading ? 'Cargando...' : 'Actualizar' }}
        </button>
    </div>

    <div class="mb-8 flex flex-wrap gap-3 rounded-xl border border-neutral-200 bg-white p-4 shadow-sm">
        <select v-model="filters.clientId" class="rounded-lg border px-3 py-2" @change="loadGraphics">
            <option :value="0">Todos los clientes</option>
            <option v-for="client in clients" :key="client.id" :value="client.id">
                {{ client.name }}
            </option>
        </select>

        <select v-model="filters.status" class="rounded-lg border px-3 py-2" @change="loadGraphics">
            <option value="">Todos los estados</option>
            <option v-for="status in statuses" :key="status" :value="status">
                {{ status }}
            </option>
        </select>

        <input v-model="filters.from" type="date" class="rounded-lg border px-3 py-2" @change="loadGraphics" />
        <input v-model="filters.to" type="date" class="rounded-lg border px-3 py-2" @change="loadGraphics" />
    </div>

    <div v-if="errorMessage" class="mb-8 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700">
        {{ errorMessage }}
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <section class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-xl font-bold">Archivos subidos por dia y cliente</h2>
                <p class="mt-1 text-sm text-neutral-500">Comparativo diario en barras</p>
            </div>
            <div class="h-[360px]">
                <Bar v-if="hasData" :data="uploadedByDayChart" :options="barOptions" />
                <div v-else class="flex h-full items-center justify-center text-neutral-500">No existen datos para los filtros seleccionados.</div>
            </div>
        </section>

        <section class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-xl font-bold">Resumen de archivos</h2>
                <p class="mt-1 text-sm text-neutral-500">Encontrados, subidos, omitidos y con errores</p>
            </div>
            <div class="h-[360px]">
                <Pie v-if="hasData" :data="summaryChart" :options="pieOptions" />
                <div v-else class="flex h-full items-center justify-center text-neutral-500">No existen datos para los filtros seleccionados.</div>
            </div>
        </section>

        <section class="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm xl:col-span-2">
            <div class="mb-4">
                <h2 class="text-xl font-bold">Archivos subidos por dia y cliente</h2>
                <p class="mt-1 text-sm text-neutral-500">Comparativo diario en lineas</p>
            </div>
            <div class="h-[360px]">
                <Line v-if="hasData" :data="uploadedByDayChart" :options="lineOptions" />
                <div v-else class="flex h-full items-center justify-center text-neutral-500">No existen datos para los filtros seleccionados.</div>
            </div>
        </section>
    </div>

</MainLayout>

</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { Bar, Line, Pie } from 'vue-chartjs'
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip,
    ArcElement
} from 'chart.js'
import MainLayout from '../components/layout/MainLayout.vue'
import { getGraphics } from '../api/client'

ChartJS.register(
    ArcElement,
    BarElement,
    CategoryScale,
    Filler,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    Tooltip
)

const today = new Date()
const start = new Date(today)
start.setDate(start.getDate() - 7)

function formatDate(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
}

const filters = ref({
    clientId: 0,
    status: '',
    from: formatDate(start),
    to: formatDate(today)
})
const clients = ref([])
const statuses = ref([])
const records = ref([])
const summary = ref({
    files_found: 0,
    files_uploaded: 0,
    files_skipped: 0,
    files_failed: 0
})
const loading = ref(false)
const errorMessage = ref('')

const palette = [
    '#2563EB',
    '#10B981',
    '#F59E0B',
    '#EF4444',
    '#8B5CF6',
    '#06B6D4',
    '#F97316',
    '#64748B'
]

const hasData = computed(() => records.value.length > 0)

const days = computed(() => [...new Set(
    records.value
    .map((item) => String(item.day ?? '').slice(0, 10))
        .filter(Boolean)
)].sort())

const clientNames = computed(() => [...new Set(
    records.value
        .map((item) => item.client_name ?? 'Sin cliente')
        .filter(Boolean)
)].sort())

function valueFor(item, field) {
    return Number(item[field] ?? 0) || 0
}

function datasetsFor(field) {
    return clientNames.value.map((clientName, index) => ({
        label: clientName,
        data: days.value.map((day) => records.value
            .filter((item) => String(item.day ?? '').slice(0, 10) === day && (item.client_name ?? 'Sin cliente') === clientName)
            .reduce((total, item) => total + valueFor(item, field), 0)),
        backgroundColor: palette[index % palette.length],
        borderColor: palette[index % palette.length],
        borderWidth: 2,
        tension: 0.25,
        fill: false
    }))
}

// const foundByDayChart = computed(() => ({
//     labels: days.value,
//     datasets: datasetsFor('files_uploaded')
// }))

const uploadedByDayChart = computed(() => ({
    labels: days.value,
    datasets: datasetsFor('files_uploaded')
}))

const summaryChart = computed(() => ({
    labels: [ 'Encontrados','Subidos', 'Omitidos', 'Errores'],
    datasets: [{
        data: [ 'files_found', 'files_uploaded', 'files_skipped', 'files_failed'].map((field) => valueFor(summary.value, field)),
        backgroundColor: ['#2563EB', '#10B981', '#F59E0B', '#EF4444'],
        borderColor: '#ffffff',
        borderWidth: 2
    }]
}))

const commonOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'top' },
        tooltip: { enabled: true }
    }
}

const barOptions = {
    ...commonOptions,
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
    }
}

const lineOptions = {
    ...commonOptions,
    interaction: { intersect: false, mode: 'index' },
    scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
    }
}

const pieOptions = {
    ...commonOptions,
    maintainAspectRatio: false
}

async function loadGraphics() {
    loading.value = true
    errorMessage.value = ''

    try {
        const response = await getGraphics(filters.value)
        records.value = response?.data ?? []
        summary.value = response?.summary ?? summary.value

        const availableClients = response?.filters?.clients ?? []
        clients.value = availableClients.map((client) => ({
            id: client.id,
            name: client.name ?? client.business_name ?? ''
        }))
        statuses.value = response?.filters?.statuses ?? []
    } catch (error) {
        records.value = []
        errorMessage.value = 'No se pudieron cargar los graficos.'
        console.error(error)
    } finally {
        loading.value = false
    }
}

onMounted(loadGraphics)
</script>

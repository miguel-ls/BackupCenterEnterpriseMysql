<template>

<MainLayout>

    <div class="flex flex-wrap justify-between items-center gap-3 mb-8">

        <h1 class="text-3xl font-bold">Historial</h1>

        <div class="flex flex-wrap items-center gap-3">

            <select v-model="limit" @change="changeLimit" class="border rounded-lg px-3 py-2">
                <option :value="10">10</option>
                <option :value="20">20</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
            </select>

            <button @click="loadHistory" class="bg-blue-600 text-white px-5 py-3 rounded-lg hover:bg-blue-700">
                Actualizar
            </button>

        </div>

    </div>

    <div class="mb-6 flex flex-wrap gap-3 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
        <input v-model="filters.from" type="date" class="border rounded-lg px-3 py-2" @change="applyFilters" />
        <input v-model="filters.to" type="date" class="border rounded-lg px-3 py-2" @change="applyFilters" />

        <select v-model="filters.clientId" class="border rounded-lg px-3 py-2" @change="applyFilters">
            <option :value="0">Todos los clientes</option>
            <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
        </select>

        <select v-model="filters.jobId" class="border rounded-lg px-3 py-2" @change="applyFilters">
            <option :value="0">Todos los trabajos</option>
            <option v-for="job in filterOptions.jobs" :key="job.id" :value="job.id">{{ job.name }}</option>
        </select>

        <select v-model="filters.status" class="border rounded-lg px-3 py-2" @change="applyFilters">
            <option value="">Todos los estados</option>
            <option v-for="statusOption in filterOptions.statuses" :key="statusOption" :value="statusOption">{{ statusOption }}</option>
        </select>

        <button @click="resetFilters" class="border rounded-lg px-4 py-2">Limpiar</button>
    </div>

    <div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-neutral-100">

                <tr>
                    <th class="text-left p-3">Fecha</th>
                    <th class="text-left p-3">Cliente</th>
                    <th class="text-left p-3">Trabajo</th>
                    <th class="text-left p-3">Encontrados</th>
                    <th class="text-left p-3">Subidos</th>
                    <th class="text-left p-3">Omitidos</th>
                    <th class="text-left p-3">Errores</th>
                    <th class="text-left p-3">Duración</th>
                    <th class="text-left p-3">Estado</th>
                </tr>

            </thead>

            <tbody>

                <tr v-for="item in history" :key="item.id" class="border-t hover:bg-neutral-50">
                    <td class="p-3">{{ item.started_at }}</td>
                    <td class="p-3">{{ item.client_name }}</td>
                    <td class="p-3">{{ item.job_name }}</td>
                    <td class="p-3">{{ item.files_found }}</td>
                    <td class="p-3">{{ item.files_uploaded }}</td>
                    <td class="p-3">{{ item.files_skipped }}</td>
                    <td class="p-3">{{ item.files_failed }}</td>
                    <td class="p-3">{{ formatDuration(item.duration_seconds) }}</td>

                    <td class="p-3">
                        <span :class="['Correcto','Completed','OK','success'].includes(item.status) ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                            {{ item.status }}
                        </span>
                    </td>
                </tr>

                <tr v-if="history.length === 0">
                    <td colspan="9" class="text-center py-10 text-neutral-500">No existen registros.</td>
                </tr>

            </tbody>

        </table>

    </div>

    <div class="flex justify-between items-center mt-6">

        <div class="text-sm text-neutral-500">
            Mostrando {{ history.length }} registros de {{ total }}
        </div>

        <div class="flex items-center gap-3">
            <button class="border rounded-lg px-4 py-2 disabled:opacity-50" :disabled="page === 1" @click="previous">← Anterior</button>
            <span>Página {{ page }} de {{ pages }}</span>
            <button class="border rounded-lg px-4 py-2 disabled:opacity-50" :disabled="page >= pages" @click="next">Siguiente →</button>
        </div>

    </div>

</MainLayout>

</template>

<script setup>
import { ref, onMounted } from 'vue'
import MainLayout from '../components/layout/MainLayout.vue'
import { getHistory, getClients, getJobs } from '../api/client'

const history = ref([])
const page = ref(1)
const limit = ref(10)
const total = ref(0)
const pages = ref(0)
const filterOptions = ref({ clients: [], jobs: [], statuses: [] })
const filters = ref({
    clientId: 0,
    jobId: 0,
    status: '',
    from: '',
    to: ''
})

async function loadHistory() {
    const response = await getHistory(page.value, limit.value, {
        clientId: filters.value.clientId,
        jobId: filters.value.jobId,
        status: filters.value.status,
        from: filters.value.from,
        to: filters.value.to
    })

    history.value = response.data
    total.value = response.total
    pages.value = response.pages
    filterOptions.value = response.filters ?? filterOptions.value
}

async function loadFilterOptions() {
    const [clientsResponse, jobsResponse] = await Promise.all([
        getClients(),
        getJobs()
    ])

    const clients = clientsResponse?.data ?? clientsResponse?.items ?? []
    const jobs = jobsResponse?.data ?? jobsResponse?.items ?? []

    filterOptions.value = {
        ...filterOptions.value,
        clients: clients.map((item) => ({ id: item.id, name: item.business_name ?? item.name ?? item.client_name ?? '' })),
        jobs: jobs.map((item) => ({ id: item.id, name: item.name ?? '' }))
    }
}

function formatDuration(seconds) {

    const total = Number(seconds ?? 0)

    const hrs = Math.floor(total / 3600)
    const mins = Math.floor((total % 3600) / 60)
    const secs = Math.floor(total % 60)

    return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`
}

function applyFilters() {
    page.value = 1
    loadHistory()
}

function resetFilters() {
    filters.value = {
        clientId: 0,
        jobId: 0,
        status: '',
        from: '',
        to: ''
    }
    page.value = 1
    loadHistory()
}

function previous() {
    if (page.value > 1) {
        page.value--
        loadHistory()
    }
}

function next() {
    if (page.value < pages.value) {
        page.value++
        loadHistory()
    }
}

function changeLimit() {
    page.value = 1
    loadHistory()
}

onMounted(async () => {
    await loadFilterOptions()
    await loadHistory()
})
</script>
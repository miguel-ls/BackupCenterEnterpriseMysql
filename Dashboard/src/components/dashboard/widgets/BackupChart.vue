<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

    <div class="flex flex-col gap-4 px-6 py-5 border-b">

        <div>

            <h2 class="text-xl font-bold">

                Actividad de Backups

            </h2>

            <div class="text-sm text-neutral-500 mt-1">

                Últimos 7 días

            </div>

        </div>

        <div class="flex min-w-0 flex-wrap items-center justify-end gap-2">

            <select
                v-model="selectedClient"
                @change="onUpdate"
                class="min-w-0 flex-1 border rounded-md px-3 py-2 text-sm sm:min-w-[180px] sm:flex-none"
            >

                <option :value="0">Todos los clientes</option>

                <option
                    v-for="client in clients"
                    :key="client.id"
                    :value="client.id"
                >
                    {{ client.business_name }}
                </option>

            </select>

<input
    v-model="startDate"
    type="date"
    class="min-w-0 flex-1 border rounded-md px-3 py-2 text-sm sm:flex-none"
/>

<input
    v-model="endDate"
    type="date"
    class="min-w-0 flex-1 border rounded-md px-3 py-2 text-sm sm:flex-none"
/>            

            <button
                @click="onUpdate"
                class="shrink-0 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition"
            >

                Actualizar

            </button>

        </div>

    </div>

    <div class="p-6">

        <div style="height:360px">

            <Line
                v-if="loaded"
                :data="chartData"
                :options="chartOptions"
            />
            <div
                v-else
                class="flex items-center justify-center h-full text-neutral-500"
            >

                Cargando gráfico...

            </div>

        </div>

    </div>

</div>

</template>

<script setup>

import {

    ref,
    onMounted,
    onUnmounted

} from "vue"

import {

    Line

} from "vue-chartjs"

import {

    Chart as ChartJS,

    Title,
    Tooltip,
    Legend,
    Filler,

    LineElement,
    PointElement,

    CategoryScale,
    LinearScale

} from "chart.js"

import {

    getChart,
    getClients,
    getHistory

} from "@/api/client"

ChartJS.register(

    Title,
    Tooltip,
    Legend,
    Filler,

    LineElement,
    PointElement,

    CategoryScale,
    LinearScale

)

const loaded = ref(false)
const clients = ref([])
const selectedClient = ref(0)

const today = new Date()

const endDate = ref(formatLocalDate(today))

const start = new Date(today)
start.setDate(start.getDate() - 7)

const startDate = ref(formatLocalDate(start))

const chartData = ref({

    labels:[],

    datasets:[]

})

const chartOptions = {

    responsive:true,

    maintainAspectRatio:false,

    interaction:{

        intersect:false,

        mode:"index"

    },

    plugins:{

        legend:{

            display:true,

            position:"top"

        },

        title:{

            display:false

        },

        tooltip:{

            enabled:true

        }

    },

    scales:{

        y:{

            beginAtZero:true,

            grid:{

                color:"#E5E7EB"

            },

            ticks:{

                precision:0

            }

        },

        x:{

            grid:{

                display:false

            }

        }

    }

}

let timer = null

function formatLocalDate(date) {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, "0")
    const day = String(date.getDate()).padStart(2, "0")

    return `${year}-${month}-${day}`
}

function parseLocalDate(value) {

    const [year, month, day] = value.split("-").map(Number)

    return new Date(year, month - 1, day, 12, 0, 0)

}

function utcToLocalDate(dateString) {

    if (!dateString) return ""

    const date = new Date(dateString)

    return formatLocalDate(date)

}

async function __loadBackupChartData() {

    try {

        const response = await getChart(
            selectedClient.value,
            startDate.value,
            endDate.value
        )

        const rows = Array.isArray(response.data)
            ? response.data
            : []

        const labels = []
        const values = {}

        rows.forEach(item => {
            values[item.day] = Number(item.uploaded) || 0
        })

        const current = parseLocalDate(startDate.value)
        const end = parseLocalDate(endDate.value)

        while (current <= end) {

            labels.push(
                formatLocalDate(current)
            )

            current.setDate(
                current.getDate() + 1
            )
        }

        chartData.value = {
            labels,

            datasets: [
                {
                    label: "Archivos subidos",

                    data: labels.map(
                        day => values[day] ?? 0
                    ),

                    borderColor: "#2563EB",

                    backgroundColor:
                        "rgba(37,99,235,0.08)",

                    fill: true,

                    tension: 0.25,

                    pointRadius: 3
                }
            ]
        }

        /*
         * Solo mostramos Cargando...
         * durante la primera carga.
         *
         * Las actualizaciones automáticas
         * mantienen el gráfico visible.
         */
        loaded.value = true

    }
    catch (e) {

        console.error(
            "Error cargando actividad de backups:",
            e
        )

        /*
         * IMPORTANTE:
         *
         * No vaciamos chartData.
         *
         * Si falla una actualización automática,
         * conservamos el último gráfico válido.
         */

        if (!loaded.value) {
            chartData.value = {
                labels: [],
                datasets: []
            }
        }
    }
}

function onUpdate() {
    return __loadBackupChartData()
}

onMounted(()=>{

    onUpdate()

    // load clients for selector
    async function loadClients(){
        try{
            const r = await getClients()
            clients.value = Array.isArray(r.data) ? r.data : []
        } catch(e){
            clients.value = []
        }
    }

    loadClients()

    timer = setInterval(onUpdate,20000)

})

onUnmounted(()=>{

    clearInterval(timer)

})

</script>
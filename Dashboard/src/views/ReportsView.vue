<template>

<MainLayout>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold">

                Reportes

            </h1>

            <p class="text-neutral-500 mt-2">

                Dashboard Ejecutivo

            </p>

        </div>

        <div class="flex gap-3">

            <button
                @click="load"
                class="bg-gray-200 hover:bg-gray-300 px-5 py-3 rounded-lg"
            >
                Actualizar
            </button>

        </div>

    </div>

    <div class="mb-8 bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b bg-neutral-50">

            <h2 class="text-lg font-bold">Exportaciones detalladas</h2>

            <p class="text-sm text-neutral-500 mt-1">
                Descarga los indicadores en Excel o PDF.
            </p>

        </div>

        <div class="divide-y">

            <div
                v-for="report in exportReports"
                :key="report.action"
                class="flex items-center justify-between gap-4 px-6 py-4"
            >

                <div>

                    <div class="font-medium">{{ report.title }}</div>

                    <div class="text-sm text-neutral-500">{{ report.description }}</div>

                </div>

                <div class="flex shrink-0 gap-2">

                    <button
                        @click="download(report.action, 'excel')"
                        class="rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-2 text-sm"
                    >
                        XLS
                    </button>

                    <button
                        @click="download(report.action, 'pdf')"
                        class="rounded-lg bg-red-600 hover:bg-red-700 text-white px-3 py-2 text-sm"
                    >
                        PDF
                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- KPIs -->

    <div class="grid grid-cols-5 gap-6">

        <StatCard
            title="Trabajos"
            :value="summary.jobs"
        />

        <StatCard
            title="Conexiones"
            :value="summary.connections"
        />

        <StatCard
            title="Total Archivos"
            :value="summary.uploaded_files"
        />

        <StatCard
            title="Ejecuciones"
            :value="summary.executions"
        />

        <StatCard
            title="Errores"
            :value="summary.errors"
        />

    </div>

    <!-- Clientes -->

    <div class="mt-8 bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b bg-neutral-50">

            <h2 class="text-lg font-bold">

                Ranking de Clientes

            </h2>

            <span class="text-sm text-neutral-500">

                {{ clients.length }} clientes

            </span>

        </div>

        <table class="w-full">

            <thead class="bg-neutral-100">

                <tr>

                    <th class="text-left p-3">Cliente</th>

                    <th class="text-right p-3">Ejecuciones</th>

                    <th class="text-right p-3">Archivos</th>

                    <th class="text-right p-3">Errores</th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="item in clients"
                    :key="item.client"
                    class="border-t hover:bg-blue-50 transition"
                >

                    <td class="p-3 font-medium">

                        {{ item.client }}

                    </td>

                    <td class="p-3 text-right">

                        {{ item.executions }}

                    </td>

                    <td class="p-3 text-right">

                        {{ item.uploaded }}

                    </td>

                    <td
                        class="p-3 text-right font-semibold"
                        :class="item.errors>0 ? 'text-red-600' : 'text-green-600'"
                    >

                        {{ item.errors }}

                    </td>

                </tr>

                <tr
                    v-if="clients.length===0"
                >

                    <td
                        colspan="4"
                        class="text-center py-10 text-neutral-500"
                    >

                        No existen registros.

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <div class="mt-8 bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b bg-neutral-50">

            <h2 class="text-lg font-bold">Actividad diaria</h2>

            <span class="text-sm text-neutral-500">Últimos 30 días con actividad</span>

        </div>

        <table class="w-full">

            <thead class="bg-neutral-100">

                <tr>

                    <th class="text-left p-3">Fecha</th>

                    <th class="text-right p-3">Ejecuciones</th>

                    <th class="text-right p-3">Archivos subidos</th>

                    <th class="text-right p-3">Errores</th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="item in daily"
                    :key="item.day"
                    class="border-t hover:bg-blue-50 transition"
                >

                    <td class="p-3 font-medium">{{ item.day }}</td>

                    <td class="p-3 text-right">{{ item.executions }}</td>

                    <td class="p-3 text-right">{{ item.uploaded }}</td>

                    <td
                        class="p-3 text-right font-semibold"
                        :class="item.errors>0 ? 'text-red-600' : 'text-green-600'"
                    >
                        {{ item.errors }}
                    </td>

                </tr>

                <tr v-if="daily.length===0">

                    <td colspan="4" class="text-center py-10 text-neutral-500">
                        No existen ejecuciones registradas.
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <div class="mt-8 bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <div class="flex items-center justify-between px-6 py-4 border-b bg-neutral-50">

            <h2 class="text-lg font-bold">Ejecuciones con errores</h2>

            <span class="text-sm text-neutral-500">{{ errors.length }} registros</span>

        </div>

        <table class="w-full">

            <thead class="bg-neutral-100">

                <tr>

                    <th class="text-left p-3">Fecha</th>

                    <th class="text-left p-3">Cliente</th>

                    <th class="text-right p-3">Encontrados</th>

                    <th class="text-right p-3">Subidos</th>

                    <th class="text-right p-3">Errores</th>

                    <th class="text-right p-3">Duracion</th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="item in errors"
                    :key="`${item.started_at}-${item.client}`"
                    class="border-t hover:bg-red-50 transition"
                >

                    <td class="p-3">{{ item.started_at }}</td>

                    <td class="p-3 font-medium">{{ item.client }}</td>

                    <td class="p-3 text-right">{{ item.files_found }}</td>

                    <td class="p-3 text-right">{{ item.files_uploaded }}</td>

                    <td class="p-3 text-right font-semibold text-red-600">{{ item.errors }}</td>

                    <td class="p-3 text-right">{{ item.duration }} s</td>

                </tr>

                <tr v-if="errors.length===0">

                    <td colspan="6" class="text-center py-10 text-green-600">
                        No existen ejecuciones con errores.
                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <div class="mt-5 text-right text-sm text-neutral-500">

        Última actualización:
        <strong>{{ lastUpdate }}</strong>

    </div>

</MainLayout>

</template>

<script setup>

import {

    ref

} from "vue"

import MainLayout from "../components/layout/MainLayout.vue"

import StatCard from "../components/cards/StatCard.vue"

import {

    getReportSummary,
    getReportClients,
    getReportDaily,
    getReportErrors,
    exportReport

} from "../api/client"

import {

    useAutoRefresh

} from "../composables/useAutoRefresh"

const summary = ref({

    jobs:0,

    connections:0,

    uploaded_files:0,

    executions:0,

    errors:0

})

const clients = ref([])

const daily = ref([])

const errors = ref([])

const exportReports = [

    {
        action:'clients',
        title:'Reporte de clientes',
        description:'Ejecuciones, archivos subidos y errores por cliente.'
    },

    {
        action:'jobs',
        title:'Reporte de trabajos',
        description:'Estado y última ejecución de cada trabajo configurado.'
    },

    {
        action:'daily',
        title:'Reporte de actividad diaria',
        description:'Ejecuciones, archivos procesados y errores de los últimos 30 días.'
    },

    {
        action:'errors',
        title:'Reporte de errores',
        description:'Detalle de ejecuciones fallidas, archivos procesados y duracion.'
    },

    {
        action:'connections',
        title:'Reporte de conexiones',
        description:'Inventario de conexiones, protocolo, servidor y ruta remota.'
    }

]

const lastUpdate = ref("-")

async function load(){

    try{

        const [

            summaryResponse,

            clientsResponse,

            dailyResponse,

            errorsResponse

        ] = await Promise.all([

            getReportSummary(),

            getReportClients(),

            getReportDaily(30),

            getReportErrors()

        ])

        summary.value = summaryResponse.data

        clients.value = clientsResponse.data

        daily.value = dailyResponse.data

        errors.value = errorsResponse.data

        lastUpdate.value = new Date().toLocaleTimeString()

    }
    catch(e){

        console.error(e)

    }

}

function download(action,type){

    exportReport(action,type).catch((e) => {

        console.error(e)

        alert("No se pudo exportar el reporte. Vuelve a iniciar sesión e inténtalo de nuevo.")

    })

}

useAutoRefresh(load,20000)

</script>

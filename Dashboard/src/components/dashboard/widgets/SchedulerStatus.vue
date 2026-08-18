<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

    <div class="flex items-center justify-between px-6 py-5 border-b">

        <div>

            <h2 class="text-xl font-bold">
                Centro de Monitoreo
            </h2>

            <div class="text-sm text-neutral-500">
                Actualización automática cada 5 segundos
            </div>

        </div>

        <div class="flex items-center gap-2">

            <span
                class="w-3 h-3 rounded-full"
                :class="online ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
            ></span>

            <span
                class="font-semibold"
                :class="online ? 'text-green-600' : 'text-red-600'"
            >
                {{ online ? "ONLINE" : "OFFLINE" }}
            </span>

        </div>

    </div>

    <div class="grid grid-cols-2 gap-5 p-6">

        <!-- Scheduler -->

        <div class="service">

            <div class="title">

                🗓 Scheduler

            </div>

            <table class="w-full text-sm">
 <tbody>
                <tr>
                    <td>Estado</td>
                    <td class="text-right font-semibold"
                        :class="statusColor(data.scheduler.status)">
                        {{ data.scheduler.status }}
                    </td>
                </tr>

                <tr>
                    <td>PID</td>
                    <td class="text-right">{{ data.scheduler.pid }}</td>
                </tr>

                <tr>
                    <td>RAM</td>
                    <td class="text-right">{{ data.scheduler.memory }}</td>
                </tr>

                <tr>
                    <td>CPU</td>
                    <td class="text-right">{{ data.scheduler.cpu }}</td>
                </tr>

                <tr>
                    <td>Inicio</td>
                    <td class="text-right text-xs">
                        {{ data.scheduler.started_at }}
                    </td>
                </tr>
</tbody>
            </table>

        </div>

        <!-- Worker -->

        <div class="service">

            <div class="title">

                ⚙ Worker

            </div>

            <table class="w-full text-sm">
<tbody>
                <tr>
                    <td>Estado</td>
                    <td class="text-right font-semibold"
                        :class="statusColor(data.worker.status)">
                        {{ data.worker.status }}
                    </td>
                </tr>

                <tr>
                    <td>PID</td>
                    <td class="text-right">{{ data.worker.pid }}</td>
                </tr>

                <tr>
                    <td>RAM</td>
                    <td class="text-right">{{ data.worker.memory }}</td>
                </tr>

                <tr>
                    <td>CPU</td>
                    <td class="text-right">{{ data.worker.cpu }}</td>
                </tr>

                <tr>
                    <td>Inicio</td>
                    <td class="text-right text-xs">
                        {{ data.worker.started_at }}
                    </td>
                </tr>
</tbody>
            </table>

        </div>

    </div>

    <div class="grid grid-cols-4 gap-4 px-6 pb-6">

        <div class="kpi">

            <div class="kpiTitle">Pendientes</div>

            <div class="kpiValue">
                {{ data.queue }}
            </div>

        </div>

        <div class="kpi">

            <div class="kpiTitle">Ejecutándose</div>

            <div class="kpiValue text-blue-600">
                {{ data.running }}
            </div>

        </div>

        <div class="kpi">

            <div class="kpiTitle">Completados</div>

            <div class="kpiValue text-green-600">
                {{ data.completed }}
            </div>

        </div>

        <div class="kpi">

            <div class="kpiTitle">Fallidos</div>

            <div
                class="kpiValue"
                :class="data.failed>0 ? 'text-red-600':'text-green-600'"
            >
                {{ data.failed }}
            </div>

        </div>

    </div>

    <div class="border-t px-6 py-5">

        <div class="flex justify-between">

            <span>Total ejecuciones</span>

            <strong>

                {{ data.executions }}

            </strong>

        </div>

        <div class="flex justify-between mt-3">

            <span>Última ejecución</span>

            <strong>

                {{ data.last_execution }}

            </strong>

        </div>

    </div>

</div>

</template>

<script setup>

import {

    ref,
    computed,
    onMounted,
    onUnmounted

} from "vue"

import {

    getSystemStatus

} from "@/api/client"

const data = ref({

    scheduler:{},

    worker:{},

    queue:0,

    running:0,

    completed:0,

    failed:0,

    executions:0,

    last_execution:"-"

})

const online = computed(()=>{

    return data.value.scheduler?.status==="Activo"

})

function statusColor(status){

    return status==="Activo"

        ? "text-green-600"

        : "text-red-600"

}

async function load(){

    const response = await getSystemStatus()

    data.value = response.data

}

let timer = null

onMounted(()=>{

    load()

    timer = setInterval(load,20000)

})

onUnmounted(()=>{

    clearInterval(timer)

})

</script>

<style scoped>

.service{

    border:1px solid #e5e7eb;

    border-radius:12px;

    padding:20px;

    transition:.2s;

}

.service:hover{

    box-shadow:0 10px 25px rgba(0,0,0,.08);

}

.title{

    font-size:18px;

    font-weight:700;

    margin-bottom:15px;

}

table td{

    padding:6px 0;

}

.kpi{

    border:1px solid #e5e7eb;

    border-radius:12px;

    padding:18px;

    text-align:center;

}

.kpiTitle{

    color:#6b7280;

    font-size:13px;

}

.kpiValue{

    font-size:30px;

    font-weight:700;

    margin-top:8px;

}

</style>
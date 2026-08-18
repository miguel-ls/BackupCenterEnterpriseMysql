<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

    <div class="flex justify-between items-center px-6 py-4 border-b">

        <div>

            <h2 class="text-lg font-bold">
                🖥 Centro de Operaciones
            </h2>

            <div class="text-xs text-neutral-500">
                Estado general del sistema
            </div>

        </div>

        <div
            class="px-3 py-1 rounded-full text-sm font-bold"
            :class="alerts.length===0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
        >

            {{ alerts.length===0 ? 'OPERATIVO' : 'ALERTA' }}

        </div>

    </div>

    <div class="grid grid-cols-3 divide-x px-6 py-4  ">

        <!-- Eventos -->

        <div class="col-span-2">

            <div class="font-semibold mb-4">
                Últimos Eventos
            </div>

            <div
                v-for="event in events"
                :key="event.date"
                class="mb-3"
            >

                <div class="font-medium">

                    {{ event.title }}

                </div>

                <div class="text-xs text-neutral-500">

                    {{ formatDate(event.date) }}

                </div>

            </div>

        </div>

        
        <!-- Estado -->

        <div class="p-5 ">

            <div class="font-semibold mb-4">
                Estado
            </div>

            <div
                v-for="item in status"
                :key="item.name"
                class="flex justify-between py-2"
            >

                <span>

                    {{ item.name }}

                </span>

                <span
                    :class="item.ok ? 'text-green-600' : 'text-red-600'"
                >

                    {{ item.ok ? '🟢' : '🔴' }}

                </span>

            </div>

            <div class="border my-4"></div>

            <div class="font-semibold mb-4">
                Alertas
            </div>

            <div
                v-if="alerts.length===0"
                class="text-green-600"
            >

                ✅ Sin alertas activas

            </div>

            <div
                v-for="alert in alerts"
                :key="alert"
                class="text-red-600 mb-2"
            >

                🔴 {{ alert }}

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

const status = ref([])

const events = ref([])

const alerts = ref([])

import API from "@/config/api";
import { token } from "@/api/auth";

async function load(){

    const r = await fetch(

        `${API}/alerts.php`,

        {
            headers: {
                Authorization: `Bearer ${token()}`
            }
        }

    )

    const j = await r.json()

    status.value = j.data.status

    events.value = j.data.events

    alerts.value = j.data.alerts

}

function formatDate(value){

    if(!value){
 
        return "-"

    }

    return new Date(value).toLocaleString(
        "es-PE",
        { 
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false
        }
    )

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
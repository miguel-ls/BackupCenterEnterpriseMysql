<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

    <div class="flex justify-between items-center px-6 py-4 border-b">

        <h2 class="text-lg font-bold">

            🔔 Centro de Alertas

        </h2>

        <span
            class="bg-blue-600 text-white text-xs px-3 py-1 rounded-full"
        >
            {{ alerts.length }}
        </span>

    </div>

    <div
        v-if="alerts.length===0"
        class="p-8 text-center text-neutral-400"
    >

        No existen alertas

    </div>

    <div
        v-for="item in alerts"
        :key="item.title"
        class="flex items-center justify-between px-6 py-4 border-b last:border-b-0 hover:bg-neutral-50"
    >

        <div class="flex items-center gap-4">

            <div class="text-2xl">

                {{ icon(item.type) }}

            </div>

            <div>

                <div class="font-semibold">

                    {{ item.title }}

                </div>

                <div class="text-sm text-neutral-500">

                    {{ item.message }}

                </div>

            </div>

        </div>

        <span
            class="text-xs font-semibold"
            :class="color(item.type)"
        >

            {{ label(item.type) }}

        </span>

    </div>

</div>

</template>

<script setup>

import {

    ref,
    onMounted,
    onUnmounted

} from "vue"

import { token } from "@/api/auth"

const alerts = ref([])

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

    alerts.value = j.data

}

function icon(type){

    switch(type){

        case "success":

            return "🟢"

        case "warning":

            return "🟡"

        case "danger":

            return "🔴"

        default:

            return "ℹ️"

    }

}

function color(type){

    switch(type){

        case "success":

            return "text-green-600"

        case "warning":

            return "text-yellow-600"

        case "danger":

            return "text-red-600"

        default:

            return "text-blue-600"

    }

}

function label(type){

    switch(type){

        case "success":

            return "OK"

        case "warning":

            return "Atención"

        case "danger":

            return "Error"

        default:

            return "Info"

    }

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
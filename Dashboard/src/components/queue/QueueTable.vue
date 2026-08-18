<template>

<div class="bg-white rounded-xl shadow-sm border border-neutral-200 overflow-hidden">

    <!-- Encabezado -->

    <div class="flex items-center justify-between px-6 py-4 border-b bg-neutral-50">

        <div>

            <h2 class="text-lg font-bold">

                Estado de Trabajos

            </h2>

            <p class="text-sm text-neutral-500">

                Total registros: {{ items.length }}

            </p>

        </div>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full">

            <thead class="bg-slate-100">

                <tr>

                    <th class="text-left px-4 py-3 font-semibold">ID</th>

                    <th class="text-left px-4 py-3 font-semibold">Trabajo</th>

                    <th class="text-left px-4 py-3 font-semibold">Cliente</th>

                    <th class="text-center px-4 py-3 font-semibold">Estado</th>

                    <th class="text-left px-4 py-3 font-semibold">Creado</th>

                    <th class="text-left px-4 py-3 font-semibold">Inicio</th>

                    <th class="text-left px-4 py-3 font-semibold">Fin</th>

                    <th class="text-center px-4 py-3 font-semibold">Tiempo</th>

                    <th class="text-center px-4 py-3 font-semibold">Accion</th>

                </tr>

            </thead>

            <tbody v-if="items.length">

                <tr
                    v-for="item in items"
                    :key="item.id"
                    class="border-t hover:bg-blue-50 transition-colors"
                >

                    <td class="px-4 py-3 font-semibold text-slate-700">

                        #{{ item.id }}

                    </td>

                    <td class="px-4 py-3">

                        {{ item.name }}

                    </td>

                    <td class="px-4 py-3">

                        {{ item.client_name || "-" }}

                    </td>

                    <td class="px-4 py-3 text-center">

                        <StatusBadge
                            :status="item.status"
                        />

                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600">

                        {{ formatDate(item.created_at) }}

                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600">

                        {{ formatDate(item.started_at) }}

                    </td>

                    <td class="px-4 py-3 text-sm text-slate-600">

                        {{ formatDate(item.finished_at) }}

                    </td>

                    <td class="px-4 py-3 text-center text-sm text-slate-600 whitespace-nowrap">

                        {{ formatQueueDuration(item) }}

                    </td>

                    <td class="px-4 py-3 text-center">

                        <button
                            v-if="item.status === 'Pending' || item.status === 'Pendiente'"
                            type="button"
                            class="rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-60"
                            :disabled="finishingId === item.id"
                            @click="finishQueue(item)"
                        >
                            {{ finishingId === item.id ? 'Finalizando...' : 'Terminar cola' }}
                        </button>

                        <span v-else class="text-neutral-400">-</span>

                    </td>

                </tr>

            </tbody>

            <tbody v-else>

                <tr>

                    <td
                        colspan="9"
                        class="text-center py-12 text-neutral-500"
                    >

                        No existen trabajos en la cola.

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

</template>

<script setup>

import { ref } from "vue"
import StatusBadge from "@/components/common/StatusBadge.vue"

const props = defineProps({

    items:{
        type:Array,
        default:()=>[]
    },
    onFinish: {
        type: Function,
        required: true
    }

})
const finishingId = ref(null)

async function finishQueue(item) {
    if (!window.confirm("¿Desea cambiar esta cola pendiente a completada?")) {
        return
    }

    finishingId.value = item.id

    try {
        await props.onFinish(item)
    } finally {
        finishingId.value = null
    }
}

function formatDate(value) {

    if (!value) {

        return "-"

    }

    const date = parsePeruDate(value)

    if (!date) {
        return "-"
    }

    return date.toLocaleString(
        "es-PE",
        {
            year: "numeric",
            month: "2-digit",
            day: "2-digit",
            hour: "2-digit",
            minute: "2-digit",
            second: "2-digit",
            hour12: false
        }
    )

}

function parsePeruDate(value) {
    if (!value) {
        return null
    }

    const normalized = String(value).trim().replace(' ', 'T')

    if (!normalized || normalized.startsWith('0000-00-00')) {
        return null
    }

    const hasTimezone = /(?:Z|[+-]\d{2}:?\d{2})$/.test(normalized)
    const date = new Date(hasTimezone ? normalized : `${normalized}-05:00`)

    return Number.isNaN(date.getTime()) ? null : date
}

function formatQueueDuration(item) {
    const created = parsePeruDate(item.created_at)
    const end = parsePeruDate(item.finished_at)

    if (!created || !end) {
        return '-'
    }

    const seconds = Math.max(0, Math.floor((end.getTime() - created.getTime()) / 1000))
    const hours = Math.floor(seconds / 3600)
    const minutes = Math.floor((seconds % 3600) / 60)
    const remainingSeconds = seconds % 60

    if (hours > 0) {
        return `${hours}h ${minutes}m ${remainingSeconds}s`
    }

    if (minutes > 0) {
        return `${minutes}m ${remainingSeconds}s`
    }

    return `${remainingSeconds}s`
}

</script>
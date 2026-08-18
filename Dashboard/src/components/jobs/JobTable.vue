<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

    <!-- Encabezado -->

    <div class="flex items-center justify-between px-6 py-4 border-b bg-neutral-50">

        <h2 class="text-lg font-bold">
            Trabajos
        </h2>

        <span class="text-sm text-neutral-500">
            Total: {{ jobs.length }}
        </span>

    </div>

    <table class="w-full">

        <thead class="bg-neutral-100">

            <tr>

                <th class="text-left px-4 py-3 font-semibold">ID</th>

                <th class="text-left px-4 py-3 font-semibold">
                    Trabajo
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Cliente
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Conexión
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Origen
                </th>


                <th class="text-left px-4 py-3 font-semibold">
                    Destino
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Programación
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Hora
                </th>                

                <th class="text-center px-4 py-3 font-semibold">
                    Estado
                </th>

                <th class="text-left px-4 py-3 font-semibold">
                    Última ejecución
                </th>

                <th class="text-center px-4 py-3 font-semibold">
                    Acciones
                </th>

            </tr>

        </thead>

        <tbody v-if="jobs.length">

            <tr
                v-for="job in jobs"
                :key="job.id"
                class="border-t hover:bg-blue-50 transition-colors"
            >

                <td class="px-4 py-3 font-semibold text-neutral-700">
                    {{ job.id }}
                </td>

                <td class="px-4 py-3">

                    <div class="font-semibold">
                        {{ job.name }}
                    </div>

                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ job.client_name || '-' }}
                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ job.connection }}
                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ job.source }}
                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ job.destination }}
                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ getScheduleType(job.schedule) }}
                </td>

                <td class="px-4 py-3 text-neutral-600">
                    {{ getScheduleTime(job.schedule) }}
                </td>                

                <td class="px-4 py-3 text-center">

                    <span
                        class="px-3 py-1 rounded-full text-xs font-bold"

                        :class="{

                            'bg-yellow-100 text-yellow-700':
                                job.status=='Pending' ||
                                job.status=='Pendiente',

                            'bg-blue-100 text-blue-700':
                                job.status=='Running' ||
                                job.status=='Ejecutando' ||
                                job.status=='En ejecución',

                            'bg-green-100 text-green-700':
                                job.status=='Completed' ||
                                job.status=='Correcto',

                            'bg-red-100 text-red-700':
                                job.status=='Failed' ||
                                job.status=='Error'

                        }"
                    >

                        {{ job.status }}

                    </span>

                </td>

                <td class="px-4 py-3 text-sm text-neutral-600">
                    {{ formatDate(job.time) }}
                </td>

                <td class="px-4 py-3">

                    <div class="flex justify-center gap-2">

                        <button
                            @click="$emit('toggle',job)"
                            class="w-9 h-9 rounded-lg flex items-center justify-center transition"
                            :class="isEnabled(job)
                                ? 'bg-blue-600 hover:bg-blue-700 text-white'
                                : 'bg-slate-700 hover:bg-slate-800 text-white'"
                            :title="isEnabled(job) ? 'Deshabilitar' : 'Habilitar'"
                        >
                            <Power :size="17"/>
                        </button>

                        <button
                            @click="$emit('run',job)"
                            class="w-9 h-9 rounded-lg flex items-center justify-center transition"
                            :class="isEnabled(job)
                                ? 'bg-green-600 hover:bg-green-700 text-white'
                                : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                            :disabled="!isEnabled(job)"
                            :title="isEnabled(job) ? 'Ejecutar' : 'Deshabilitado'"
                        >
                            <Play :size="17"/>
                        </button>

                        <button
                            @click="$emit('edit',job)"
                            class="w-9 h-9 rounded-lg bg-amber-500 hover:bg-amber-600 text-white flex items-center justify-center transition"
                            title="Editar"
                        >
                            <Pencil :size="17"/>
                        </button>

                        <button
                            @click="$emit('delete',job.id)"
                            class="w-9 h-9 rounded-lg bg-red-600 hover:bg-red-700 text-white flex items-center justify-center transition"
                            title="Eliminar"
                        >
                            <Trash2 :size="17"/>
                        </button>

                    </div>

                </td>

            </tr>

        </tbody>

        <tbody v-else>

            <tr>

                <td
                    colspan="10"
                    class="text-center py-10 text-neutral-500"
                >

                    No existen trabajos registrados.

                </td>

            </tr>

        </tbody>

    </table>

</div>

</template>

<script setup>

import {

    Play,
    Pencil,
    Power,
    Trash2

} from "lucide-vue-next";

defineProps({

    jobs:{
        type:Array,
        default:()=>[]
    }

});

defineEmits([

    "run",
    "edit",
    "delete",
    "toggle"

]);

function isEnabled(job) {

    return Number(job?.enabled ?? 1) === 1;

}

function getScheduleTime(cron) {

    if (!cron) return "-";

    const parts = cron.split(" ");

    if (parts.length < 2) return "-";

    return `${parts[1].padStart(2, "0")}:${parts[0].padStart(2, "0")}`;

}

function getScheduleType(cron) {

    if (!cron) return "-";

    const parts = cron.split(" ");

    if (parts.length < 5) return "-";

    if (
        parts[2] === "*" &&
        parts[3] === "*" &&
        parts[4] === "*"
    ) {
        return "Cada día";
    }

    if (
        parts[2] === "*" &&
        parts[3] === "*" &&
        parts[4] !== "*"
    ) {
        return "Semanal";
    }

    if (
        parts[2] !== "*" &&
        parts[3] === "*" &&
        parts[4] === "*"
    ) {
        return "Mensual";
    }

    return "Personalizado";

}

function formatDate(value) {

    if (!value || value === "-") {

        return "-";

    }

    return new Date(value).toLocaleString(
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
    );

}


</script>

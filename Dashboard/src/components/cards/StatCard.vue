<template>

<div
    class="bg-white rounded-xl border border-neutral-200 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
>

    <div class="flex items-center justify-between">

        <div>

            <div class="text-sm text-neutral-500 font-medium">

                {{ title }}

            </div>

            <div class="mt-3 text-4xl font-bold text-neutral-800">

                {{ value }}

            </div>

        </div>

        <div
            class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl"
            :class="iconColor"
        >

            {{ icon }}

        </div>

    </div>

    <!-- <div class="mt-5 pt-4 border-t border-neutral-100 flex justify-between items-center">

        <span class="text-xs text-neutral-500">

            Actualizado ahora

        </span>

        <div
            class="flex items-center gap-2 text-xs font-semibold"
            :class="statusColor"
        >

            <span class="w-2 h-2 rounded-full bg-current"></span>

            OK

        </div>

    </div> -->

</div>

</template>

<script setup>

import { computed } from "vue";

const props = defineProps({

    title: String,

    value: [String, Number]

});

const icon = computed(() => {

    switch (props.title) {

        case "Trabajos":
            return "📋";

        case "Conexiones":
            return "🌐";

        case "En Cola":
            return "⏳";

        case "Ejecutando":
            return "⚡";

        case "Subidos Hoy":
            return "⬆️";

        case "Ejecuciones Hoy":
            return "🚀";

        case "Errores Hoy":
            return "⚠️";

        case "Total Archivos":
            return "📁";

        default:
            return "📊";

    }

});

const iconColor = computed(() => {

    switch (props.title) {

        case "Trabajos":
            return "bg-blue-100";

        case "Conexiones":
            return "bg-green-100";

        case "En Cola":
            return "bg-yellow-100";

        case "Ejecutando":
            return "bg-orange-100";

        case "Subidos Hoy":
            return "bg-cyan-100";

        case "Ejecuciones Hoy":
            return "bg-indigo-100";

        case "Errores Hoy":
            return "bg-red-100";

        case "Total Archivos":
            return "bg-violet-100";

        default:
            return "bg-neutral-100";

    }

});

const statusColor = computed(() => {

    if (props.title === "Errores Hoy" && Number(props.value) > 0) {

        return "text-red-600";

    }

    if (props.title === "En Cola" && Number(props.value) > 0) {

        return "text-yellow-600";

    }

    return "text-green-600";

});

</script>
<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

<div class="flex items-center justify-between p-5 border-b">

    <h2 class="text-lg font-semibold">

        Últimas ejecuciones

    </h2>

    <div class="flex items-center gap-3">

        <select
            v-model="selectedClient"
            @change="load"
            class="border rounded-md px-3 py-2 text-sm"
        >

            <option :value="0">
                Todos los clientes
            </option>

            <option
                v-for="client in clients"
                :key="client.id"
                :value="client.id"
            >
                {{ client.business_name }}
            </option>

        </select>

        <select
            v-model="selectedStatus"
            @change="load"
            class="border rounded-md px-3 py-2 text-sm"
        >
            <option value="">Todos los estados</option>
            <option
                v-for="statusOption in filterOptions.statuses"
                :key="statusOption"
                :value="statusOption"
            >
                {{ statusOption }}
            </option>
        </select>

        <button
            @click="load"
            class="text-sm text-blue-600 hover:underline"
        >
            Actualizar
        </button>

    </div>

</div>

<div class="overflow-x-auto">

    <div class="max-h-[360px] overflow-y-auto">

        <table class="w-full">

            <thead class="bg-neutral-50 sticky top-0 z-10">

                <tr>

                    <th class="text-left p-3 font-semibold">Fecha</th>
                    <th class="text-left p-3 font-semibold">Trabajo</th>
                    <th class="text-left p-3 font-semibold">Cliente</th>
                    <th class="text-center p-3 font-semibold">Subidos</th>
                    <th class="text-center p-3 font-semibold">Errores</th>
                    <th class="text-center p-3 font-semibold">Tiempo</th>
                    <th class="text-center p-3 font-semibold">Estado</th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="item in history"
                    :key="item.id"
                    class="border-t hover:bg-neutral-50 transition"
                >

                    <td class="p-3 whitespace-nowrap">
                        {{ formatDate(item.started_at) }}
                    </td>

                    <td class="p-3 font-medium">
                        {{ item.job_name }}
                    </td>

                    <td class="p-3">
                        {{ item.client_name }}
                    </td>

                    <td class="p-3 text-center">
                        {{ item.files_uploaded }}
                    </td>

                    <td class="p-3 text-center">
                        {{ item.files_failed }}
                    </td>

                    <td class="p-3 text-center">
                        {{ formatDuration(item.duration_seconds) }}
                    </td>

                    <td class="p-3 text-center">

                        <span
                            class="px-3 py-1 rounded-full text-xs font-semibold"
                            :class="badgeClass(item.status)"
                        >
                            {{ item.status }}
                        </span>

                    </td>

                </tr>

                <tr v-if="history.length===0">

                    <td
                        colspan="7"
                        class="text-center p-10 text-neutral-500"
                    >

                        No existen ejecuciones.

                    </td>

                </tr>

            </tbody>

        </table>

    </div>
   </div>
</div>

</template>

<script setup>

import {
    ref,
    onMounted
} from "vue";

import {
    getHistoryDashboard,
    getClients
} from "@/api/client";

const history = ref([]);
const clients = ref([]);
const filterOptions = ref({ statuses: [] });

const selectedClient = ref(0);
const selectedStatus = ref('');

async function loadClients(){

    try{

        const response = await getClients();

        clients.value = Array.isArray(response.data)
            ? response.data
            : [];

    }
    catch(error){

        console.error(error);

        clients.value = [];

    }

}

async function load(){

    try{

        const response = await getHistoryDashboard(
            1,
            50,
            {
                clientId: selectedClient.value,
                status: selectedStatus.value
            }
        );

        history.value = Array.isArray(response.data)
            ? response.data
            : [];

        filterOptions.value.statuses = Array.isArray(response.filters?.statuses)
            ? response.filters.statuses
            : Array.from(new Set(history.value.map(item => item.status))).sort();

    }
    catch(error){

        console.error(error);

        history.value = [];

    }

}

function formatDate(fecha) {
  if (!fecha) return ''

  // Forzar timezone Perú
  const f = new Date(fecha.replace(' ', 'T') + '-05:00')

  return f.toLocaleString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: false
  })
}

function formatDuration(seconds) {

    seconds = Math.max(0, Math.floor(Number(seconds) || 0));

    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m ${secs}s`;
    }

    if (minutes > 0) {
        return `${minutes}m ${secs}s`;
    }

    return `${secs}s`;

}

function formatFechaLocal(fecha) {
  if (!fecha) return ''

  // Forzar que sea interpretado como hora LOCAL (no UTC)
  const f = new Date(fecha.replace(' ', 'T'))

  return f.toLocaleString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}
function badgeClass(status){

    switch(status){

        case "Correcto":
            return "bg-green-100 text-green-700";

        case "Con errores":
            return "bg-red-100 text-red-700";

        default:
            return "bg-gray-100 text-gray-700";
    }

}

onMounted(async () => {

    await loadClients();

    await load();

});

</script>
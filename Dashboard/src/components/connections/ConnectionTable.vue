<template>

<div class="bg-white rounded-xl border border-neutral-200 shadow-sm">

    <table class="w-full">

        <thead class="bg-neutral-50">

            <tr>
                <th class="text-left p-4">Id</th>
                <th class="text-left p-4">Cliente</th>
                <th class="text-left p-4">Host</th>
                <th class="text-left p-4">Puerto</th>
                <th class="text-left p-4">Usuario</th>
                <th class="text-left p-4">Protocolo</th>
                <th class="text-left p-4">Estado</th>
                <th class="text-left p-4">Install Token</th>
                <th class="text-left p-4">Remote Path</th>
                <th class="text-left p-4">Estado de instalación</th>
                <th class="text-center p-4">Acciones</th>

            </tr>

        </thead>

        <tbody>

            <tr
                v-for="connection in connections"
                :key="connection.id"
                class="border-t"
            >
                <td class="p-4">
                    {{ connection.id }}
                </td>

                <td class="p-4">
                    {{ connection.client_name || '-' }}
                </td>

                <td class="p-4">
                    {{ connection.host }}
                </td>

                <td class="p-4">
                    {{ connection.port }}
                </td>

                <td class="p-4">
                    {{ connection.username }}
                </td>

                <td class="p-4">
                    {{ connection.protocol }}
                </td>

                <td class="p-4">
                    <span
                        :class="connection.install_token ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'"
                        class="px-2 py-1 rounded-full text-xs font-semibold"
                    >
                        {{ connection.install_token ? 'Listo' : 'Sin token' }}
                    </span>
                </td>

                <td class="p-4">
                    <span class="font-mono text-xs break-all">
                        {{ connection.install_token || '-' }}
                    </span>
                </td>

                <td class="p-4">
                    {{ connection.remote_path }}
                </td>

                <td class="p-4">
                    <span
                        :class="Number(connection.installed) === 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'"
                        class="px-2 py-1 rounded-full text-xs font-semibold"
                    >
                        {{ Number(connection.installed) === 1 ? 'Instalado' : 'No instalado' }}
                    </span>
                </td>

                <td class="p-4">

                    <div class="flex justify-center gap-2">

                        <button
                            @click="$emit('edit',connection)"
                            class="bg-amber-500 hover:bg-amber-600 text-white rounded p-2"
                        >
                            <Pencil :size="17"/>
                        </button>
                                                
                        <button
                            @click="$emit('toggle-installed', connection)"
                            :class="Number(connection.installed) === 1 ? 'bg-blue-600 hover:bg-blue-700' : 'bg-neutral-500 hover:bg-neutral-600'"
                            class="text-white rounded p-2"
                            :title="Number(connection.installed) === 1 ? 'Marcar como no instalado' : 'Marcar como instalado'"
                        >
                            <ToggleRight v-if="Number(connection.installed) === 1" :size="17"/>
                            <ToggleLeft v-else :size="17"/>
                        </button>



                        <!-- <button
                            @click="$emit('generate-install',connection)"
                            class="bg-blue-600 hover:bg-blue-700 text-white rounded p-2"
                            title="Generar instalación"
                        >
                            <Download :size="17"/>
                        </button> -->



                        <button
                            @click="$emit('delete',connection.id)"
                            class="bg-red-600 hover:bg-red-700 text-white rounded p-2"
                        >
                            <Trash2 :size="17"/>
                        </button>

                    </div>

                </td>

            </tr>

            <tr v-if="connections.length===0">

                <td
                    colspan="12"
                    class="text-center p-10 text-neutral-400"
                >

                    No existen conexiones.

                </td>

            </tr>

        </tbody>

    </table>

</div>

</template>

<script setup>

import {

    Pencil,
    Trash2,
    Download,
    ToggleLeft,
    ToggleRight

} from "lucide-vue-next";

defineProps({

    connections:{
        type:Array,
        default:()=>[]
    }

})

defineEmits([

    'edit',
    'delete',
    'generate-install',
    'toggle-installed'

])

</script>
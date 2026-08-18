<template>

<MainLayout>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold">

                Logs del Sistema

            </h1>

            <p class="text-neutral-500 mt-2">

                Visualización en tiempo real de los eventos del sistema.

            </p>

        </div>

        <div class="flex items-center gap-4">

            <select
                v-model="limit"
                @change="changeLimit"
                class="border rounded-lg px-3 py-2"
            >

                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>

            </select>

            <button
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                @click="load"
            >

                Actualizar

            </button>

        </div>

    </div>

    <div class="bg-white rounded-xl border border-neutral-200 shadow-sm overflow-hidden">

        <table class="w-full">

            <thead class="bg-neutral-100">

                <tr>

                    <th class="text-left p-4 w-48">

                        Fecha

                    </th>

                    <th class="text-left p-4 w-28">

                        Nivel

                    </th>

                    <th class="text-left p-4">

                        Mensaje

                    </th>

                </tr>

            </thead>

            <tbody>

                <tr
                    v-for="(item,index) in logs"
                    :key="index"
                    class="border-t hover:bg-neutral-50"
                >

                    <td class="p-4 whitespace-nowrap">

                        {{ item.date }}

                    </td>

                    <td class="p-4">

                        <span
                            class="px-2 py-1 rounded text-xs font-semibold"
                            :class="badge(item.level)"
                        >

                            {{ item.level }}

                        </span>

                    </td>

                    <td class="p-4 font-mono text-sm whitespace-pre-wrap break-words">

                        {{ item.message }}

                    </td>

                </tr>

                <tr v-if="loading">

                    <td
                        colspan="3"
                        class="text-center py-10"
                    >

                        Cargando...

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

    <div
        class="flex justify-between items-center mt-6"
    >

        <div class="text-sm text-neutral-500">

            Página {{ page }} de {{ pages }}

            ·

            {{ total }} registros

        </div>

        <div class="flex gap-2">

            <button
                class="border rounded px-3 py-2"
                @click="firstPage"
                :disabled="page===1"
            >

                «

            </button>

            <button
                class="border rounded px-3 py-2"
                @click="prevPage"
                :disabled="page===1"
            >

                ‹

            </button>

            <button
                class="border rounded px-3 py-2"
                @click="nextPage"
                :disabled="page===pages"
            >

                ›

            </button>

            <button
                class="border rounded px-3 py-2"
                @click="lastPage"
                :disabled="page===pages"
            >

                »

            </button>

        </div>

    </div>

</MainLayout>

</template>

<script setup>

import {

    ref,
    onMounted,
    onUnmounted

} from "vue";

import MainLayout from "../components/layout/MainLayout.vue";

import {

    getLogs

} from "../api/client";

const logs = ref([]);

const loading = ref(false);

const page = ref(1);

const pages = ref(1);

const total = ref(0);

const limit = ref(50);

let timer = null;

function badge(level){

    switch(level){

        case "INFO":

            return "bg-blue-100 text-blue-700";

        case "WARNING":

            return "bg-yellow-100 text-yellow-700";

        case "ERROR":

            return "bg-red-100 text-red-700";

        default:

            return "bg-neutral-100 text-neutral-700";

    }

}

async function load(){

    loading.value = true;

    try{

        const response = await getLogs(

            page.value,

            limit.value

        );

        logs.value = response.data;

        pages.value = response.pages;

        total.value = response.total;

    }
    finally{

        loading.value = false;

    }

}

function nextPage(){

    if(page.value<pages.value){

        page.value++;

        load();

    }

}

function prevPage(){

    if(page.value>1){

        page.value--;

        load();

    }

}

function firstPage(){

    page.value=1;

    load();

}

function lastPage(){

    page.value=pages.value;

    load();

}

function changeLimit(){

    page.value=1;

    load();

}

onMounted(()=>{

    load();

    timer=setInterval(load,20000);

});

onUnmounted(()=>{

    clearInterval(timer);

});

</script>

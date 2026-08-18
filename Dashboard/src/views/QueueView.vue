<template>

<MainLayout>

    <div class="p-8">

        <PageHeader
            title="Queue Monitor"
            subtitle="Monitoreo en tiempo real de la cola de trabajos."
        />

        <div class="mb-6 flex flex-wrap gap-3 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">
            <input v-model="filters.from" type="date" class="border rounded-lg px-3 py-2" @change="applyFilters" />
            <input v-model="filters.to" type="date" class="border rounded-lg px-3 py-2" @change="applyFilters" />

            <select v-model="filters.clientId" class="border rounded-lg px-3 py-2" @change="applyFilters">
                <option :value="0">Todos los clientes</option>
                <option v-for="client in filterOptions.clients" :key="client.id" :value="client.id">{{ client.name }}</option>
            </select>

            <select v-model="filters.jobId" class="border rounded-lg px-3 py-2" @change="applyFilters">
                <option :value="0">Todos los trabajos</option>
                <option v-for="job in filterOptions.jobs" :key="job.id" :value="job.id">{{ job.name }}</option>
            </select>

            <select v-model="filters.status" class="border rounded-lg px-3 py-2" @change="applyFilters">
                <option value="">Todos los estados</option>
                <option v-for="statusOption in filterOptions.statuses" :key="statusOption" :value="statusOption">{{ statusOption }}</option>
            </select>

            <button @click="resetFilters" class="border rounded-lg px-4 py-2">Limpiar</button>
        </div>

        <QueueStatistics
            :items="queue"
        />

        <div v-if="loading">

            <Loading />

        </div>

        <div v-else-if="queue.length === 0">

            <EmptyState
                message="No existen trabajos en la cola."
            />

        </div>

        <QueueTable
            v-else
            :items="queue"
            :on-finish="finishQueue"
        />

    </div>

</MainLayout>

</template>

<script setup>

import {
    ref,
    onMounted,
    onUnmounted
} from "vue";

import MainLayout from "@/components/layout/MainLayout.vue";

import PageHeader from "@/components/common/PageHeader.vue";
import Loading from "@/components/common/Loading.vue";
import EmptyState from "@/components/common/EmptyState.vue";

import QueueTable from "@/components/queue/QueueTable.vue";
import QueueStatistics from "@/components/queue/QueueStatistics.vue";

import {
    completeQueue,
    getQueue
} from "@/api/client";

const isFirstLoad = ref(true)

const queue = ref([]);
const loading = ref(true);
const filterOptions = ref({ clients: [], jobs: [], statuses: [] });
const filters = ref({
    clientId: 0,
    jobId: 0,
    status: '',
    from: '',
    to: ''
});

async function load(){ 

    if (isFirstLoad.value) {
        loading.value = true;
    }

    try{

        const response = await getQueue({
            clientId: filters.value.clientId,
            jobId: filters.value.jobId,
            status: filters.value.status,
            from: filters.value.from,
            to: filters.value.to
        });

        queue.value = response.data ?? response;
        filterOptions.value = response.filters ?? filterOptions.value;

    } catch (error) {
        console.error(error);
        queue.value = [];

    }finally{

        if (isFirstLoad.value) {
            loading.value = false;
            isFirstLoad.value = false;
        }

    }

}

async function finishQueue(item) {
    const response = await completeQueue(item.id)

    if (!response?.success) {
        throw new Error(response?.message ?? "No se pudo terminar la cola.")
    }

    await load()
}

function applyFilters() {
    load();
}

function resetFilters() {
    filters.value = {
        clientId: 0,
        jobId: 0,
        status: '',
        from: '',
        to: ''
    };
    load();
}

let timer = null;

onMounted(()=>{

    load();

    timer = setInterval(load,8000);

});

onUnmounted(()=>{

    if(timer){

        clearInterval(timer);

    }

});

</script>
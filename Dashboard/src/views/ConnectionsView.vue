<template>

<MainLayout>

    <div class="flex justify-between items-center mb-8">

        <h1 class="text-3xl font-bold">

            Conexiones

        </h1>

        <button
            @click="newConnection"
            class="bg-blue-600 text-white px-5 py-3 rounded-lg"
        >
            + Nueva conexión
        </button>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <div class="text-sm text-neutral-500">Total</div>
            <div class="text-2xl font-bold">{{ connections.length }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <div class="text-sm text-neutral-500">Con token</div>
            <div class="text-2xl font-bold">{{ withTokenCount }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <div class="text-sm text-neutral-500">Listas para instalar</div>
            <div class="text-2xl font-bold">{{ readyToInstallCount }}</div>
        </div>

    </div>

    <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl p-4 mb-6 text-sm">
        Genera paquetes de instalación desde cada conexión y revisa rápidamente si ya cuentan con token de instalación.
    </div>

    <ConnectionDialog
        v-model="showDialog"
        :connection="selectedConnection"
        @saved="connectionSaved"
    />

    <ConnectionTable
        :connections="connections"
        @edit="editConnection"
        @delete="removeConnection"
        @generate-install="generateInstallPackage"
        @toggle-installed="toggleInstallationStatus"
    />

</MainLayout>

</template>

<script setup>

import { ref, onMounted, computed } from 'vue'

import MainLayout from '../components/layout/MainLayout.vue'
import ConnectionDialog from '../components/dialogs/ConnectionDialog.vue'
import ConnectionTable from '../components/connections/ConnectionTable.vue'

import {
    getConnections,
    deleteConnection,
    downloadInstallPackage,
    updateConnectionInstallationStatus
} from '../api/client'

const connections = ref([])

const showDialog = ref(false)
const selectedConnection = ref(null)

const withTokenCount = computed(() =>
    connections.value.filter((connection) => connection.install_token).length
)

const readyToInstallCount = computed(() =>
    connections.value.filter((connection) => connection.install_token && connection.client_id).length
)

async function loadConnections(){

    const response = await getConnections()

    if (!response?.success || !Array.isArray(response.data)) {
        console.error('Failed loading connections', response)
        connections.value = []
        return
    }

    connections.value = response.data

}

function newConnection(){

    selectedConnection.value = null

    showDialog.value = true

}

function connectionSaved(){

    showDialog.value = false

    selectedConnection.value = null

    loadConnections()

}

function editConnection(connection){

    selectedConnection.value = {
        ...connection
    }

    showDialog.value = true

}

async function removeConnection(id){

    if(!confirm('¿Eliminar esta conexión?')){
        return
    }

    await deleteConnection(id)

    await loadConnections()

}

async function generateInstallPackage(connection){

    const response = await downloadInstallPackage(connection.id)

    if(!response.ok){
        alert('No se pudo generar el paquete de instalación')
        return
    }

    const blob = await response.blob()
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')

    link.href = url
    link.download = `backupcenter-install-${connection.id}.zip`
    link.click()

    URL.revokeObjectURL(url)

}

async function toggleInstallationStatus(connection){

    const response = await updateConnectionInstallationStatus(
        connection.id,
        Number(connection.installed) !== 1
    )

    if (!response?.success) {
        alert('No se pudo actualizar el estado de instalación')
        return
    }

    await loadConnections()

}

onMounted(loadConnections)

</script>
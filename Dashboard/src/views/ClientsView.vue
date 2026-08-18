<template>

<MainLayout>

    <div class="p-6">

        <div class="flex items-center justify-between mb-6">

            <div>

                <h1 class="text-2xl font-bold">
                    Clientes
                </h1>

                <p class="text-sm text-neutral-500">
                    Administración de clientes
                </p>

            </div>

<button
    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
    @click="newClient"
>
    Nuevo Cliente
</button>

        </div>

        <div class="bg-white rounded-xl shadow border overflow-hidden">

            <table class="w-full">

                <thead class="bg-neutral-100">

                    <tr>
                        <th class="text-left p-3">Id</th>
                        <th class="text-left p-3">Código</th>
                        <th class="text-left p-3">Razón Social</th>
                        <th class="text-left p-3">Contacto</th>
                        <th class="text-left p-3">Correo</th>
                        <th class="text-left p-3">SFTP Alias</th>
                        <th class="text-center p-3">Estado</th>
                        
                        <th class="text-center p-3">Acciones</th>

                    </tr>

                </thead>

                <tbody>

                    <tr
                        v-for="client in clients"
                        :key="client.id"
                        class="border-t hover:bg-neutral-50"
                    >

                        <td class="p-3">
                            {{ client.id }}
                        </td>

                        <td class="p-3">
                            {{ client.code }}
                        </td>

                        <td class="p-3">
                            {{ client.business_name }}
                        </td>

                        <td class="p-3">
                            {{ client.contact_name }}
                        </td>

                        <td class="p-3">
                            {{ client.email }}
                        </td>

                        <td class="p-3">
                            {{ client.sftp_alias }}
                        </td>

                        <td class="p-3 text-center">

                            <span
                                class="px-2 py-1 rounded text-xs font-semibold"
                                :class="client.status == 1
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'"
                            >

                                {{ client.status == 1 ? 'Activo' : 'Inactivo' }}

                            </span>

                        </td>

<td class="p-3">

    <div class="flex justify-center gap-2">

        <button
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 transition"
            title="Editar"
            @click="editClient(client)"
        >
            <Pencil :size="17"/>
        </button>

        <button
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition"
            title="Eliminar"
            @click="removeClient(client)"
        >
            <Trash2 :size="17"/>
        </button>

        <button
            class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-200 transition"
            title="Restablecer contraseña SFTP"
            @click="resetPassword(client)"
        >
            <KeyRound :size="17"/>
        </button>        

    </div>

</td>

                    </tr>

                    <tr v-if="clients.length === 0">

                        <td
                            colspan="6"
                            class="text-center text-neutral-500 py-10"
                        >

                            No existen clientes registrados.

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

<!-- Modal -->

<div
    v-if="showForm"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl">

        <div class="flex items-center justify-between px-6 py-4 border-b">

            <div>

                <h2 class="text-xl font-bold">

                    {{ selectedClient ? "Editar Cliente" : "Nuevo Cliente" }}

                </h2>

                <p class="text-sm text-neutral-500">

                    Complete la información del cliente.

                </p>

            </div>

            <button
                class="text-2xl text-neutral-500 hover:text-red-600"
                @click="closeForm"
            >
                ×
            </button>

        </div>

        <div class="p-6">

            <ClientForm

                :client="selectedClient"

                @save="saveClient"

                @close="closeForm"

            />

        </div>

    </div>

</div>

<div
    v-if="showPassword"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">

        <h2 class="text-xl font-bold mb-6">
            Nueva contraseña SFTP
        </h2>

        <div class="space-y-4">

            <div>

                <label class="text-sm font-semibold">
                    Usuario
                </label>

                <input
                    class="w-full border rounded-lg p-2"
                    :value="credentials.username"
                    readonly
                >

            </div>

            <div>

                <label class="text-sm font-semibold">
                    Contraseña
                </label>

                <input
                    class="w-full border rounded-lg p-2"
                    :value="credentials.password"
                    readonly
                >

            </div>

        </div>

        <div class="flex justify-end mt-6">

            <button
                class="px-4 py-2 bg-blue-600 text-white rounded-lg"
                @click="showPassword=false"
            >
                Cerrar
            </button>

        </div>

    </div>

</div>


</MainLayout>

</template>

<script setup>

import {

    KeyRound,
    Pencil,
    Trash2

} from "lucide-vue-next";

import { ref, onMounted } from "vue"

import MainLayout from "../components/layout/MainLayout.vue"
import ClientForm from "../components/clients/ClientForm.vue"

import {

    getClients,
    createClient,
    updateClient,
    deleteClient,
    resetClientPassword

} from "@/api/client"

const clients = ref([])
const selectedClient = ref(null)
const showForm = ref(false)

const showPassword = ref(false)

const credentials = ref({
    username: "",
    password: ""
})

async function loadClients() {

    try {

const response = await getClients()

clients.value = response.data

    }
    catch (error) {

        console.error(error)

    }

}

function newClient() {

    showForm.value = true

}

function editClient(client) {

    selectedClient.value = { ...client }

    showForm.value = true

}

async function removeClient(client) {

    const ok = confirm(

        `¿Desea eliminar el cliente "${client.business_name}"?`

    )

    if (!ok) {

        return

    }

    try {

        await deleteClient(client.id)

        await loadClients()

    }
    catch (error) {

        console.error(error)

        alert("No fue posible eliminar el cliente.")

    }

}

async function resetPassword(client) {

    const ok = confirm(
        `¿Restablecer la contraseña SFTP de "${client.business_name}"?`
    )

    if (!ok) return

    const response = await resetClientPassword(client.id)

    if (!response.success) {

        alert(response.message)

        return

    }

    credentials.value = {
        username: response.username,
        password: response.password
    }

    showPassword.value = true

}


function closeForm() {

    selectedClient.value = null

    showForm.value = false

}

async function saveClient(client) {

    let response;

    if (client.id) {

        response = await updateClient(client);

    } else {

        response = await createClient(client);

    }

    if (!response.success) {

        alert(response.message);

        return;

    }

    await loadClients();

    closeForm();

}

onMounted(loadClients)

</script>
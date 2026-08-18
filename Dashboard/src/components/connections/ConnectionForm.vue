<template>

<div class="space-y-4">

    <select
        v-model="connection.client_id"
        class="w-full border rounded-lg p-3"
    >
        <option :value="null">
            Seleccione un cliente
        </option>

        <option
            v-for="client in clients"
            :key="client.id"
            :value="client.id"
        >
            {{ client.business_name }}
        </option>
    </select>

    <input
        v-model="connection.name"
        placeholder="Nombre de la conexión"
        class="w-full border rounded-lg p-3"
    >

    <input
        v-model="connection.host"
        placeholder="Host"
        class="w-full border rounded-lg p-3"
    >

    <input
        v-model="connection.port"
        type="number"
        placeholder="Puerto"
        class="w-full border rounded-lg p-3"
    >

    <input
        v-model="connection.username"
        placeholder="Usuario"
        class="w-full border rounded-lg p-3"
    >

    <input
        v-model="connection.password"
        type="password"
        placeholder="Contraseña"
        class="w-full border rounded-lg p-3"
    >

    <input
        v-model="connection.hostkey"
        placeholder="Host Key"
        class="w-full border rounded-lg p-3"
    >

    <select
        v-model="connection.protocol"
        class="w-full border rounded-lg p-3"
    >
        <option>SFTP</option>
        <option>FTP</option>
    </select>

    <input
        v-model="connection.remote_path"
        placeholder="Ruta remota"
        class="w-full border rounded-lg p-3"
    >

    <div class="flex justify-end">

        <button
            @click="save"
            class="bg-blue-600 text-white px-5 py-3 rounded-lg"
        >
            Guardar
        </button>

    </div>

</div>

</template>

<script setup>

import {
    reactive,
    watch,
    ref,
    onMounted
} from 'vue'

import {
    createConnection,
    updateConnection,
    getClients
} from '../../api/client'

const emit = defineEmits([
    'saved'
])

const props = defineProps({

    connection:Object

})

const clients = ref([])

const connection = reactive({

    id:null,
    client_id:null,
    name:'',
    host:'',
    port:22,
    username:'',
    password:'',
    hostkey:'',
    protocol:'SFTP',
    remote_path:''

})

watch(

    () => props.connection,

    (value)=>{

        if(value){

            Object.assign(connection,value)

            connection.password=''

        }else{

            Object.assign(connection,{
                id:null,
                client_id:null,
                name:'',
                host:'',
                port:22,
                username:'',
                password:'',
                hostkey:'',
                protocol:'SFTP',
                remote_path:''
            })

        }

    },

    {
        immediate:true
    }

)

async function loadClients(){

    const response = await getClients()

    if(response.success){

        clients.value = response.data

    }

}

async function save(){

    if(connection.id){

        await updateConnection(connection)

    }else{

        await createConnection(connection)

    }

    emit('saved')

}

onMounted(loadClients)

</script>
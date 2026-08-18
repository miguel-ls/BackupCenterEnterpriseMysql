<template>

<div class="bg-white rounded-xl">

    <div class="grid grid-cols-2 gap-6">

        <!-- Información general -->

        <div class="space-y-5">

            <div>

                <label class="block text-sm font-semibold mb-2">
                    Nombre del trabajo
                </label>

                <input
                    v-model="props.job.name"
                    placeholder="Ej.: Backup ERP Producción"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >

            </div>

            <div>

                <label class="block text-sm font-semibold mb-2">
                    Conexión
                </label>

                <select
                    v-model="props.job.connection_id"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >

                    <option :value="null">
                        Seleccione una conexión...
                    </option>

                    <option
                        v-for="connection in connections"
                        :key="connection.id"
                        :value="connection.id"
                    >
                        {{ connection.name }}
                    </option>

                </select>

            </div>

<div>

    <label class="block text-sm font-semibold mb-2">
        Programación
    </label>

    <select
        v-model="scheduleType"
        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
    >
        <option value="daily">Cada día</option>
        <option value="weekly">Cada semana</option>
        <option value="monthly">Cada mes</option>
        <option value="hourly">Cada hora</option>
        <option value="minutes">Cada X minutos</option>
        <option value="custom">Personalizado (CRON)</option>
    </select>

    <!-- Diario -->

    <div
        v-if="scheduleType==='daily'"
        class="mt-4"
    >

        <label class="block text-sm mb-2">
            Hora
        </label>

        <input
            type="time"
            v-model="dailyTime"
            class="w-full border rounded-lg p-3"
        >

    </div>

    <!-- Semanal -->

    <div
        v-if="scheduleType==='weekly'"
        class="mt-4 space-y-3"
    >

        <div>

            <label class="block text-sm mb-2">
                Día
            </label>

            <select
                v-model="weekDay"
                class="w-full border rounded-lg p-3"
            >
                <option value="0">Domingo</option>
                <option value="1">Lunes</option>
                <option value="2">Martes</option>
                <option value="3">Miércoles</option>
                <option value="4">Jueves</option>
                <option value="5">Viernes</option>
                <option value="6">Sábado</option>
            </select>

        </div>

        <div>

            <label class="block text-sm mb-2">
                Hora
            </label>

            <input
                type="time"
                v-model="weeklyTime"
                class="w-full border rounded-lg p-3"
            >

        </div>

    </div>

    <!-- Mensual -->

    <div
        v-if="scheduleType==='monthly'"
        class="mt-4 space-y-3"
    >

        <div>

            <label class="block text-sm mb-2">
                Día del mes
            </label>

            <input
                type="number"
                min="1"
                max="31"
                v-model="monthDay"
                class="w-full border rounded-lg p-3"
            >

        </div>

        <div>

            <label class="block text-sm mb-2">
                Hora
            </label>

            <input
                type="time"
                v-model="monthlyTime"
                class="w-full border rounded-lg p-3"
            >

        </div>

    </div>

    <!-- Cada hora -->

    <div
        v-if="scheduleType==='hourly'"
        class="mt-4"
    >

        <label class="block text-sm mb-2">
            Minuto
        </label>

        <input
            type="number"
            min="0"
            max="59"
            v-model="hourMinute"
            class="w-full border rounded-lg p-3"
        >

    </div>

    <!-- Cada X minutos -->

    <div
        v-if="scheduleType==='minutes'"
        class="mt-4"
    >

        <label class="block text-sm mb-2">
            Intervalo
        </label>

        <select
            v-model="minuteInterval"
            class="w-full border rounded-lg p-3"
        >
            <option value="5">5 minutos</option>
            <option value="10">10 minutos</option>
            <option value="15">15 minutos</option>
            <option value="30">30 minutos</option>
        </select>

    </div>

    <!-- Personalizado -->

    <div
        v-if="scheduleType==='custom'"
        class="mt-4"
    >

        <input
            v-model="props.job.schedule"
            placeholder="0 */6 * * *"
            class="w-full border rounded-lg p-3"
        >

    </div>

    <div class="mt-4 p-3 rounded-lg bg-blue-50 border border-blue-200">

        <div class="text-sm font-semibold text-blue-700">
            Expresión CRON
        </div>

        <div class="font-mono text-blue-800 mt-1">
            {{ props.job.schedule }}
        </div>

    </div>

</div>

        </div>

        <!-- Rutas -->

        <div class="space-y-5">

            <div>

                <label class="block text-sm font-semibold mb-2">
                    Carpeta origen
                </label>

                <input
                    v-model="props.job.source"
                    placeholder="D:\Backups"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >

            </div>

            <div>

                <label class="block text-sm font-semibold mb-2">
                    Carpeta destino
                </label>

                <input
                    v-model="props.job.destination"
                    placeholder="/backups/unimarket"
                    class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >

            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">

                <div class="font-semibold text-blue-700 mb-2">
                    Información
                </div>

                <ul class="text-sm text-blue-700 space-y-1">

                    <li>• El origen corresponde a la carpeta local.</li>

                    <li>• El destino corresponde a la carpeta remota SFTP.</li>

                    <li>• El trabajo utilizará la conexión seleccionada.</li>

                </ul>

            </div>

        </div>

    </div>

    <div class="border-t mt-8 pt-6 flex justify-end gap-3">

        <button
            type="button"
            class="px-5 py-3 rounded-lg border hover:bg-neutral-100"
            @click="$emit('saved')"
        >
            Cancelar
        </button>

        <button
            @click="save"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold"
        >
            💾 Guardar trabajo
        </button>

    </div>

</div>

</template>

<script setup>

import {
    onMounted,
    ref,
    watch
} from 'vue'

import {
    createJob,
    updateJob,
    getConnections
} from '../../api/client'

const emit = defineEmits([
    'saved'
])

const props = defineProps({

    job: Object

})

const connections = ref([])

const scheduleType = ref("daily")

const dailyTime = ref("22:00")

const weeklyTime = ref("22:00")

const monthlyTime = ref("22:00")

const weekDay = ref("1")

const monthDay = ref(1)

const hourMinute = ref(0)

const minuteInterval = ref(15)

async function loadConnections(){

    const response = await getConnections()

    connections.value = response.data

}

const loadingCron = ref(false)

function buildCron(){

    if(loadingCron.value){

        return

    }

    switch(scheduleType.value){

        case "daily":

            {

                const [hour, minute] = dailyTime.value.split(":")

                props.job.schedule =
                    `${minute} ${hour} * * *`

            }

            break

        case "weekly":

            {

                const [hour, minute] = weeklyTime.value.split(":")

                props.job.schedule =
                    `${minute} ${hour} * * ${weekDay.value}`

            }

            break

        case "monthly":

            {

                const [hour, minute] = monthlyTime.value.split(":")

                props.job.schedule =
                    `${minute} ${hour} ${monthDay.value} * *`

            }

            break

        case "hourly":

            props.job.schedule =
                `${hourMinute.value} * * * *`

            break

        case "minutes":

            props.job.schedule =
                `*/${minuteInterval.value} * * * *`

            break

        case "custom":

            break

    }

}

function parseCron(){

    loadingCron.value = true

    if(!props.job.schedule){

        loadingCron.value = false

        buildCron()

        return

    }

    const cron = props.job.schedule.trim().split(" ")

    if(cron.length!==5){

        scheduleType.value="custom"

        loadingCron.value = false

        return

    }

    const minute = cron[0]

    const hour = cron[1]

    const day = cron[2]

    const month = cron[3]

    const week = cron[4]

    if(minute.startsWith("*/")){

        scheduleType.value = "minutes"

        minuteInterval.value = parseInt(minute.replace("*/",""))

        loadingCron.value = false

        return

    }

    if(hour==="*" && day==="*" && month==="*" && week==="*"){

        scheduleType.value="hourly"

        hourMinute.value=parseInt(minute)

        loadingCron.value = false

        return

    }

    if(day==="*" && month==="*" && week==="*"){

        scheduleType.value="daily"

        dailyTime.value=
            `${hour.padStart(2,"0")}:${minute.padStart(2,"0")}`

        loadingCron.value = false

        return

    }

    if(day==="*" && month==="*" && week!=="*"){

        scheduleType.value="weekly"

        weekDay.value=week

        weeklyTime.value=
            `${hour.padStart(2,"0")}:${minute.padStart(2,"0")}`

        loadingCron.value = false
        
        return

    }

    if(day!=="*" && month==="*" && week==="*"){

        scheduleType.value="monthly"

        monthDay.value=parseInt(day)

        monthlyTime.value=
            `${hour.padStart(2,"0")}:${minute.padStart(2,"0")}`

        loadingCron.value = false
        
        return

    }

    scheduleType.value="custom"

    loadingCron.value = false

}

watch(

    [

        scheduleType,

        dailyTime,

        weeklyTime,

        monthlyTime,

        weekDay,

        monthDay,

        hourMinute,

        minuteInterval

    ],

    buildCron

)


async function save(){

    if(!props.job.name){

        alert('Ingrese el nombre del trabajo.')

        return

    }

    if(!props.job.connection_id){

        alert('Seleccione una conexión.')

        return

    }

    if(props.job.id){

        await updateJob(props.job)

    }else{

        await createJob(props.job)

    }

    emit('saved')

}

onMounted(async()=>{

    await loadConnections()

    parseCron()

})

</script>
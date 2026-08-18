<template>

<div class="bg-white rounded-xl">

    <div class="grid grid-cols-2 gap-5">

        <div>

            <label class="block text-sm font-semibold mb-2">
                Código
            </label>

            <input
                v-model="form.code"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div>

            <label class="block text-sm font-semibold mb-2">
                RUC
            </label>

            <input
                v-model="form.ruc"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div class="col-span-2">

            <label class="block text-sm font-semibold mb-2">
                Razón Social
            </label>

            <input
                v-model="form.business_name"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div class="col-span-2">

            <label class="block text-sm font-semibold mb-2">
                Nombre Comercial
            </label>

            <input
                v-model="form.trade_name"
                @input="form.sftp_alias = form.trade_name
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g,'')
                    .replace(/\s+/g,'')
                    .replace(/[^a-z0-9_-]/g,'')"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

<div class="col-span-2">

    <label class="block text-sm font-semibold mb-2">
        Alias SFTP
    </label>

    <input
        v-model="form.sftp_alias"
        class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
    >

</div>        

        <div>

            <label class="block text-sm font-semibold mb-2">
                Contacto
            </label>

            <input
                v-model="form.contact_name"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div>

            <label class="block text-sm font-semibold mb-2">
                Teléfono
            </label>

            <input
                v-model="form.phone"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div class="col-span-2">

            <label class="block text-sm font-semibold mb-2">
                Correo
            </label>

            <input
                v-model="form.email"
                type="email"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div class="col-span-2">

            <label class="block text-sm font-semibold mb-2">
                Dirección
            </label>

            <input
                v-model="form.address"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

        </div>

        <div>

            <label class="block text-sm font-semibold mb-2">
                Estado
            </label>

            <select
                v-model="form.status"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >

                <option :value="1">Activo</option>
                <option :value="0">Inactivo</option>

            </select>

        </div>

        <div class="col-span-2">

            <label class="block text-sm font-semibold mb-2">
                Observaciones
            </label>

            <textarea
                v-model="form.notes"
                rows="4"
                class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
            ></textarea>

        </div>

    </div>

    <div class="border-t mt-8 pt-6 flex justify-end gap-3">

        <button
            type="button"
            class="px-5 py-3 rounded-lg border hover:bg-neutral-100"
            @click="close"
        >
            Cancelar
        </button>

        <button
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold"
            @click="save"
        >
            💾 Guardar cliente
        </button>

    </div>

</div>

</template>

<script setup>

import {
    reactive,
    watch
} from "vue"

const props = defineProps({

    client: {

        type: Object,

        default: null

    }

})

const emit = defineEmits([
    "close",
    "save"
])

const emptyForm = {

    id: null,

    code: "",

    business_name: "",

    trade_name: "",

    sftp_alias: "",

    ruc: "",

    contact_name: "",

    email: "",

    phone: "",

    address: "",

    status: 1,

    notes: ""

}

const form = reactive({

    ...emptyForm

})

watch(

    () => props.client,

    (client) => {

        if (client) {

            Object.assign(form, emptyForm, client)

        } else {

            Object.assign(form, emptyForm)

        }

    },

    {

        immediate: true

    }

)

function close() {

    emit("close")

}

function save() {

    emit("save", {

        ...form

    })

}

</script>
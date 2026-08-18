<template>

<div
    v-if="modelValue"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

    <div class="bg-white rounded-xl shadow-xl w-[700px] p-6">

        <div class="flex justify-between items-center mb-6">

            <h2 class="text-2xl font-bold">

                {{ connection?.id ? 'Editar conexión' : 'Nueva conexión' }}

            </h2>

            <button
                @click="close"
                class="text-2xl"
            >
                ×
            </button>

        </div>

        <ConnectionForm
            :connection="connection"
            @saved="saved"
        />

    </div>

</div>

</template>

<script setup>

import ConnectionForm from '../connections/ConnectionForm.vue'

const emit = defineEmits([
    'update:modelValue',
    'saved'
])

defineProps({

    modelValue: Boolean,

    connection: Object

})

function close(){

    emit('update:modelValue', false)

}

function saved(){

    emit('saved')

    emit('update:modelValue', false)

}

</script>
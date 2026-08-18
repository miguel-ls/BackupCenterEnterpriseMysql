<template>

<div
    v-if="modelValue"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50"
>

    <div
        class="bg-white rounded-2xl shadow-2xl w-[760px] max-w-[95vw] overflow-hidden"
    >

        <!-- Encabezado -->

        <div
            class="flex items-center justify-between px-6 py-5 border-b bg-gradient-to-r from-blue-600 to-blue-700 text-white"
        >

            <div>

                <h2 class="text-2xl font-bold">

                    {{ props.job?.id ? 'Editar trabajo' : 'Nuevo trabajo' }}

                </h2>

                <p class="text-blue-100 text-sm mt-1">

                    Configure el trabajo de respaldo.

                </p>

            </div>

            <button
                @click="close"
                class="w-9 h-9 rounded-full hover:bg-white/20 transition text-2xl leading-none"
            >
                ×
            </button>

        </div>

        <!-- Contenido -->

        <div class="p-6 bg-neutral-50">

            <JobForm
                :job="props.job"
                @saved="saved"
            />

        </div>

    </div>

</div>

</template>

<script setup>

import JobForm from '../jobs/JobForm.vue'

const emit = defineEmits([

    'update:modelValue',

    'saved'

])

const props = defineProps({

    modelValue:Boolean,

    job:Object

})

function close(){

    emit('update:modelValue',false)

}

function saved(){

    emit('saved')

    emit('update:modelValue',false)

}

</script>
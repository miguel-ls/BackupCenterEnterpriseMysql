<template>

<div class="mb-6">

    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold">

                {{ title }}

            </h1>

            <p
                v-if="subtitle"
                class="text-neutral-500 mt-1"
            >
                {{ subtitle }}
            </p>

        </div>

        <div>

            <slot />

        </div>

    </div>

</div>

</template>

<script setup>

defineProps({

    title: {
        type: String,
        required: true
    },

    subtitle: {
        type: String,
        default: ''
    }

})

</script>
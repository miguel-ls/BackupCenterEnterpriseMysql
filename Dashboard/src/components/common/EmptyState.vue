<template>

<div class="py-12 text-center text-neutral-400">

    {{ message }}

</div>

</template>

<script setup>

defineProps({

    message: {
        type: String,
        default: "No hay información."
    }

})

</script>
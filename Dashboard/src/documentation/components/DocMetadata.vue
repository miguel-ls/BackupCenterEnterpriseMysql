<template>

    <div class="flex flex-wrap gap-3 mb-10">

        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm">

            {{ version }}

        </span>

        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">

            {{ updated }}

        </span>

        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm">

            {{ reading }}

        </span>

    </div>

</template>

<script setup>

defineProps({

    version: String,

    updated: String,

    reading: String

});

</script>
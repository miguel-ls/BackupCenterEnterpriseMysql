<template>
    <figure class="my-8">
        <img
            :src="src"
            :alt="alt"
            class="rounded-lg border shadow-sm w-full"
        />

        <figcaption
            v-if="caption"
            class="text-center text-sm text-gray-500 mt-3"
        >
            {{ caption }}
        </figcaption>
    </figure>
</template>

<script setup>
defineProps({
    src: String,
    alt: String,
    caption: String
});
</script>
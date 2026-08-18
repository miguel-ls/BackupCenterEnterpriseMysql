<template>

<span
    :class="badgeClass"
    class="px-2 py-1 rounded-full text-xs font-semibold"
>

    {{ status }}

</span>

</template>

<script setup>

import { computed } from 'vue'

const props = defineProps({

    status: {
        type: String,
        required: true
    }

})

const badgeClass = computed(() => {

    switch (props.status) {

        case 'Pending':
            return 'bg-yellow-100 text-yellow-700'

        case 'Running':
            return 'bg-blue-100 text-blue-700'

        case 'Completed':
            return 'bg-green-100 text-green-700'

        case 'Failed':
            return 'bg-red-100 text-red-700'

        default:
            return 'bg-neutral-200 text-neutral-700'

    }

})

</script>
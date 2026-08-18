<template>

<div :class="['doc-alert', type]">

    <div class="icon">

        <slot name="icon">

            {{ icon }}

        </slot>

    </div>

    <div class="body">

        <strong>{{ title }}</strong>

        <div class="content">

            <slot />

        </div>

    </div>

</div>

</template>

<script setup>

import { computed } from "vue"

const props = defineProps({

    type: {
        type: String,
        default: "info"
    },

    title: {
        type: String,
        default: "Información"
    }

})

const icon = computed(() => {

    switch (props.type) {

        case "success":
            return "✅"

        case "warning":
            return "⚠️"

        case "danger":
            return "❌"

        default:
            return "ℹ️"

    }

})

</script>

<style scoped>

.doc-alert{

    display:flex;
    gap:16px;

    padding:20px;

    border-radius:12px;

    margin:20px 0;

    border-left:6px solid;

}

.icon{

    font-size:28px;

    line-height:1;

}

.body{

    flex:1;

}

.body strong{

    display:block;

    margin-bottom:8px;

}

.info{

    background:#eff6ff;
    border-color:#2563eb;

}

.success{

    background:#ecfdf5;
    border-color:#16a34a;

}

.warning{

    background:#fff7ed;
    border-color:#ea580c;

}

.danger{

    background:#fef2f2;
    border-color:#dc2626;

}

</style>
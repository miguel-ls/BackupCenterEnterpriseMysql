<template>

<div class="doc-code">

    <div class="header">

        <span>{{ languageLabel }}</span>

        <button @click="copy">

            Copiar

        </button>

    </div>

    <pre>

        <code ref="codeRef">

            <slot />

        </code>

    </pre>

</div>

</template>

<script setup>

import { computed, ref } from "vue"

const props = defineProps({

    language: {
        type: String,
        default: "bash"
    }

})

const languageLabel = computed(() => props.language.toUpperCase())
const codeRef = ref(null)

async function copy() {

    if (!codeRef.value) return

    await navigator.clipboard.writeText(codeRef.value.innerText)

}

</script>

<style scoped>

.doc-code{

    margin:25px 0;

    border-radius:14px;

    overflow:hidden;

    border:1px solid #d1d5db;

}

.header{

    background:#111827;

    color:white;

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:10px 18px;

    font-size:14px;

}

button{

    background:#2563eb;

    color:white;

    border:none;

    padding:6px 14px;

    border-radius:8px;

    cursor:pointer;

}

button:hover{

    background:#1d4ed8;

}

pre{

    margin:0;

    background:#1f2937;

    color:#e5e7eb;

    padding:22px;

    overflow:auto;

    font-family:Consolas, monospace;

    font-size:14px;

}

</style>
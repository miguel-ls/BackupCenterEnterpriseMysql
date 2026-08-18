<template>

    <div class="doc-card">

        <slot />

    </div>

</template>

<script setup>
</script>

<style scoped>

.doc-card{

    background:#fff;

    border-radius:18px;

    border:1px solid #e5e7eb;

    padding:28px;

    transition:.25s;

    box-shadow:0 4px 18px rgba(0,0,0,.05);

    height:100%;

}

.doc-card:hover{

    transform:translateY(-6px);

    box-shadow:0 15px 35px rgba(0,0,0,.12);

    border-color:#2563eb;

}

</style>
<template>

<section class="doc-section">

    <h2>{{ title }}</h2>

    <div class="content">

        <slot />

    </div>

</section>

</template>

<script setup>

defineProps({

    title: {
        type: String,
        required: true
    }

})

</script>

<style scoped>

.doc-section{

    background:#ffffff;
    border-radius:16px;
    padding:32px;
    margin-bottom:30px;

    box-shadow:0 4px 14px rgba(0,0,0,.05);

}

h2{

    margin-top:0;
    margin-bottom:20px;

    font-size:24px;
    font-weight:600;

    color:#1f2937;

    border-bottom:1px solid #e5e7eb;

    padding-bottom:12px;

}

.content{

    line-height:1.8;

    color:#374151;

}

</style>
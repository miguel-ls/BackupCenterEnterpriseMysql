<template>

    <div class="doc-title">

        <div class="icon">

            <slot name="icon">
                📚
            </slot>

        </div>

        <div>

            <h1>{{ title }}</h1>

            <p v-if="subtitle">

                {{ subtitle }}

            </p>

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
        default: ""
    }

})

</script>

<style scoped>

.doc-title{

    display:flex;
    align-items:center;
    gap:20px;

    margin-bottom:35px;

}

.icon{

    width:70px;
    height:70px;

    border-radius:16px;

    background:#2563eb;

    color:white;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:34px;

}

h1{

    margin:0;
    font-size:34px;
    font-weight:700;

}

p{

    margin-top:8px;
    color:#6b7280;
    font-size:16px;

}

</style>
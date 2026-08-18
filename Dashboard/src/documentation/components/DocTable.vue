<template>

<div class="doc-table-wrapper">

    <table class="doc-table">

        <thead>

            <tr>

                <th
                    v-for="column in columns"
                    :key="column">

                    {{ column }}

                </th>

            </tr>

        </thead>

        <tbody>

            <tr
                v-for="(row,index) in rows"
                :key="index">

                <td
                    v-for="(value,key) in row"
                    :key="key">

                    {{ value }}

                </td>

            </tr>

        </tbody>

    </table>

</div>

</template>

<script setup>

defineProps({

    columns:{
        type:Array,
        required:true
    },

    rows:{
        type:Array,
        required:true
    }

})

</script>

<style scoped>

.doc-table-wrapper{

    overflow-x:auto;

    margin:25px 0;

}

.doc-table{

    width:100%;

    border-collapse:collapse;

    background:white;

    border-radius:12px;

    overflow:hidden;

}

.doc-table th{

    background:#2563eb;

    color:white;

    padding:14px;

    text-align:left;

    font-weight:600;

}

.doc-table td{

    padding:14px;

    border-bottom:1px solid #e5e7eb;

}

.doc-table tbody tr:hover{

    background:#f8fafc;

}

</style>
<template>

<div class="grid grid-cols-4 gap-6 mb-6">

    <div class="bg-white rounded-xl shadow p-6">

        <div class="text-sm text-neutral-500">

            Pending

        </div>

        <div class="text-3xl font-bold">

            {{ pending }}

        </div>

    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <div class="text-sm text-neutral-500">

            Running

        </div>

        <div class="text-3xl font-bold text-blue-600">

            {{ running }}

        </div>

    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <div class="text-sm text-neutral-500">

            Completed

        </div>

        <div class="text-3xl font-bold text-green-600">

            {{ completed }}

        </div>

    </div>

    <div class="bg-white rounded-xl shadow p-6">

        <div class="text-sm text-neutral-500">

            Failed

        </div>

        <div class="text-3xl font-bold text-red-600">

            {{ failed }}

        </div>

    </div>

</div>

</template>

<script setup>

import { computed } from "vue";

const props=defineProps({

    items:Array

});

const pending=computed(()=>props.items.filter(x=>x.status==="Pending").length);

const running=computed(()=>props.items.filter(x=>x.status==="Running").length);

const completed=computed(()=>props.items.filter(x=>x.status==="Completed").length);

const failed=computed(()=>props.items.filter(x=>x.status==="Failed").length);

</script>
<template>

<div class="flex h-screen bg-neutral-100">

    <Sidebar />

    <div class="flex flex-1 flex-col">

        <Topbar />

        <main class="flex-1 overflow-auto p-8">

            <slot />

        </main>

    </div>

</div>

</template>

<script setup>

import Sidebar from './Sidebar.vue'
import Topbar from './Topbar.vue'

</script>
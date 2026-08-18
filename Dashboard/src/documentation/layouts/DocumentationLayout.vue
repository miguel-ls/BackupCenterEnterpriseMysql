<template>

    <MainLayout>

        <div class="documentation">

            <DocHeader />

            <DocBreadcrumb />

            <div class="grid grid-cols-12 gap-10">

                <aside class="col-span-2">

                    <DocSidebar />

                </aside>

                <main class="col-span-10">

                    <slot />

                    <DocNavigation />

                </main>

            </div>

            <DocFooter />

        </div>

    </MainLayout>

</template>

<script setup>

import MainLayout from "../../components/layout/MainLayout.vue";

import DocHeader from "../components/DocHeader.vue";
import DocFooter from "../components/DocFooter.vue";
import DocSidebar from "../components/DocSidebar.vue";
import DocBreadcrumb from "../components/DocBreadcrumb.vue";
import DocNavigation from "../components/DocNavigation.vue";

import "../styles/documentation.css";

</script>

<style scoped>

.documentation{

    max-width: 1750px;

    margin: 0 auto;

    padding: 30px;

}

</style>
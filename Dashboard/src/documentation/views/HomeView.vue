<template>

    <DocumentationLayout>

        <div class="mb-10">

            <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-10">

                <div class="text-4xl font-bold mb-3">

                    📚 BackupCenter Documentation

                </div>

                <div class="text-blue-100 text-lg">

                    Documentación oficial de BackupCenter Enterprise

                </div>

            </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">

            <DocCard
                v-for="item in navigation"
                :key="item.id"
            >

                <div class="flex items-center gap-5 mb-6">

                    <div
                        class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-3xl font-bold"
                    >

                        {{ item.title.charAt(0) }}

                    </div>

                    <div>

                        <div class="text-xl font-bold">

                            {{ item.title }}

                        </div>

                        <div class="text-gray-500 text-sm">

                            {{ item.path }}

                        </div>

                    </div>

                </div>

                <p class="text-gray-600 mb-8">

                    Documentación técnica del módulo
                    <strong>{{ item.title }}</strong>

                </p>

                <RouterLink
                    :to="item.path"
                    class="inline-flex items-center rounded-lg bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 transition"
                >

                    Abrir documentación

                </RouterLink>

            </DocCard>

        </div>

    </DocumentationLayout>

</template>

<script setup>

import DocumentationLayout from "../layouts/DocumentationLayout.vue";
import { DocCard } from "../components";
import navigation from "../constants/navigation";

</script>
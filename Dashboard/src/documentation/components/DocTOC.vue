<template>

    <aside class="rounded-2xl border border-gray-200 bg-white p-6 mb-10">

        <h3 class="font-bold text-lg mb-4">

            Contenido

        </h3>

        <ul class="space-y-3">

            <li
                v-for="item in items"
                :key="item.id"
            >

                <a
                    :href="'#' + item.id"
                    class="text-blue-600 hover:text-blue-800"
                >

                    {{ item.title }}

                </a>

            </li>

        </ul>

    </aside>

</template>

<script setup>

defineProps({

    items:{

        type:Array,

        default:()=>[]

    }

});

</script>
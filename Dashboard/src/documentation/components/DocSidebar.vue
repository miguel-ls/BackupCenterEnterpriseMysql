<template>

    <aside class="sticky top-24">

        <h3 class="text-lg font-bold mb-5">
            Documentación
        </h3>

        <nav>

            <ul class="space-y-2">

                <li
                    v-for="item in navigation"
                    :key="item.id"
                >

                    <RouterLink
                        :to="item.path"
                        class="block px-3 py-2 rounded-lg hover:bg-gray-100 transition"
                        active-class="bg-blue-50 text-blue-700 font-semibold"
                    >

                        {{ item.title }}

                    </RouterLink>

                </li>

            </ul>

        </nav>

    </aside>

</template>

<script setup>

import navigation from "../constants/navigation";

</script>
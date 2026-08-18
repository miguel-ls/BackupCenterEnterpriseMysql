<template>

    <section
        class="rounded-3xl overflow-hidden bg-gradient-to-r from-slate-900 via-blue-900 to-indigo-900 text-white p-12 mb-10"
    >

        <div class="uppercase tracking-widest text-blue-300 text-sm mb-3">

            {{ category }}

        </div>

        <h1 class="text-5xl font-bold mb-5">

            {{ title }}

        </h1>

        <p class="text-blue-100 text-xl max-w-4xl leading-9">

            {{ description }}

        </p>

        <div
            class="flex gap-6 mt-10 text-sm text-blue-200"
        >

            <div>

                📄 {{ version }}

            </div>

            <div>

                ⏱ {{ reading }}

            </div>

            <div>

                📅 {{ updated }}

            </div>

        </div>

    </section>

</template>

<script setup>

defineProps({

    category:{
        type:String,
        default:"Documentation"
    },

    title:{
        type:String,
        default:""
    },

    description:{
        type:String,
        default:""
    },

    version:{
        type:String,
        default:"BC-071"
    },

    reading:{
        type:String,
        default:"5 min"
    },

    updated:{
        type:String,
        default:"Agosto 2026"
    }

})

</script>
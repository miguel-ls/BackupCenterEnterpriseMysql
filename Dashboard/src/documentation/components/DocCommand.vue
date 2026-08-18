<template>

    <div class="relative my-8">

        <button
            class="absolute top-4 right-4 text-xs bg-slate-800 text-white px-3 py-1 rounded hover:bg-slate-700"
            @click="copy"
        >
            Copiar
        </button>

        <pre class="bg-slate-900 text-slate-100 rounded-2xl p-6 overflow-x-auto"><code><slot /></code></pre>

    </div>

</template>

<script setup>

const copy = async () => {

    const text = document.querySelector("pre code")?.innerText ?? "";

    await navigator.clipboard.writeText(text);

};

</script>
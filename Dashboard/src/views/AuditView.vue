<template>

<MainLayout>

<div class="flex flex-wrap justify-between items-center gap-3 mb-8">

<h1 class="text-3xl font-bold">Auditoría</h1>



</div>

<div class="mb-6 flex flex-wrap gap-3 bg-white rounded-xl border border-neutral-200 p-4 shadow-sm">

<input
    v-model="search"
    placeholder="Buscar..."
    class="border rounded-lg px-3 py-2 w-72"
    @input="applyFilters"
/>

<input
    v-model="filters.from"
    type="date"
    class="border rounded-lg px-3 py-2"
    @change="applyFilters"
/>

<input
    v-model="filters.to"
    type="date"
    class="border rounded-lg px-3 py-2"
    @change="applyFilters"
/>

<select
    v-model="filters.module"
    class="border rounded-lg px-3 py-2"
    @change="applyFilters"
>
    <option value="">Todos los módulos</option>
    <option v-for="module in modules" :key="module" :value="module">{{ module }}</option>
</select>

<button @click="resetFilters" class="border rounded-lg px-4 py-2">Limpiar</button>

</div>

<div class="bg-white rounded-xl border shadow-sm overflow-hidden">

<table class="w-full">

<thead class="bg-neutral-100">

<tr>
<th class="p-3 text-left">Fecha</th>
<th class="p-3 text-left">Usuario</th>
<th class="p-3 text-left">Módulo</th>
<th class="p-3 text-left">Acción</th>
<th class="p-3 text-left">Descripción</th>
<th class="p-3 text-left">IP</th>
<th class="p-3 text-left">Host</th>
<th class="p-3 text-center">Estado</th>
</tr>

</thead>

<tbody>

<tr v-for="item in filtered" :key="item.id" class="border-t hover:bg-neutral-50">
<td class="p-3">{{ item.created_at }}</td>
<td class="p-3">{{ item.username }}</td>
<td class="p-3"><span class="font-semibold">{{ item.module }}</span></td>
<td class="p-3">{{ item.action }}</td>
<td class="p-3">{{ item.description }}</td>
<td class="p-3">{{ item.ip }}</td>
<td class="p-3">{{ item.hostname }}</td>
<td class="p-3 text-center">
<span class="px-3 py-1 rounded-full text-xs font-semibold" :class="item.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
{{ item.success ? 'OK' : 'ERROR' }}
</span>
</td>
</tr>

<tr v-if="filtered.length === 0">
<td colspan="8" class="text-center p-8 text-neutral-500">No existen registros.</td>
</tr>

</tbody>

</table>

</div>

<div class="flex justify-between mt-6">
<button @click="prev" :disabled="page === 1" class="border px-5 py-2 rounded-lg">Anterior</button>
<div>Página {{ page }}</div>
<button @click="next" class="border px-5 py-2 rounded-lg">Siguiente</button>
</div>

</MainLayout>

</template>

<script setup>
import { ref, computed, onMounted } from "vue"
import MainLayout from "../components/layout/MainLayout.vue"
import { getAudit } from "../api/client"
import { token } from "../api/auth"

const audit = ref([])
const modules = ref([])
const page = ref(1)
const search = ref("")
const filters = ref({
    user: "",
    module: "",
    from: "",
    to: "",
    success: ""
})

async function load() {
    const response = await getAudit(page.value, 50, {
        user: filters.value.user,
        module: filters.value.module,
        from: filters.value.from,
        to: filters.value.to,
        success: filters.value.success
    })

    audit.value = response?.data?.items ?? []
    modules.value = response?.data?.filters?.modules ?? []
}

function applyFilters() {
    page.value = 1
    load()
}

function resetFilters() {
    search.value = ""
    filters.value = {
        user: "",
        module: "",
        from: "",
        to: "",
        success: ""
    }
    page.value = 1
    load()
}

const filtered = computed(() => {
    const term = search.value.toLowerCase()

    return audit.value.filter((x) => {
        return [
            x.username,
            x.module,
            x.action,
            x.description
        ].some((value) => (value ?? "").toLowerCase().includes(term))
    })
})

function next() {
    page.value++
    load()
}

function prev() {
    if (page.value > 1) {
        page.value--
        load()
    }
}

async function clearAudit() {
    if (!confirm("¿Eliminar auditoría?")) {
        return
    }

    await fetch(`${window.location.origin}/api/audit.php`, {
        method: "DELETE",
        headers: {
            Authorization: `Bearer ${token()}`
        }
    })
    load()
}

onMounted(load)
</script>
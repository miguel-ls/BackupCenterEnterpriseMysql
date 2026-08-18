<template>

<div class="bg-white rounded-xl border shadow-sm overflow-hidden">

<table class="w-full">

<thead class="bg-neutral-100">

<tr>

<th class="p-3 text-left">Usuario</th>

<th class="p-3 text-left">Nombre</th>

<th class="p-3 text-left">Rol</th>

<th class="p-3 text-center">Estado</th>

<th class="p-3 text-center">2FA</th>

<th class="p-3 text-left">Último acceso</th>

<th class="p-3 text-center">Acciones</th>

</tr>

</thead>

<tbody>

<tr
v-for="user in users"
:key="user.id"
class="border-t hover:bg-neutral-50"
>

<td class="p-3 font-medium">

{{ user.username }}

</td>

<td class="p-3">

{{ user.fullname }}

</td>

<td class="p-3">

<span
class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs"
>

{{ user.role }}

</span>

</td>

<td class="p-3 text-center">

<span
:class="user.enabled ? 'text-green-600':'text-red-600'"
>

{{ user.enabled ? 'Activo' : 'Inactivo' }}

</span>

</td>

<td class="p-3 text-center">

<span
v-if="user.twofactor_enabled"
class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs"
>

Activado

</span>

<span
v-else
class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs"
>

Desactivado

</span>

</td>

<td class="p-3">

{{ user.last_login ?? "-" }}

</td>

<td class="p-3">

<div class="flex justify-center gap-2">

<button
@click="$emit('edit',user)"
class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700"
>

<Pencil :size="17"/>

</button>

<button
@click="$emit('twofa',user)"
class="px-3 py-1 rounded bg-amber-500 text-white hover:bg-amber-600"
>

2FA

</button>

<button
@click="$emit('delete',user)"
class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700"
>

<Trash2 :size="17"/>

</button>

</div>

</td>

</tr>

<tr v-if="users.length===0">

<td
colspan="7"
class="text-center p-10 text-neutral-500"
>

No existen usuarios.

</td>

</tr>

</tbody>

</table>

</div>

</template>

<script setup>

import {

    Play,
    Pencil,
    Trash2

} from "lucide-vue-next";

defineProps({

users:{

type:Array,

default:()=>[]

}

})

defineEmits([

"edit",

"delete",

"twofa"

])

</script>
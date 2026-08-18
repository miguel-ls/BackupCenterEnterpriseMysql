<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl shadow-xl w-[850px] p-8">

<h2 class="text-2xl font-bold mb-6">

Sesiones Activas

</h2>

<table class="w-full">

<thead class="bg-neutral-100">

<tr>

<th class="p-3 text-left">

Dispositivo

</th>

<th class="p-3 text-left">

IP

</th>

<th class="p-3 text-left">

Última actividad

</th>

<th class="p-3 text-center">

Estado

</th>

<th class="p-3 text-center">

Acción

</th>

</tr>

</thead>

<tbody>

<tr
v-for="item in sessions"
:key="item.id"
class="border-t"
>

<td class="p-3">

{{item.device}}

</td>

<td class="p-3">

{{item.ip}}

</td>

<td class="p-3">

{{item.last_activity}}

</td>

<td class="p-3 text-center">

<span
:class="item.active?'text-green-600':'text-red-600'"
>

{{item.active?'Activa':'Cerrada'}}

</span>

</td>

<td class="p-3 text-center">

<button

v-if="item.active"

@click="closeSession(item.id)"

class="text-red-600 hover:underline"

>

Cerrar

</button>

</td>

</tr>

<tr
v-if="sessions.length==0"
>

<td
colspan="5"
class="p-8 text-center text-neutral-500"
>

No existen sesiones.

</td>

</tr>

</tbody>

</table>

<div class="flex justify-end mt-8">

<button

@click="$emit('update:modelValue',false)"

class="border rounded-lg px-5 py-2"

>

Cerrar

</button>

</div>

</div>

</div>

</template>

<script setup>

import {

ref,

watch

} from "vue"

import API from "@/config/api";
import { token } from "@/api/auth";

const props=defineProps({

modelValue:Boolean,

user:Object

})

const emit=defineEmits([

"update:modelValue"

])

const sessions=ref([])

watch(

()=>props.modelValue,

(v)=>{

if(v){

load()

}

}

)

async function load(){

    

const r=await fetch(

`${API}/sessions.php?user_id=${props.user.id}`,

{

headers:{

Authorization:`Bearer ${token()}`

}

}

)

const j=await r.json()

sessions.value=j.data??[]

}

async function closeSession(id){

if(!confirm("¿Cerrar esta sesión?")){

return

}

await fetch(

 `${API}/sessions.php`,

{

method:"DELETE",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify({

id

})

}

)

load()

}

</script>
<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl w-[520px] p-8">

<h2 class="text-2xl font-bold mb-6">

{{ editing ? "Editar Usuario" : "Nuevo Usuario" }}

</h2>

<div class="space-y-4">

<input
v-model="local.username"
:disabled="editing"
placeholder="Usuario"
class="w-full border rounded-lg p-3"
/>

<input
v-model="local.fullname"
placeholder="Nombre Completo"
class="w-full border rounded-lg p-3"
/>

<input
v-model="local.password"
type="password"
:placeholder="editing ? 'Nueva contraseña (opcional)' : 'Contraseña'"
class="w-full border rounded-lg p-3"
/>

<select
v-model="local.role"
class="w-full border rounded-lg p-3"
>

<option value="ADMIN">ADMIN</option>
<option value="OPERATOR">OPERATOR</option>
<option value="VIEWER">VIEWER</option>

</select>

<label class="flex items-center gap-3">

<input
type="checkbox"
v-model="local.enabled"
/>

<span>Usuario activo</span>

</label>

<label class="flex items-center gap-3">

<input
type="checkbox"
v-model="local.twofactor_enabled"
/>

<span>Habilitar autenticación en dos pasos (2FA)</span>

</label>

</div>

<div class="flex justify-end gap-3 mt-8">

<button
@click="$emit('update:modelValue',false)"
class="px-5 py-2 rounded-lg border"
>

Cancelar

</button>

<button
@click="save"
class="px-5 py-2 rounded-lg bg-blue-600 text-white"
>

{{ editing ? "Actualizar" : "Guardar" }}

</button>

</div>

</div>

</div>

</template>

<script setup>

import {ref,watch} from "vue"

const props=defineProps({

modelValue:Boolean,

editing:Boolean,

form:Object

})

const emit=defineEmits([

"update:modelValue",

"save"

])

const local=ref({})

watch(

()=>props.form,

v=>{

local.value={...v}

},

{

immediate:true,

deep:true

}

)

function save(){

emit(

"save",

local.value

)

}

</script>
<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl shadow-xl w-[650px] p-8">

<h2 class="text-2xl font-bold mb-8">

Mi Perfil

</h2>

<div class="flex gap-8">

<div class="w-40 flex flex-col items-center">

<div
class="w-28 h-28 rounded-full bg-blue-600 text-white text-4xl font-bold flex items-center justify-center"
>

{{initial}}

</div>

<div class="mt-4 text-sm text-neutral-500">

Avatar próximamente

</div>

</div>

<div class="flex-1 space-y-5">

<div>

<label class="block mb-2">

Usuario

</label>

<input

v-model="form.username"

disabled

class="w-full border rounded-lg p-3 bg-neutral-100"

/>

</div>

<div>

<label class="block mb-2">

Nombre completo

</label>

<input

v-model="form.fullname"

class="w-full border rounded-lg p-3"

/>

</div>

<div>

<label class="block mb-2">

Rol

</label>

<input

v-model="form.role"

disabled

class="w-full border rounded-lg p-3 bg-neutral-100"

/>

</div>

<div>

<label class="block mb-2">

Último acceso

</label>

<input

v-model="form.last_login"

disabled

class="w-full border rounded-lg p-3 bg-neutral-100"

/>

</div>

</div>

</div>

<div
v-if="message"
class="mt-6 bg-green-100 border border-green-300 text-green-700 rounded-lg p-3"
>

{{message}}

</div>

<div
v-if="error"
class="mt-6 bg-red-100 border border-red-300 text-red-700 rounded-lg p-3"
>

{{error}}

</div>

<div class="flex justify-end gap-3 mt-8">

<button

@click="close"

class="px-5 py-2 border rounded-lg"

>

Cerrar

</button>

<button

@click="save"

class="px-5 py-2 bg-blue-600 text-white rounded-lg"

>

Guardar

</button>

</div>

</div>

</div>

</template>

<script setup>

import {

ref,

watch,

computed

} from "vue"

import API from "@/config/api";
import { token } from "@/api/auth";

const props=defineProps({
 
modelValue:Boolean

})

const emit=defineEmits([

"update:modelValue"

])

const form=ref({})

const message=ref("")

const error=ref("")

const initial=computed(()=>{

return (form.value.fullname??"?")

.substring(0,1)

.toUpperCase()

})

watch(

()=>props.modelValue,

(v)=>{

if(v){

const user=JSON.parse(

localStorage.getItem("user")??"{}"

)

form.value={...user}

message.value=""

error.value=""

}

}

)

function close(){

emit(

"update:modelValue",

false

)

}

async function save(){



const r=await fetch(

`${API}/profile.php`,

{

method:"PUT",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify(form.value)

}

)

const j=await r.json()

if(!j.success){

error.value=j.message

return

}

message.value=j.message

localStorage.setItem(

"user",

JSON.stringify(form.value)

)

}

</script>
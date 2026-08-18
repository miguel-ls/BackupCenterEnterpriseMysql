<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl shadow-xl w-[520px] p-8">

<h2 class="text-2xl font-bold mb-6">

Cambiar contraseña

</h2>

<div class="space-y-5">

<div>

<label class="block mb-2">

Contraseña actual

</label>

<input

v-model="form.current_password"

type="password"

class="w-full border rounded-lg p-3"

/>

</div>

<div>

<label class="block mb-2">

Nueva contraseña

</label>

<input

v-model="form.new_password"

type="password"

class="w-full border rounded-lg p-3"

/>

</div>

<div>

<label class="block mb-2">

Confirmar contraseña

</label>

<input

v-model="form.confirm_password"

type="password"

class="w-full border rounded-lg p-3"

/>

</div>

<div
v-if="message"
class="bg-green-100 border border-green-300 text-green-700 rounded-lg p-3"
>

{{message}}

</div>

<div
v-if="error"
class="bg-red-100 border border-red-300 text-red-700 rounded-lg p-3"
>

{{error}}

</div>

</div>

<div class="flex justify-end gap-3 mt-8">

<button

@click="close"

class="px-5 py-2 rounded-lg border"

>

Cancelar

</button>

<button

@click="save"

class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white"

>

Actualizar

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

modelValue:Boolean

})

const emit=defineEmits([

"update:modelValue"

])

const form=ref({

current_password:"",

new_password:"",

confirm_password:""

})

const error=ref("")

const message=ref("")

watch(

()=>props.modelValue,

(v)=>{

if(v){

form.value={

current_password:"",

new_password:"",

confirm_password:""

}

error.value=""

message.value=""

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

error.value=""

message.value=""

const user=JSON.parse(

localStorage.getItem("user")??"{}"

)




const r=await fetch(

`${API}/change-password.php`,

{

method:"POST",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify({

username:user.username,

...form.value

})

}

)

const j=await r.json()

if(!j.success){

error.value=j.message

return

}

message.value=j.message

setTimeout(()=>{

close()

},1200)

}

</script>
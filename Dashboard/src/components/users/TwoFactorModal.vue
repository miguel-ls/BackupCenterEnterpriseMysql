<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl shadow-xl w-[650px] p-8">

<h2 class="text-2xl font-bold mb-6">

Autenticación en dos pasos

</h2>

<div
v-if="loading"
class="text-center py-12 text-lg"
>

Generando secreto...

</div>

<div
v-else
class="space-y-6"
>

<div class="bg-blue-50 border border-blue-200 rounded-xl p-6">

<h3 class="font-bold text-lg">

Configurar Google Authenticator

</h3>

<p class="mt-3 text-sm text-neutral-600">

Abra Google Authenticator y seleccione:

</p>

<div class="mt-2 font-semibold">

➜ Agregar cuenta

</div>

<div class="font-semibold">

➜ Introducir clave de configuración

</div>

</div>

<div>

<label class="font-semibold">

Nombre de la cuenta

</label>

<input

readonly

value="Backup Center Enterprise"

class="w-full border rounded-lg p-3 mt-2 bg-neutral-100"

/>

</div>

<div class="flex flex-col items-center gap-4 my-6">

    <img
        v-if="qr"
        :src="qr"
        class="w-56 h-56 border rounded-xl bg-white p-2 shadow"
    />

    <div class="text-sm text-gray-500">
        Escanee este código con Google Authenticator
    </div>

</div>


<div>

<label class="font-semibold">

Clave secreta

</label>

<div class="flex gap-3 mt-2">

<input

readonly

v-model="secret"

class="flex-1 border rounded-lg p-3 font-mono"

/>

<button

@click="copy"

class="bg-blue-600 text-white px-5 rounded-lg"

>

Copiar

</button>

</div>

</div>

<div>

<label class="font-semibold">

Código generado por Google Authenticator

</label>

<input

v-model="code"

maxlength="6"

placeholder="123456"

class="w-full border rounded-lg p-3 mt-2 text-center text-2xl tracking-[8px]"

/>

</div>

</div>

<div class="flex justify-end gap-3 mt-8">

<button

@click="$emit('update:modelValue',false)"

class="border rounded-lg px-5 py-2"

>

Cancelar

</button>

<button

@click="activate"

class="bg-green-600 hover:bg-green-700 text-white rounded-lg px-6 py-2"

>

Activar 2FA

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

"update:modelValue",

"activated"

])

const loading=ref(false)
const secret=ref("")
const qr = ref("")
const code=ref("")

watch(

()=>props.modelValue,

async(v)=>{

if(!v){

return

}

loading.value=true

const r=await fetch(

 `${API}/2fa-qr.php`,

{

method:"POST",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

credentials:"include",

body:JSON.stringify({

id:props.user.id

})

}

)

const j=await r.json()

loading.value=false

if(!j.success){

alert(j.message)

return

}

secret.value = j.data.secret
qr.value = j.data.qr

}

)



async function activate(){

const r=await fetch(

    `${API}/2fa-enable.php`,

{

method:"POST",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

credentials:"include",

body:JSON.stringify({

id:props.user.id,

code:code.value

})

}

)

const j=await r.json()

alert(j.message)

if(!j.success){

return

}

emit("activated")

emit("update:modelValue",false)

}

async function copy(){

await navigator.clipboard.writeText(

secret.value

)

alert("Secreto copiado.")

}

</script>
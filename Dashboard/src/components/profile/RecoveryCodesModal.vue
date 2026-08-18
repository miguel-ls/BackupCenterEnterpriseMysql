<template>

<div
v-if="modelValue"
class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
>

<div class="bg-white rounded-xl shadow-xl w-[760px] p-8">

<h2 class="text-2xl font-bold mb-3">

Recovery Codes

</h2>

<p class="text-neutral-500 mb-6">

Guarde estos códigos. Cada uno solo podrá utilizarse una vez.

</p>

<div class="grid grid-cols-2 gap-3 mb-8">

<div
v-for="code in codes"
:key="code"
class="border rounded-lg p-3 text-center font-mono bg-neutral-50"
>

{{code}}

</div>

</div>

<div class="flex justify-between">

<div class="flex gap-3">

<button

@click="generate"

class="bg-blue-600 text-white rounded-lg px-5 py-2"

>

Regenerar

</button>

<button

@click="download"

class="bg-green-600 text-white rounded-lg px-5 py-2"

>

Descargar TXT

</button>

</div>

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

defineEmits([

"update:modelValue"

])

const codes=ref([])

watch(

()=>props.modelValue,

(v)=>{

if(v){

generate()

}

}

)

async function generate(){

const r=await fetch(


`${API}/recovery-codes.php`,

{

method:"POST",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify({

user_id:props.user.id

})

}

)

const j=await r.json()

codes.value=j.data??[]

}

async function download(){

const r=await fetch(



`${API}/recovery-codes-download.php`,

{

method:"POST",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify({

user_id:props.user.id

})

}

)

const text=await r.text()

const blob=new Blob(

[text],

{

type:"text/plain"

}

)

const url=URL.createObjectURL(blob)

const a=document.createElement("a")

a.href=url

a.download="RecoveryCodes.txt"

a.click()

URL.revokeObjectURL(url)

}

</script>
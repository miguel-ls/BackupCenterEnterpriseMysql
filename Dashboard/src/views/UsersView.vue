<template>

<MainLayout>

<div class="flex justify-between items-center mb-8">

<h1 class="text-3xl font-bold">

Usuarios

</h1>

<button
@click="newUser"
class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg"
>

Nuevo Usuario

</button>

</div>

<UserTable
:users="users"
@edit="editUser"
@delete="deleteUser"
@twofa="open2FA"
/>

<UserFormModal
v-model="showForm"
:editing="editing"
:form="form"
@save="saveUser"
/>

<TwoFactorModal
v-model="show2FA"
:user="selectedUser"
@activated="load"
/>

</MainLayout>

</template>

<script setup>

import {ref,onMounted} from "vue"

import MainLayout from "../components/layout/MainLayout.vue"

import UserTable from "../components/users/UserTable.vue"

import UserFormModal from "../components/users/UserFormModal.vue"

import TwoFactorModal from "../components/users/TwoFactorModal.vue"

import API from "@/config/api";
import { token } from "@/api/auth";

const users=ref([])

const showForm=ref(false)

const show2FA=ref(false)

const editing=ref(false)

const selectedUser=ref(null)

const form=ref({})

function resetForm(){

form.value={

id:null,

username:"",

fullname:"",

password:"",

role:"VIEWER",

enabled:true,

twofactor_enabled:false

}

}

async function load(){

const r=await fetch(

`${API}/users.php`,

{

headers:{

Authorization:`Bearer ${token()}`

}

}

)

const j=await r.json()

users.value=j.data??[]

}

function newUser(){

editing.value=false

resetForm()

showForm.value=true

}

function editUser(user){

editing.value=true

form.value={

...user,

password:""

}

showForm.value=true

}

async function saveUser(data){

const method=editing.value?"PUT":"POST"

const r=await fetch(

`${API}/users.php`,

{

method,

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify(data)

}

)

const j=await r.json()

alert(j.message)

if(j.success){

showForm.value=false

load()

}

}

async function deleteUser(user){

if(user.id===1){

alert("No puede eliminar el administrador.")

return

}

if(!confirm("¿Eliminar usuario?")){

return

}

const r=await fetch(

`${API}/users.php`,

{

method:"DELETE",

headers:{

"Content-Type":"application/json",

Authorization:`Bearer ${token()}`

},

body:JSON.stringify({

id:user.id

})

}

)

const j=await r.json()

alert(j.message)

load()

}

function open2FA(user){

selectedUser.value=user

show2FA.value=true

}

onMounted(load)

</script>
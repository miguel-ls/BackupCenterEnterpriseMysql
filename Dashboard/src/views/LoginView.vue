<template>

<div class="min-h-screen bg-slate-100 flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-10">

        <h1 class="text-3xl font-bold text-center mb-2">

            Backup Center Enterprise

        </h1>

<div class="flex justify-center mb-4">
    <div class="w-16 h-16 rounded-xl overflow-hidden bg-white flex items-center justify-center">
        <img 
            src="/logo.jpg" 
            alt="Logo"
            class="w-full h-full object-contain"
        />
    </div>
</div>      

        <p class="text-center text-neutral-500 mb-8">

            Iniciar sesión

        </p>

        <form
            @submit.prevent="login"
            class="space-y-5"
        >

            <div v-if="!requires2FA">

                <div>

                    <label class="block mb-2">

                        Usuario

                    </label>

                    <input
                        v-model="username"
                        class="w-full border rounded-lg p-3"
                        autocomplete="username"
                    >

                </div>

                <div class="mt-4">

                    <label class="block mb-2">

                        Contraseña

                    </label>

                    <input
                        v-model="password"
                        type="password"
                        class="w-full border rounded-lg p-3"
                        autocomplete="current-password"
                    >

                </div>

            </div>

            <div v-else>

                <div class="text-center">

                    <div class="text-lg font-semibold">

                        Verificación en dos pasos

                    </div>

                    <div class="text-sm text-neutral-500 mt-2">

                        Abra Google Authenticator e ingrese el código de 6 dígitos.

                    </div>

                </div>

                <input
                    v-model="code"
                    maxlength="6"
                    placeholder="123456"
                    class="w-full border rounded-lg p-3 text-center text-2xl tracking-[8px] mt-5"
                >

            </div>

            <div
                v-if="error"
                class="bg-red-100 border border-red-300 text-red-700 rounded-lg p-3"
            >

                {{ error }}

            </div>

            <button
                class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg p-3 font-semibold"
            >

                {{ requires2FA ? 'Verificar código' : 'Iniciar sesión' }}

            </button>

        </form>

    </div>

</div>

</template>

<script setup>

import { ref } from "vue"
import { useRouter } from "vue-router"

const router = useRouter()

import API from "@/config/api";

const username = ref("")
const password = ref("")
const code = ref("")
const error = ref("")

const requires2FA = ref(false)
const challenge = ref("")

async function login(){

    error.value=""

    if(!requires2FA.value){

        const response = await fetch(

            `${API}/login.php`,

            {

                method:"POST",

                headers:{

                    "Content-Type":"application/json"

                },

                credentials:"include",

                body:JSON.stringify({

                    username:username.value,

                    password:password.value

                })

            }

        )

        const json = await response.json()

        if(!json.success){

            error.value=json.message

            return

        }

        if(json.data.requires2FA){

            requires2FA.value=true

            challenge.value=json.data.challenge

            return

        }

        localStorage.setItem(

            "user",

            JSON.stringify(json.data)

        )

        localStorage.setItem(

            "token",

            json.data.token

        )

        router.push("/")

        return

    }

    const response = await fetch(

        `${API}/2fa-verify.php`,

        {

            method:"POST",

            headers:{

                "Content-Type":"application/json"

            },

            credentials:"include",

            body:JSON.stringify({

                challenge:challenge.value,

                code:code.value

            })

        }

    )

    const json = await response.json()

    if(!json.success){

        error.value=json.message

        return

    }

    localStorage.setItem(

        "user",

        JSON.stringify(json.data)

    )

    if(json.data.token){

        localStorage.setItem(

            "token",

            json.data.token

        )

    }

    router.push("/")

}

</script>
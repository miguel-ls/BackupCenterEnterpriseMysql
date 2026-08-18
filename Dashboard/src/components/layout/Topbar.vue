<template>

<header class="h-16 bg-white border-b border-neutral-200 flex items-center justify-between px-8 relative">

    <div>

        <h1 class="text-xl font-semibold">
            

            Backup Center 

        </h1>

                    <div class="text-xs ">

                        Enterprise Edition

                    </div>        

    </div>

    <div class="flex items-center gap-6">

        <!-- NOTIFICACIONES -->

        <div class="relative">

            <button
                @click="showNotifications=!showNotifications"
                class="relative text-2xl hover:scale-110 transition"
            >

                🔔

                <span
                    v-if="unread>0"
                    class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full min-w-[20px] h-5 px-1 flex items-center justify-center text-xs font-bold"
                >

                    {{ unread }}

                </span>

            </button>

            <div
                v-if="showNotifications"
                class="absolute right-0 mt-3 w-96 bg-white border rounded-xl shadow-xl z-50"
            >

                <div class="flex justify-between items-center p-4 border-b">

                    <h3 class="font-semibold">

                        Notificaciones

                    </h3>

                    <span>{{notifications.length}}</span>

                </div>

                <div class="max-h-96 overflow-y-auto">

                    <div
                        v-for="item in notifications.slice(0,5)"
                        :key="item.id"
                        class="p-4 border-b"
                    >

                        <div class="font-semibold">

                            {{item.title}}

                        </div>

                        <div class="text-sm text-neutral-500">

                            {{item.message}}

                        </div>

                    </div>

                </div>

                <div class="flex justify-between p-4 border-t">

                    <button
                        class="text-blue-600"
                        @click="markAll"
                    >

                        Marcar leídas

                    </button>

                    <button
                        class="text-red-600"
                        @click="clearAll"
                    >

                        Limpiar

                    </button>

                </div>

            </div>

        </div>

        <!-- USUARIO -->

        <div class="relative">

            <button

                @click="showMenu=!showMenu"

                class="flex items-center gap-3 hover:bg-neutral-100 rounded-xl px-3 py-2"

            >

                <div
                    class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold"
                >

                    {{initial}}

                </div>

                <div>

                    <div class="font-semibold">

                        {{user.fullname}}

                    </div>

                    <div class="text-xs text-neutral-500">

                        {{user.role}}

                    </div>

                </div>

                ▼

            </button>

            <div

                v-if="showMenu"

                class="absolute right-0 mt-2 w-64 bg-white border rounded-xl shadow-xl overflow-hidden z-50"

            >

                <button

                    @click="openProfile"

                    class="w-full text-left px-5 py-3 hover:bg-neutral-100"

                >

                    👤 Mi Perfil

                </button>

                <button

                    @click="openPassword"

                    class="w-full text-left px-5 py-3 hover:bg-neutral-100"

                >

                    🔒 Cambiar contraseña

                </button>

                <button

                    @click="openRecovery"

                    class="w-full text-left px-5 py-3 hover:bg-neutral-100"

                >

                    🔑 Recovery Codes

                </button>

                <button

                    @click="openSessions"

                    class="w-full text-left px-5 py-3 hover:bg-neutral-100"

                >

                    🖥 Sesiones

                </button>

                <hr>

                <button

                    @click="logout"

                    class="w-full text-left px-5 py-3 text-red-600 hover:bg-red-50"

                >

                    🚪 Cerrar sesión

                </button>

            </div>

        </div>

    </div>

</header>

<ProfileModal
v-model="showProfile"
/>

<ChangePasswordModal
v-model="showPassword"
/>

<RecoveryCodesModal
v-model="showRecovery"
:user="user"
/>

<SessionsModal
v-model="showSessions"
/>

</template>

<script setup>

import {

ref,

computed,

onMounted,

onUnmounted

} from "vue"

import {useRouter} from "vue-router"

import {

getNotifications,

markNotificationsAsRead,

clearNotifications

} from "@/api/client"

import ProfileModal from "../profile/ProfileModal.vue"

import ChangePasswordModal from "../profile/ChangePasswordModal.vue"

import RecoveryCodesModal from "../profile/RecoveryCodesModal.vue"

import SessionsModal from "../profile/SessionsModal.vue"

const router=useRouter()

const showMenu=ref(false)

const showNotifications=ref(false)

const showPassword=ref(false)

const showProfile=ref(false)

const showRecovery=ref(false)

const showSessions=ref(false)

const unread=ref(0)

const notifications=ref([])

const user=JSON.parse(

localStorage.getItem("user")??"{}"

)

const initial=computed(()=>

(user.fullname??"?")

.substring(0,1)

.toUpperCase()

)

let timer=null

function openProfile(){

showMenu.value=false

showProfile.value=true

}

function openPassword(){

showMenu.value=false

showPassword.value=true

}

function openRecovery(){

showMenu.value=false

showRecovery.value=true

}

function openSessions(){

showMenu.value=false

showSessions.value=true

}

function logout(){

localStorage.removeItem("user")

router.push("/login")

}

async function load(){

const r=await getNotifications()

notifications.value=r.data

unread.value=r.unread

}

async function markAll(){

await markNotificationsAsRead()

load()

}

async function clearAll(){

await clearNotifications()

load()

}

onMounted(()=>{

load()

timer=setInterval(load,20000)

})

onUnmounted(()=>{

clearInterval(timer)

})

</script>
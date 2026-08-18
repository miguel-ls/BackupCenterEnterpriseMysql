<template>

<aside :class="['bg-slate-900 text-white flex flex-col shadow-2xl transition-all duration-300 overflow-hidden', isCollapsed ? 'w-20' : 'w-72']">

    <div class="border-b border-slate-700 p-3">

        <div class="flex items-center justify-between gap-2">

            <div :class="['flex items-center gap-3 overflow-hidden', isCollapsed ? 'justify-center w-full' : '']">

                <div v-if="!isCollapsed" class="w-11 h-11 rounded-xl overflow-hidden bg-white flex items-center justify-center flex-shrink-0">
                    <img src="/logo.jpg" />
                </div>

                <!-- <div v-if="!isCollapsed" class="min-w-0">

                    <h1 class="text-xl font-bold leading-tight">

                        Backup Center

                    </h1>

                    <div class="text-xs text-slate-400">

                        Enterprise Edition

                    </div>

                </div> -->

            </div>

            <button
                type="button"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-700 bg-slate-800 text-slate-200 transition hover:bg-slate-700"
                @click="toggleSidebar"
                aria-label="Alternar menú"
            >
                <span class="flex flex-col items-center justify-center gap-1.5">
                    <span :class="['block h-0.5 w-5 rounded-full bg-white transition-all duration-200', isCollapsed ? 'rotate-45 translate-y-2' : '']"></span>
                    <span :class="['block h-0.5 w-5 rounded-full bg-white transition-all duration-200', isCollapsed ? 'opacity-0' : '']"></span>
                    <span :class="['block h-0.5 w-5 rounded-full bg-white transition-all duration-200', isCollapsed ? '-rotate-45 -translate-y-2' : '']"></span>
                </span>
            </button>

        </div>

    </div>

    <div v-if="!isCollapsed" class="flex flex-1 flex-col">

        <!-- Estado

        <div class="border-b border-slate-700 p-5">

            <div class="flex justify-between items-center">

                <span class="text-sm text-slate-300">

                    Estado

                </span>

                <span class="bg-green-600 px-3 py-1 rounded-full text-xs font-semibold">

                    ONLINE

                </span>

            </div>

        </div> -->

        <!-- Menú -->

        <nav class="flex-1 px-4 py-5 overflow-y-auto">

            <button
                type="button"
                class="title flex w-full items-center justify-between"
                :aria-expanded="expandedGroups.general"
                @click="toggleGroup('general')"
            >
                <span>GENERAL</span>
                <span class="group-chevron" :class="{ 'group-chevron-open': expandedGroups.general }">⌄</span>
            </button>

            <div v-show="expandedGroups.general">

            <RouterLink to="/" class="menu">

                <span>📊</span>

                <span>Dashboard</span>

            </RouterLink>

            <RouterLink to="/clients" class="menu">

                <span>🏢</span>

                <span>Clientes</span>

            </RouterLink>

            <RouterLink to="/connections" class="menu">

                <span>🌐</span>

                <span>Conexiones</span>

            </RouterLink>            

            <RouterLink to="/jobs" class="menu">

                <span>💼</span>

                <span>Trabajos</span>

            </RouterLink>

            
            <RouterLink to="/queue" class="menu">

                <span>📋</span>

                <span>Cola</span>

            </RouterLink>

            <RouterLink to="/transfers" class="menu">

                <span>📤</span>

                <span>Transferencias</span>

            </RouterLink>

            <RouterLink to="/history" class="menu">

                <span>🕘</span>

                <span>Historial</span>

            </RouterLink>

            <RouterLink to="/reports" class="menu">

                <span>📈</span>

                <span>Reportes</span>

            </RouterLink>

            <RouterLink to="/graphics" class="menu">

                <span>📊</span>

                <span>Graficos</span>

            </RouterLink>

            </div>

            <button
                type="button"
                class="title mt-8 flex w-full items-center justify-between"
                :aria-expanded="expandedGroups.security"
                @click="toggleGroup('security')"
            >
                <span>SEGURIDAD</span>
                <span class="group-chevron" :class="{ 'group-chevron-open': expandedGroups.security }">⌄</span>
            </button>

            <div v-show="expandedGroups.security">

            <RouterLink to="/users" class="menu">

                <span>👤</span>

                <span>Usuarios</span>

            </RouterLink>

            <RouterLink to="/audit" class="menu">

                <span>🛡</span>

                <span>Auditoría</span>

            </RouterLink>

            <RouterLink to="/logs" class="menu">

                <span>📄</span>

                <span>Logs</span>

            </RouterLink>            

            </div>

            <button
                type="button"
                class="title mt-8 flex w-full items-center justify-between"
                :aria-expanded="expandedGroups.system"
                @click="toggleGroup('system')"
            >
                <span>SISTEMA</span>
                <span class="group-chevron" :class="{ 'group-chevron-open': expandedGroups.system }">⌄</span>
            </button>

            <div v-show="expandedGroups.system">

            <RouterLink to="/settings" class="menu">

                <span>⚙️</span>

                <span>Configuración</span>

            </RouterLink>

            <!-- <RouterLink to="/documentation" class="menu">

                <span>📚</span>

                <span>Documentación</span>

            </RouterLink> -->

            <RouterLink to="/about" class="menu">

                <span>ℹ️</span>

                <span>Acerca de</span>

            </RouterLink>

            </div>

        </nav>

        <!-- Pie -->

        <div class="border-t border-slate-700 p-5">

            <div class="font-semibold">

                Backup Center

            </div>

            <div class="text-xs text-slate-400 mt-1">

                Enterprise Edition

            </div>

            <div class="mt-4">



                <div class="flex justify-between text-xs">
                    <span>Fecha / Hora:</span>
                    <span class="text-yellow-400 font-mono">
                        {{ serverDate }} {{ serverTime }}
                    </span>
                </div>

                <div class="flex justify-between text-xs text-green-400">

                    <span>Versión</span>

                    <strong>1.0.0</strong>

                </div>

                <!-- <div class="flex justify-between text-xs mt-2">

                    <span>Estado</span>

                    <strong class="text-green-400">

                        Operativo

                    </strong>

                </div> -->

            </div>

        </div>

    </div>

</aside>

</template>

<script setup>

import { RouterLink } from "vue-router"
import { ref, onMounted } from 'vue'

const isCollapsed = ref(false)
const expandedGroups = ref({
    general: true,
    security: true,
    system: true
})
const serverTime = ref('--:--:--')
const serverDate = ref('--/--/----')

const groupsStorageKey = 'backup-center-sidebar-groups'

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value
}

function toggleGroup(group) {
    expandedGroups.value[group] = !expandedGroups.value[group]
    localStorage.setItem(groupsStorageKey, JSON.stringify(expandedGroups.value))
}

function loadExpandedGroups() {
    try {
        const savedGroups = JSON.parse(localStorage.getItem(groupsStorageKey))

        if (savedGroups && typeof savedGroups === 'object') {
            expandedGroups.value = {
                ...expandedGroups.value,
                general: savedGroups.general !== false,
                security: savedGroups.security !== false,
                system: savedGroups.system !== false
            }
        }
    } catch {
        expandedGroups.value = {
            general: true,
            security: true,
            system: true
        }
    }
}

function updateTime() {
  const now = new Date()

  serverTime.value = now.toLocaleTimeString()

  serverDate.value = now.toLocaleDateString('es-PE', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

onMounted(() => {
    loadExpandedGroups()
  updateTime()
  setInterval(updateTime, 1000)
})

</script>

<style scoped>

.title{

    font-size:11px;

    color:#94a3b8;

    font-weight:700;

    letter-spacing:2px;

    margin-bottom:12px;

    padding-left:12px;

    border:0;

    background:transparent;

    cursor:pointer;

    text-align:left;

}

.group-chevron{

    font-size:16px;

    line-height:1;

    transform:rotate(-90deg);

    transition:transform .2s;

}

.group-chevron-open{

    transform:rotate(0deg);

}

.menu{

    display:flex;

    align-items:center;

    gap:12px;

    padding:14px 16px;

    margin-bottom:8px;

    border-radius:12px;

    transition:.2s;

    color:#e5e7eb;

}

.menu:hover{

    background:#1e293b;

    transform:translateX(5px);

}

.router-link-active{

    background:#2563eb;

    color:white;

    font-weight:700;

    box-shadow:0 0 15px rgba(37,99,235,.35);

}

.badge{

    margin-left:auto;

    background:#10b981;

    color:white;

    font-size:10px;

    padding:2px 8px;

    border-radius:9999px;

    font-weight:bold;

}

</style>
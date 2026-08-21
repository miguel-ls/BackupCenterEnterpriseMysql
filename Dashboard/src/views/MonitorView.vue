<template>

<MainLayout>

    <div class="flex justify-between items-center mb-4">

        <div>

            <h1 class="text-3xl font-bold">

                Monitor en Tiempo Real

            </h1>

            <p class="text-neutral-500 mt-2">

                Transferencias de archivos y cambios de estado de la cola, en vivo.

            </p>

        </div>

        <div class="flex items-center gap-3">

            <span
                class="w-2.5 h-2.5 rounded-full"
                :class="connected ? 'bg-green-500 animate-pulse' : 'bg-red-500'"
            ></span>

            <span class="text-sm text-neutral-500">

                {{ connected ? 'En vivo' : 'Sin conexión' }}

            </span>

            <input
                type="date"
                class="border rounded-lg px-3 py-2 text-sm"
                :max="todayStr"
                v-model="selectedDate"
                @change="onDateChange"
            />

            <button
                class="px-4 py-2 rounded-lg bg-neutral-700 text-white hover:bg-neutral-800"
                @click="openFiles"
            >

                Archivos

            </button>

            <button
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                @click="clearScreen"
            >

                Limpiar

            </button>

        </div>

    </div>

    <div
        ref="terminal"
        class="bg-black text-white font-mono text-sm rounded-xl p-4 h-[calc(100vh-13rem)] min-h-[300px] w-full max-w-full overflow-auto whitespace-pre shadow-inner"
    >

        <div
            v-for="(line, index) in lines"
            :key="index"
            v-html="colorize(line)"
        ></div>

        <div v-if="lines.length === 0" class="text-neutral-500">

            {{ isHistorical ? 'Sin registros para la fecha seleccionada.' : 'Esperando actividad...' }}

        </div>

    </div>

    <div
        v-if="showFiles"
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        @click.self="closeFiles"
    >

        <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[80vh] flex flex-col">

            <div class="flex justify-between items-center p-5 border-b">

                <h2 class="text-xl font-bold">

                    Archivos de Monitor (.log)

                </h2>

                <button
                    class="text-neutral-500 hover:text-neutral-800 text-2xl leading-none"
                    @click="closeFiles"
                >

                    &times;

                </button>

            </div>

            <div class="overflow-y-auto p-5 flex-1">

                <table class="w-full text-sm">

                    <thead>

                        <tr class="text-left text-neutral-500 border-b">

                            <th class="py-2">Archivo</th>
                            <th class="py-2">Tamaño</th>
                            <th class="py-2">Modificado</th>
                            <th class="py-2 text-right">Acciones</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr
                            v-for="item in files"
                            :key="item.name"
                            class="border-b hover:bg-neutral-50"
                        >

                            <td class="py-2 font-mono">{{ item.name }}</td>
                            <td class="py-2">{{ formatSize(item.size) }}</td>
                            <td class="py-2">{{ item.modified }}</td>

                            <td class="py-2 text-right">

                                <button
                                    class="px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 mr-2"
                                    @click="downloadMonitorFile(item.name)"
                                >

                                    Descargar

                                </button>

                                <button
                                    class="px-3 py-1.5 rounded-lg bg-red-600 text-white hover:bg-red-700"
                                    @click="confirmDelete(item.name)"
                                >

                                    Eliminar

                                </button>

                            </td>

                        </tr>

                        <tr v-if="files.length === 0">

                            <td colspan="4" class="text-center py-8 text-neutral-500">

                                No hay archivos de log.

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</MainLayout>

</template>

<script setup>

import {

    ref,
    nextTick,
    onMounted,
    onUnmounted

} from "vue";

import MainLayout from "../components/layout/MainLayout.vue";

import {
    getMonitor,
    getMonitorFiles,
    deleteMonitorFile,
    downloadMonitorFile
} from "../api/client";

const lines = ref([]);

const showFiles = ref(false);

const files = ref([]);

const connected = ref(false);

const terminal = ref(null);

const todayStr = new Date().toISOString().slice(0, 10);

const selectedDate = ref(todayStr);

const isHistorical = ref(false);

let offset = null;

let currentDate = null;

let timer = null;

const MAX_LINES = 1000;

const POLL_INTERVAL = 2000;

function colorize(line) {

    const escaped = line
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    if (line.includes("| TRANSFER")) {
        return `<span class="text-cyan-400">${escaped}</span>`;
    }

    if (line.includes("| WORKER")) {
        return `<span class="text-purple-400">${escaped}</span>`;
    }

    if (line.includes("Failed") || line.includes("fallida")) {
        return `<span class="text-red-400">${escaped}</span>`;
    }

    if (line.includes("Completed") || line.includes("completada")) {
        return `<span class="text-green-400">${escaped}</span>`;
    }

    if (line.includes("| QUEUE")) {
        return `<span class="text-yellow-300">${escaped}</span>`;
    }

    return escaped;

}

function clearScreen() {

    lines.value = [];

}

function formatSize(bytes) {

    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / 1024 / 1024).toFixed(2)} MB`;

}

async function loadFiles() {

    const response = await getMonitorFiles();

    files.value = response?.success ? response.data : [];

}

async function openFiles() {

    showFiles.value = true;

    await loadFiles();

}

function closeFiles() {

    showFiles.value = false;

}

async function confirmDelete(name) {

    if (!window.confirm(`¿Eliminar el archivo "${name}"? Esta acción no se puede deshacer.`)) {
        return;
    }

    const response = await deleteMonitorFile(name);

    if (response?.success) {
        await loadFiles();
    }

}

async function scrollToBottom() {

    await nextTick();

    if (terminal.value) {
        terminal.value.scrollTop = terminal.value.scrollHeight;
    }

}

function appendLines(newLines) {

    if (!newLines || newLines.length === 0) {
        return;
    }

    lines.value.push(...newLines);

    if (lines.value.length > MAX_LINES) {
        lines.value = lines.value.slice(-MAX_LINES);
    }

    scrollToBottom();

}

async function poll() {

    try {

        const response = await getMonitor(offset);

        connected.value = !!response?.success;

        if (!response?.success) {
            return;
        }

        // Cambio de día: reiniciar lectura desde el archivo nuevo.
        if (currentDate && response.date !== currentDate) {
            clearScreen();
            offset = null;
        }

        currentDate = response.date;

        appendLines(response.lines);

        offset = response.next_offset;

    } catch {

        connected.value = false;

    }

}

async function loadHistorical(date) {

    try {

        const response = await getMonitor(null, date);

        connected.value = !!response?.success;

        if (!response?.success) {
            return;
        }

        lines.value = response.lines || [];

        scrollToBottom();

    } catch {

        connected.value = false;

    }

}

function startLive() {

    clearScreen();

    offset = null;
    currentDate = null;

    poll();

    if (!timer) {
        timer = setInterval(poll, POLL_INTERVAL);
    }

}

function stopLive() {

    if (timer) {
        clearInterval(timer);
        timer = null;
    }

}

function onDateChange() {

    isHistorical.value = selectedDate.value !== todayStr;

    if (isHistorical.value) {
        stopLive();
        clearScreen();
        loadHistorical(selectedDate.value);
    } else {
        startLive();
    }

}

onMounted(() => {

    startLive();

});

onUnmounted(() => {

    stopLive();

});

</script>


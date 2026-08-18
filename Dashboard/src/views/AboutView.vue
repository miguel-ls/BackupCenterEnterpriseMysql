<template>
    <MainLayout>

        <div class="max-w-6xl">

            <h1 class="text-3xl font-bold mb-6">
                Acerca de
            </h1>

            <div class="bg-white rounded-xl border shadow-sm p-8">

                <div class="flex items-center gap-5 mb-8">

                    <div
                        class="w-16 h-16 rounded-xl bg-blue-600 text-white flex items-center justify-center text-3xl font-bold"
                    >
                <img 
                    src="/logo.jpg" 
                    alt="Logo"
                    class="w-full h-full object-contain"
                />
                    </div>

                    <div>

                        <h2 class="text-2xl font-bold">
                            Backup Center Enterprise
                        </h2>

                        <p class="text-neutral-500">
                            Plataforma centralizada para la administración,
                            monitoreo y automatización de respaldos empresariales.
                        </p>

                    </div>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <div>

                        <h3 class="text-lg font-semibold mb-4">
                            Información del Sistema
                        </h3>

                        <table class="w-full text-sm">

                            <tbody>

                                <tr class="border-b">
                                    <td class="py-2 font-semibold">Versión</td>
                                    <td>1.0.0</td>
                                </tr>

                                <tr class="border-b">
                                    <td class="py-2 font-semibold">Edición</td>
                                    <td>Enterprise</td>
                                </tr>

                                <tr class="border-b">
                                    <td class="py-2 font-semibold">Frontend</td>
                                    <td>Vue 3 + Vite</td>
                                </tr>

                                <tr class="border-b">
                                    <td class="py-2 font-semibold">Backend</td>
                                    <td>PHP 8.2</td>
                                </tr>

                                <tr class="border-b">
                                    <td class="py-2 font-semibold">Base de Datos</td>
                                    <td>SQLite</td>
                                </tr>

                                <tr>
                                    <td class="py-2 font-semibold">Estado</td>
                                    <td class="text-green-600 font-semibold">
                                        Operativo
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <div>

                        <h3 class="text-lg font-semibold mb-4">
                            Funcionalidades
                        </h3>

                        <ul class="space-y-2 text-sm">

                            <li>✔ Administración de Clientes</li>
                            <li>✔ Administración de Conexiones</li>
                            <li>✔ Administración de Trabajos</li>
                            <li>✔ Agente de Respaldo para Windows</li>
                            <li>✔ Monitoreo en Tiempo Real</li>
                            <li>✔ Historial de Ejecuciones</li>
                            <li>✔ Reportes y Exportación</li>
                            <li>✔ Auditoría del Sistema</li>
                            <li>✔ Autenticación en Dos Factores (2FA)</li>

                        </ul>

                    </div>

                </div>

                <hr class="my-8">

                <h3 class="text-lg font-semibold mb-4">
                    Descripción
                </h3>

                <p class="text-sm text-neutral-700 leading-7">
                    Backup Center Enterprise es una plataforma desarrollada para
                    centralizar la administración y supervisión de respaldos
                    empresariales desde una única consola web. Permite gestionar
                    clientes, conexiones, trabajos programados, historial de
                    ejecuciones, auditoría y reportes, proporcionando una visión
                    completa del estado de la infraestructura de respaldos.
                </p>

                <p class="text-sm text-neutral-700 leading-7 mt-4">
                    El sistema trabaja junto con un agente para Windows encargado
                    de detectar, validar y transferir automáticamente los archivos
                    de respaldo hacia el servidor central mediante conexiones
                    seguras.
                </p>

                <hr class="my-8">

                <div class="flex justify-between items-center text-sm text-neutral-500">

                    <div>
                        © 2026 Codesicorp
                    </div>

                    <div>
                        Backup Center Enterprise v1.0.0
                    </div>

                </div>

            </div>

        </div>

    </MainLayout>
</template>

<script setup>
import MainLayout from "../components/layout/MainLayout.vue";
</script>
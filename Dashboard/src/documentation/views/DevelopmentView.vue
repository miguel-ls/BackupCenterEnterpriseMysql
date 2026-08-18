<template>

    <DocumentationLayout>

        <DocTitle>

            🚀 Entorno de Desarrollo

        </DocTitle>

        <p class="text-lg text-gray-600 mb-10">

            Esta documentación describe el entorno oficial utilizado para el desarrollo
            de BackupCenter Enterprise y el flujo recomendado para implementar nuevas
            funcionalidades desde el equipo local hasta Producción.

        </p>

        <!-- HERO -->

        <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white p-10 mb-10">

            <div class="text-3xl font-bold mb-3">

                Flujo oficial de desarrollo

            </div>

            <div class="text-blue-100">

                Todo cambio debe pasar por Local → GitHub → Code Server →
                Sandbox → Producción.

            </div>

        </div>

        <!-- ALERTA -->

        <DocAlert>

            <template #title>

                Entorno oficial

            </template>

            Toda modificación debe validarse primero en Sandbox antes de
            publicarse en Producción.

        </DocAlert>

        <!-- ARQUITECTURA -->

        <DocSection>

            <template #title>

                Arquitectura del entorno

            </template>

            <DocCard>

<pre>
 Laptop
    │
    ▼
 GitHub
    ▲
    │
Code Server
    │
    ▼
 Sandbox
    │
    ▼
Producción
</pre>

            </DocCard>

        </DocSection>

        <!-- DESCRIPCIÓN -->

        <DocSection>

            <template #title>

                Componentes

            </template>

            <DocTable>

                <thead>

                <tr>

                    <th>Componente</th>

                    <th>Descripción</th>

                </tr>

                </thead>

                <tbody>

                <tr>

                    <td>Laptop</td>

                    <td>Desarrollo inicial del código.</td>

                </tr>

                <tr>

                    <td>GitHub</td>

                    <td>Repositorio oficial del proyecto.</td>

                </tr>

                <tr>

                    <td>Code Server</td>

                    <td>Entorno Linux utilizado para compilar y validar.</td>

                </tr>

                <tr>

                    <td>Sandbox</td>

                    <td>Servidor de pruebas.</td>

                </tr>

                <tr>

                    <td>Producción</td>

                    <td>Ambiente utilizado por los clientes.</td>

                </tr>

                </tbody>

            </DocTable>

        </DocSection>

        <!-- FLUJO -->

        <DocSection>

            <template #title>

                Flujo de trabajo

            </template>

            <div class="grid md:grid-cols-5 gap-5">

                <DocCard>

                    <h3 class="font-bold text-lg mb-3">

                        1. Desarrollo Local

                    </h3>

                    <p>

                        El desarrollo inicia en el equipo del desarrollador.

                    </p>

                </DocCard>

                <DocCard>

                    <h3 class="font-bold text-lg mb-3">

                        2. GitHub

                    </h3>

                    <p>

                        Todo cambio debe registrarse mediante commits y ramas.

                    </p>

                </DocCard>

                <DocCard>

                    <h3 class="font-bold text-lg mb-3">

                        3. Code Server

                    </h3>

                    <p>

                        Compilación, revisión y validación del Dashboard.

                    </p>

                </DocCard>

                <DocCard>

                    <h3 class="font-bold text-lg mb-3">

                        4. Sandbox

                    </h3>

                    <p>

                        Ambiente de pruebas antes de Producción.

                    </p>

                </DocCard>

                <DocCard>

                    <h3 class="font-bold text-lg mb-3">

                        5. Producción

                    </h3>

                    <p>

                        Publicación oficial para clientes.

                    </p>

                </DocCard>

            </div>

        </DocSection>

        <!-- GIT FLOW -->

        <DocSection>

            <template #title>

                Git Flow

            </template>

            <DocCode>

git checkout main

git pull

git checkout -b feature/BC-071-documentation-center

git add .

git commit -m "BC-071"

git push

            </DocCode>

        </DocSection>

        <!-- SCRIPTS -->

        <DocSection>

            <template #title>

                Scripts oficiales

            </template>

            <DocTable>

                <thead>

                <tr>

                    <th>Script</th>

                    <th>Uso</th>

                </tr>

                </thead>

                <tbody>

                <tr>

                    <td>publish-dashboard.sh</td>

                    <td>Compila y publica el Dashboard.</td>

                </tr>

                <tr>

                    <td>docker compose up</td>

                    <td>Levanta el entorno.</td>

                </tr>

                <tr>

                    <td>docker compose down</td>

                    <td>Detiene el entorno.</td>

                </tr>

                </tbody>

            </DocTable>

        </DocSection>
        
        <!-- BUENAS PRÁCTICAS -->

        <DocSection>

            <template #title>

                Buenas prácticas

            </template>

            <div class="grid md:grid-cols-2 gap-6">

                <DocCard>

                    <h3 class="font-bold mb-3">

                        Antes de comenzar

                    </h3>

                    <ul class="list-disc ml-5 space-y-2">

                        <li>Actualizar la rama principal.</li>

                        <li>Crear una nueva rama feature.</li>

                        <li>Verificar que el proyecto compile.</li>

                        <li>Revisar cambios pendientes.</li>

                    </ul>

                </DocCard>

                <DocCard>

                    <h3 class="font-bold mb-3">

                        Antes del Push

                    </h3>

                    <ul class="list-disc ml-5 space-y-2">

                        <li>Validar el Dashboard.</li>

                        <li>Ejecutar publish-dashboard.sh.</li>

                        <li>Revisar consola del navegador.</li>

                        <li>Confirmar funcionamiento en Sandbox.</li>

                    </ul>

                </DocCard>

            </div>

        </DocSection>

        <!-- TROUBLESHOOTING -->

        <DocSection>

            <template #title>

                Troubleshooting

            </template>

            <DocTable>

                <thead>

                <tr>

                    <th>Problema</th>

                    <th>Solución</th>

                </tr>

                </thead>

                <tbody>

                <tr>

                    <td>No compila el Dashboard</td>

                    <td>Ejecutar nuevamente publish-dashboard.sh.</td>

                </tr>

                <tr>

                    <td>Error de permisos</td>

                    <td>Verificar propietario del workspace y permisos Docker.</td>

                </tr>

                <tr>

                    <td>API no responde</td>

                    <td>Revisar la variable VITE_API_URL y los servicios PHP.</td>

                </tr>

                <tr>

                    <td>Sandbox desactualizado</td>

                    <td>Publicar nuevamente la última versión.</td>

                </tr>

                </tbody>

            </DocTable>

        </DocSection>
        
        <!-- RESUMEN -->

        <DocSection>

            <template #title>

                Resumen

            </template>

            <p class="leading-8">

                Todo el ciclo de desarrollo de BackupCenter Enterprise sigue una
                arquitectura controlada que permite validar cada cambio antes de
                llegar a Producción. El uso de GitHub, Code Server y Sandbox
                garantiza un proceso seguro, repetible y trazable.

            </p>

        </DocSection>

        <!-- NAVEGACIÓN -->

        <DocNavigation>

            <RouterLink
                to="/documentation/architecture"
                class="text-blue-600 font-semibold"
            >
                ← Arquitectura
            </RouterLink>

            <RouterLink
                to="/documentation/docker"
                class="text-blue-600 font-semibold"
            >
                Docker →
            </RouterLink>

        </DocNavigation>

    </DocumentationLayout>

</template>

<script setup>

import { RouterLink } from "vue-router";

import DocumentationLayout from "../layouts/DocumentationLayout.vue";

import {
    DocAlert,
    DocCard,
    DocCode,
    DocNavigation,
    DocSection,
    DocTable,
    DocTitle
} from "../components";

</script>


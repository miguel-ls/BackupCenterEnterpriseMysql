# BackupCenter Enterprise
# Entorno de Desarrollo

## Arquitectura

El proyecto utiliza cuatro ambientes principales:

```
Laptop (Visual Studio Code)
        │
        ▼
     GitHub
        ▲
        │
Code Server (TrueNAS)
        │
        ▼
Sandbox (Docker)
        │
        ▼
Producción (Docker)
```

---

# Ambientes

## Laptop

Ubicación del proyecto:

```
D:\miguel.lopez\MyCloud\FUENTES\BackupCenterEnterprise
```

Se utiliza para:

- Desarrollo principal
- Nuevas funcionalidades
- Refactorización
- Pruebas locales
- Git

---

## Code Server

Workspace:

```
/config/workspace
```

Se utiliza para:

- Cambios rápidos
- Desarrollo remoto
- Validación en TrueNAS
- Sincronización con Sandbox

---

## Sandbox

Contenedor:

```
backupcenter-sandbox
```

Puerto:

```
8087
```

Características:

- Monta el código fuente desde el Host.

```
Host
↓

/mnt/Interno2TB/apps/backupcenter/source

↓

Container

/var/www/html
```

Por ello:

Los cambios PHP son inmediatos.

Los cambios Vue requieren compilación.

---

## Producción

Contenedor:

```
backupcenter
```

Puerto:

```
8085
```

Características:

No monta el código fuente.

El código queda incluido dentro de la imagen Docker.

Por ello, cualquier cambio requiere reconstruir la imagen.

---

# Flujo Git

La rama principal de desarrollo es:

```
feature/BC-070-scheduler
```

Como el upstream ya está configurado:

```
git push
git pull
```

son suficientes.

No es necesario utilizar:

```
git push -u origin feature/BC-070-scheduler
```

excepto la primera vez que se crea una rama.

---

# Desarrollo en Laptop

Modificar código

↓

Pruebas

↓

```
git add .
git commit -m "Descripción"
git push
```

↓

Code Server

```
git pull
```

↓

Si hubo cambios Vue:

```
./publish-dashboard.sh
```

↓

F5

---

# Desarrollo en Code Server

Modificar código

↓

Si es PHP

F5

↓

Si es Vue

Watcher recompila

↓

```
./publish-dashboard.sh
```

↓

F5

↓

```
git add .
git commit -m "Descripción"
git push
```

↓

Laptop

```
git pull
```

---

# Despliegue a Producción

Actualizar código

```
git pull
```

Reconstruir imagen

```
docker compose build
```

Actualizar contenedor

```
docker compose up -d
```

---

# Scripts

## update-sandbox.sh

Actualiza completamente el Sandbox.

Ejecuta:

- git pull
- composer install
- npm install
- npm run build
- Publicación Dashboard

Uso:

```
./update-sandbox.sh
```

---

## publish-dashboard.sh

Publica únicamente el Dashboard compilado.

No recompila.

Uso:

```
./publish-dashboard.sh
```

---

# Desarrollo Vue

Una sola vez por sesión:

```
cd /config/workspace/Dashboard

npm run build -- --watch
```

Dejar la terminal abierta.

Cada modificación:

Guardar

↓

Esperar compilación

↓

```
./publish-dashboard.sh
```

↓

F5

---

# ¿Cuándo reconstruir Docker?

## NO

- Cambios PHP
- Cambios Vue
- CSS
- JavaScript
- Configuración interna

## SI

- Dockerfile
- Extensiones PHP
- Librerías del sistema
- Imagen Base
- docker-compose.yml

# Decisiones de Arquitectura

## 2026-08-03

- Se agregó `publish-dashboard.sh`.
- El Sandbox utiliza `npm run build -- --watch`.
- Producción continúa compilando el Dashboard durante `docker compose build`.
- El contenedor Sandbox monta el código fuente.
- El contenedor Producción utiliza el código empaquetado dentro de la imagen.

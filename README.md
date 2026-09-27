# 💊 FarmaBien — Sistema de Gestión Farmacéutica y POS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/Docker-ready-2496ED?style=for-the-badge&logo=docker&logoColor=white"/>
  <img src="https://img.shields.io/badge/ISO%2FIEC_25010-Evaluado-4CAF50?style=for-the-badge"/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Calidad_Global-91.6%25-4CAF50?style=for-the-badge"/>
  <img src="https://img.shields.io/badge/Live_Demo-farmabien.onrender.com-blue?style=for-the-badge"/>
  <img src="https://img.shields.io/badge/Estado-Prototipo_v1.0.0-orange?style=for-the-badge"/>
</p>

> Sistema web farmacéutico y punto de venta (POS) desarrollado en **Laravel 10**, evaluado bajo **ISO/IEC 25010**, con índice global de calidad del **91.6%**.

---

## 🚀 Instalación Rápida (Windows + Docker)

> **Requisito único:** Tener [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo.

### Método 1 — Script Automático ⚡ (recomendado)

```bat
:: 1. Clona el repositorio
git clone https://github.com/AmsselFern10/FarmaBien.git
cd FarmaBien

:: 2. Ejecuta el script de instalación (doble clic o desde terminal)
setup.bat
```

El script `setup.bat` resuelve automáticamente:
- ✅ Detecta si el puerto 80 está ocupado → usa 8080
- ✅ Detecta si el puerto 3306 está ocupado (XAMPP) → usa 3307
- ✅ Crea el archivo `.env` con la configuración correcta para Docker
- ✅ Instala dependencias PHP sin necesidad de tener PHP instalado en Windows
- ✅ Espera a que MySQL esté listo antes de migrar
- ✅ Ejecuta migraciones, seeders y compila el frontend **dentro del contenedor**
- ✅ Te dice exactamente en qué URL abrir la app

---

### Método 2 — Manual paso a paso

<details>
<summary>Haz clic para expandir el proceso manual</summary>

#### Requisitos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y corriendo
- Git

#### Pasos

```bat
:: 1. Clonar
git clone https://github.com/AmsselFern10/FarmaBien.git
cd FarmaBien

:: 2. Crear .env (usa la plantilla preconfigurada para Docker)
copy .env.local.example .env

:: 3. Instalar dependencias PHP via contenedor temporal
::    (no necesitas PHP instalado en tu PC)
docker run --rm -v "%cd%:/app" -w /app composer:2 install --no-interaction --ignore-platform-reqs

:: 4. Levantar contenedores
docker compose up -d --build

:: 5. Esperar ~20 segundos a que MySQL inicie, luego:
docker compose exec laravel.test php artisan key:generate
docker compose exec laravel.test php artisan migrate:fresh --seed --force
docker compose exec laravel.test php artisan storage:link

:: 6. Compilar el frontend DENTRO del contenedor
::    (no ejecutes npm directamente en PowerShell — bloqueado por política de seguridad)
docker compose exec laravel.test npm run build
```

Acceder en: **http://localhost**

</details>

---

## 🔑 Credenciales de Prueba

| Rol | Email | Contraseña |
|---|---|---|
| **Administrador** | `admin@farmabien.com` | `password` |
| **Farmacéutico** | `farmaceutico@farmabien.com` | `password` |
| **Cajero** | `cajero@farmabien.com` | `password` |
| **Inventario** | `inventario@farmabien.com` | `password` |

> ⚠️ Cambiar las contraseñas antes de usar en producción.

---

## 🛠️ Comandos Útiles del Día a Día

```bat
:: Apagar los contenedores (conserva la base de datos)
docker compose down

:: Volver a encender
docker compose up -d

:: Ver logs en tiempo real si algo falla
docker compose logs -f laravel.test
docker compose logs -f mysql

:: Resetear toda la base de datos (borra y vuelve a sembrar)
docker compose exec laravel.test php artisan migrate:fresh --seed

:: Limpiar caché de Laravel
docker compose exec laravel.test php artisan optimize:clear

:: Abrir una terminal dentro del contenedor
docker compose exec laravel.test bash
```

---

## 🐛 Solución de Problemas Frecuentes

| Síntoma | Causa | Solución |
|---|---|---|
| `SQLSTATE[HY000] Connection refused` | `.env.example` original usa `DB_HOST=127.0.0.1` | Usa `.env.local.example` que tiene `DB_HOST=mysql` |
| Puerto 80 ocupado | IIS, XAMPP o proceso del Sistema | El `setup.bat` lo detecta y usa 8080 automáticamente |
| Puerto 3306 ocupado | XAMPP u otro MySQL local | El `setup.bat` lo detecta y usa 3307 automáticamente |
| `PSSecurityException` al ejecutar npm | Política de PowerShell bloquea scripts | Ejecuta npm **dentro del contenedor**: `docker compose exec laravel.test npm run build` |
| Contenedor no inicia, error de permisos | Storage creado como root | `docker compose exec laravel.test chmod -R 775 storage bootstrap/cache` |
| Blank page / error 500 | Falta `APP_KEY` o caché vieja | `docker compose exec laravel.test php artisan key:generate && php artisan optimize:clear` |
| `vendor/` no existe y Docker no inicia | Git no sube carpetas vacías | El `setup.bat` instala con contenedor temporal automáticamente |

---

## ✨ Características

| Módulo | Descripción |
|---|---|
| 🛒 **Punto de Venta (POS)** | Ventas con búsqueda reactiva, descuentos, múltiples métodos de pago y ticket |
| 📦 **Inventario FEFO** | Control de stock por lotes, política First Expired First Out, alertas |
| 🛍️ **Compras y Lotes** | Registro de compras a proveedores con control de lotes y vencimientos |
| 💊 **Recetas Médicas** | Retención digital de recetas, control de dispensación y saldo |
| 💰 **Cajas y Turnos** | Apertura/cierre de turno, fondo, arqueo y consolidación automática |
| 📊 **Reportes** | PDF (DomPDF) y Excel de ventas, compras, Kardex e inventario |
| 🤖 **Asistente IA** | Consulta farmacológica por síntoma o principio activo (Gemini/offline) |
| 👥 **RBAC + Seguridad** | Roles, permisos granulares, bloqueo por intentos fallidos, audit log |
| 🔒 **Imágenes Privadas** | Imágenes de productos protegidas — solo accesibles con sesión activa |

---

## 🏗️ Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2, Laravel 10, MySQL 8.0 |
| Frontend | Tailwind CSS 3, Alpine.js 3, Vite |
| Auth / RBAC | Laravel Breeze + Spatie Permission |
| DevOps local | Docker Compose + Laravel Sail |
| Cloud | Render (Docker) + Aiven MySQL |

---

## 📊 Calidad ISO/IEC 25010

| Característica | Índice | Estado |
|---|:---:|:---:|
| Adecuación Funcional | 0.936 | ✅ |
| Eficiencia de Desempeño | 1.000 | ✅ |
| Compatibilidad | 1.000 | ✅ |
| Usabilidad | 0.817 | ✅ |
| Fiabilidad | 0.928 | ✅ |
| Seguridad | 0.937 | ✅ |
| Mantenibilidad | 1.000 | ✅ |
| **Portabilidad** | **0.550** → mejorando con `setup.bat` | ⚠️ |
| **ÍNDICE GLOBAL** | **0.916** | ✅ **APROBADO** |

---

<p align="center">Desarrollado con ❤️ — FarmaBien v1.0.0 · Laravel 10 · ISO/IEC 25010</p>

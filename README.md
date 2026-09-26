# 💊 FarmaBien — Sistema de Gestión Farmacéutica y POS

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10-FF2D20?style=for-the-badge&logo=laravel&logoColor=white"/>
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white"/>
  <img src="https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white"/>
  <img src="https://img.shields.io/badge/Docker-ready-2496ED?style=for-the-badge&logo=docker&logoColor=white"/>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/ISO%2FIEC_25010-Evaluado-4CAF50?style=for-the-badge"/>
  <img src="https://img.shields.io/badge/Calidad_Global-92.6%25-4CAF50?style=for-the-badge"/>
  <img src="https://img.shields.io/badge/Estado-Prototipo_v1.0.0-blue?style=for-the-badge"/>
</p>

---

## ¿Qué es FarmaBien?

**FarmaBien** es un sistema web de gestión farmacéutica y punto de venta (POS) desarrollado en **Laravel 10**, diseñado para farmacias comunitarias y medianas que necesitan digitalizar sus operaciones de mostrador, inventario y control de recetas médicas.

Desarrollado como proyecto de tesis y evaluado bajo la norma **ISO/IEC 25010 e ISO/IEC 25023**, alcanzando un **índice global de calidad de 91.6%**.

---

## ✨ Características Principales

| Módulo | Descripción |
|---|---|
| 🛒 **Punto de Venta (POS)** | Ventas con búsqueda reactiva, descuentos, múltiples métodos de pago (efectivo, tarjeta, transferencia, mixto) y emisión de ticket |
| 📦 **Inventario y Kardex** | Control de stock por lotes con política **FEFO** (First Expired, First Out), alertas de vencimiento y ajustes de inventario |
| 🛍️ **Compras y Lotes** | Registro de compras a proveedores con control de lotes, presentaciones y fechas de vencimiento obligatorias |
| 💊 **Recetas Médicas** | Retención digital de recetas con imagen adjunta, control de dispensación y saldo de receta |
| 💰 **Cajas y Turnos** | Apertura y cierre de turno con fondo, arqueo de caja, ingreso/egreso manual y consolidación automática |
| 📊 **Reportes** | Exportación de reportes de ventas, compras, Kardex e inventario en PDF (DomPDF) y Excel |
| 🤖 **Asistente IA** | Integración con API de IA para consulta de información farmacológica por síntoma o principio activo |
| 👥 **RBAC y Seguridad** | Control de acceso basado en roles (Spatie Permission), auditoría de acciones críticas y bloqueo por intentos fallidos |
| 🏷️ **Catálogo Público** | Vista pública de productos disponibles sin necesidad de autenticación |

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP 8.2** + **Laravel 10** (MVC + Service Layer)
- **MySQL 8.0** (Aiven Cloud) / SQLite (local dev)
- **Spatie Laravel Permission** — RBAC con roles y permisos granulares
- **Laravel Sanctum** — autenticación de API
- **barryvdh/laravel-dompdf** — generación de PDFs
- **Guzzle HTTP** — cliente HTTP para integración IA

### Frontend
- **Tailwind CSS 3** — diseño responsive utility-first
- **Alpine.js 3** — interactividad declarativa (modales, toggles, carrito POS)
- **Vite** — bundler y hot reload en desarrollo

### DevOps
- **Docker + Docker Compose** — contenedores para despliegue reproducible
- **Render** — despliegue cloud (producción en `farmabien.onrender.com`)
- **Apache** (vía `apache.conf`) — servidor web en producción

---

## 🏗️ Arquitectura

```
farmaBien/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # 30 controladores (uno por módulo)
│   │   └── Requests/        # 27 FormRequests con validación en español
│   ├── Models/              # 23 modelos Eloquent
│   └── Services/            # 7 servicios desacoplados (VentaService, KardexService, etc.)
├── resources/
│   └── views/               # 126 vistas Blade con componentes reutilizables
├── database/
│   ├── migrations/          # Migraciones versionadas
│   └── seeders/             # 7 seeders para datos de prueba
├── routes/
│   └── web.php              # Rutas protegidas por middleware de rol
├── Dockerfile
├── docker-compose.yml
└── Evidencias_ISO25010_FarmaBien/   # Evaluación completa de calidad
```

---

## 🚀 Instalación y Configuración

### Requisitos Previos
- PHP 8.2+
- Composer 2+
- Node.js 18+ y npm
- MySQL 8.0+ (o SQLite para desarrollo rápido)

### Pasos de Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/tu-usuario/farmaBien.git
cd farmaBien

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JS
npm install

# 4. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar la base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=farmabien
# DB_USERNAME=root
# DB_PASSWORD=tu_password

# 6. Ejecutar migraciones y seeders
php artisan migrate:fresh --seed

# 7. Crear enlace de almacenamiento
php artisan storage:link

# 8. Compilar assets
npm run build

# 9. Iniciar servidor de desarrollo
php artisan serve
```

Acceder en: `http://127.0.0.1:8000`

### Con Docker

```bash
# Construir y levantar los contenedores
docker-compose up --build -d

# Ejecutar migraciones dentro del contenedor
docker exec -it farmabien-app php artisan migrate:fresh --seed
```

### Credenciales por Defecto (después del seeder)

| Rol | Email | Contraseña |
|---|---|---|
| Administrador | `admin@farmabien.com` | `password` |
| Farmacéutico | `farmaceutico@farmabien.com` | `password` |
| Cajero | `cajero@farmabien.com` | `password` |

> ⚠️ **Cambiar estas credenciales antes de usar en producción.**

---

## 🔬 Evaluación de Calidad ISO/IEC 25010

FarmaBien fue evaluado formalmente bajo la norma **ISO/IEC 25010** e **ISO/IEC 25023** con datos empíricos reales.

### Índice Global de Calidad: **91.6% — APROBADO**

| Característica | Índice | Peso AHP | Dictamen |
|---|:---:|:---:|:---:|
| Adecuación Funcional | 0.936 | 13.45% | ✅ APROBADO |
| Eficiencia de Desempeño | 1.000 | 6.01% | ✅ APROBADO |
| Compatibilidad | 1.000 | 2.91% | ✅ APROBADO |
| Usabilidad | 0.817 | 13.45% | ✅ APROBADO |
| Fiabilidad | 0.928 | 27.63% | ✅ APROBADO |
| Seguridad | 0.937 | 27.63% | ✅ APROBADO |
| Mantenibilidad | 1.000 | 6.01% | ✅ APROBADO |
| **Portabilidad** | **0.550** | 2.91% | ❌ NO APROBADO |
| **ÍNDICE GLOBAL** | **0.916** | **100%** | ✅ **APROBADO** |

> Los pesos fueron determinados mediante el **Proceso Analítico Jerárquico (AHP)** de Saaty con ratio de consistencia de 0.15% (< 10% — válido).

### Hallazgos Clave

**Fortalezas:**
- ✅ Eficiencia: tiempo de respuesta promedio 2.584 s — CPU 1.88%, RAM 1.56%
- ✅ Seguridad: RBAC completo, CSRF, XSS, SQLi protegido — 93.7%
- ✅ Mantenibilidad: MVC + servicios desacoplados — 100%
- ✅ Protección de errores de usuario: 27 FormRequests + 6/6 acciones con modal — 100%

**Áreas de Mejora (v1.1.0):**
- ⚠️ Portabilidad/Instalabilidad: proceso de instalación complejo (~3 h) — requiere simplificación con scripts automatizados
- ⚠️ Usabilidad/Aprendizaje: 3 tareas críticas con fricción en primer uso (Kardex, recetas, presentaciones)
- ⚠️ Usabilidad/Coherencia: 4 elementos de UI con comportamiento inconsistente entre módulos
- ⚠️ Seguridad/Autenticación: 2 reglas de contraseña faltantes (longitud mínima, restablecimiento por email)

### Documentación de Evidencias

```
Evidencias_ISO25010_FarmaBien/
├── 00_Ficha_y_Especificacion/   # Ficha técnica y AnexoC (Excel de indicadores)
├── 01_Adecuacion_Funcional/     # EV-ADF-01 (38 requisitos, 4 casos fallidos)
├── 02_Eficiencia_Desempeno/     # EV-EFD-01 (Artillery, telemetría CPU/RAM)
├── 03_Compatibilidad/           # EV-COM-01
├── 04_Usabilidad/               # EV-USA-01 (pruebas con 3 evaluadores externos)
├── 05_Fiabilidad/               # EV-FIA-01 (7 escenarios de fallo simulados)
├── 06_Seguridad/                # EV-SEG-01 (26 casos RBAC, CSRF, XSS, SQLi)
├── 07_Mantenibilidad/           # EV-MAN-01 (30 controladores, 7 servicios)
├── 08_Portabilidad/             # EV-POR-01 (10 dispositivos, 4 navegadores)
└── 09_AHP_y_Justificaciones/    # Matriz AHP y justificación de pesos
```

---

## ⚡ Comandos Artisan Personalizados

```bash
# Verificar completitud de requisitos funcionales
php artisan farma:verificar-completitud

# Benchmark de concurrencia (prueba de carga interna)
php artisan farma:benchmark-concurrencia

# Health check del sistema
GET /health

# Métricas de rendimiento (CPU, RAM, tiempo de respuesta)
GET /benchmark/metricas
```

---

## 🧪 Testing y Verificación

```bash
# Reiniciar entorno de prueba completo (< 4.5 s)
php artisan migrate:fresh --seed

# Listar todas las rutas con middlewares
php artisan route:list

# Monitoreo de base de datos
php artisan db:monitor
```

Los casos de prueba incluyen:
- **15 colecciones Postman** para endpoints de API
- **7 seeders** de datos de prueba con escenarios farmacéuticos reales
- **38 casos de prueba funcionales** (REQ-01 a REQ-38)
- **7 escenarios de tolerancia a fallos** (sobreventa, lote vencido, receta sin saldo, etc.)

---

## 📋 Variables de Entorno Requeridas

```env
APP_NAME=FarmaBien
APP_ENV=production
APP_KEY=          # Generada con php artisan key:generate
APP_URL=https://tu-dominio.com

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=farmabien
DB_USERNAME=
DB_PASSWORD=

# IA (opcional)
GEMINI_API_KEY=   # Para el asistente farmacológico

# Storage
FILESYSTEM_DISK=local
```

---

## 📄 Licencia

Este proyecto fue desarrollado como trabajo de tesis académica.

---

<p align="center">
  Desarrollado con ❤️ — FarmaBien v1.0.0 · Laravel 10 · ISO/IEC 25010
</p>

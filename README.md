# 💊 FarmaBien - Sistema de Gestión Farmacéutica Inteligente

**FarmaBien** es una solución integral de gestión de inventario y facturación en efectivo diseñada específicamente para farmacias pequeñas y locales. El sistema no solo organiza el flujo de caja y stock, sino que potencia la administración mediante el uso de **Inteligencia Artificial**


---

## ✨ Características Principales

* **📦 Control de Inventario:** Registro detallado de entradas, salidas y trazabilidad de productos.
* **💰 Facturación en Efectivo:** Proceso de venta ágil con generación e impresión de facturas.
* **📊 Dashboard & Reportes:** Visualización clara del estado del negocio y movimientos financieros.
* **🤖 Asistente IA Administrativo (Chat Interno):**
    * Consultas en lenguaje natural: *“¿Cuánto vendí hoy?”*, *“¿Qué productos están por agotarse?”*.
    * **Lupa Inteligente:** Al activar la lupa en un producto, la IA explica qué es y para qué sirve.
* **🔔 Notificaciones Inteligentes:** Alertas automáticas sobre stock bajo, vencimientos y hitos de ventas.
* **🛡️ Gestión de Accesos:** Control estricto mediante roles (Admin, Cajero, Inventario).

---

## 🛠️ Tecnologías Utilizadas

| Capa | Tecnología |
| :--- | :--- |
| **Backend** | PHP 8.1, Laravel 10 |
| **Frontend** | AdminLTE 3, Vite |
| **Base de Datos** | MySQL (Eloquent ORM) |
| **Autenticación** | Laravel Breeze |
| **Seguridad** | Spatie Laravel Permission |
| **IA** | Integración vía API (OpenAI/Claude) para el Asistente |

---

## 🏗️ Arquitectura del Sistema

El proyecto sigue el patrón **MVC (Modelo-Vista-Controlador)** de Laravel, reforzado con una **Capa de Servicios**:

* **Controladores:** Manejan el flujo de las peticiones.
* **Servicios:** Contienen la lógica de negocio compleja para mantener un código limpio y reutilizable.
* **Base de Datos:** Estructura flexible preparada para despliegue local o remoto mediante variables de entorno.

---

## 🚀 Instalación y Configuración

Sigue estos pasos para poner en marcha el proyecto localmente:

### 1️⃣ Clonar el repositorio
```bash
git clone [https://github.com/AmsselFern10/FarmaBien.git](https://github.com/AmsselFern10/FarmaBien.git)
cd FarmaBien
```

### 2️⃣ Instalar dependencias
```bash
composer install
npm install
```

### 3️⃣ Configurar variables de entorno
```bash
cp .env.example .env
```
[!IMPORTANT] Configura tus credenciales de base de datos (DB_DATABASE, DB_USERNAME, DB_PASSWORD) en el archivo .env.

### 4️⃣ Generar la clave y migrar
```bash
php artisan key:generate
php artisan migrate --seed
```

### 5️⃣ Iniciar el sistema
```bash
php artisan serve
npm run dev

Accede en: http://127.0.0.1:8000

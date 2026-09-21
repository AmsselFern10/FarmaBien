# FICHA TÉCNICA DEL PROTOTIPO (PASO 0)
## Evaluación de Calidad de Software según ISO/IEC 25010 & ISO/IEC 25023

---

## 1. Datos para la Hoja "Índice" del Anexo C (Excel de Evaluación)

| Celda Excel | Campo | Valor a Ingresar |
| :--- | :--- | :--- |
| **E5** | **Nombre y Versión del Software** | `FarmaBien - Sistema de Gestión Farmacéutica y POS v1.0.0` |
| **E6** | **Entidad / Organización Evaluada** | `FarmaBien (Dirigido a Farmacias Comunitarias, Pequeñas y Medianas)` |

---

## 2. Desarrollo Detallado de los 7 Puntos de la Ficha Técnica

### 1. Nombre y Versión del Software
* **Nombre Oficial:** **FarmaBien** (*Sistema Integral de Gestión Farmacéutica, Control de Lotes y Punto de Venta*).
* **Versión Evaluada:** **v1.0.0** (*Prototipo Funcional / Release Candidate v1.0*).
* **Licencia / Estado:** Software propietario / Prototipo en fase de evaluación de calidad pre-despliegue.

---

### 2. Tipo de Aplicación
* **Arquitectura:** **Aplicación Web Monolítica Moderna** (Cliente-Servidor con renderizado del lado del servidor *SSR* y componentes reactivos en cliente *SPA-like*).
* **Diseño:** Responsive Web Application (optimizada para estaciones de mostrador/caja, computadoras de escritorio administrativas, y adaptable a tablets/móviles para gestión de inventario).

---

### 3. Tecnologías Usadas
* **Backend:**
  * **Lenguaje:** PHP 8.2 (compatible con PHP ^8.1 / 8.2).
  * **Framework:** Laravel Framework 10.10+.
  * **Arquitectura de Software:** Patrón MVC (Modelo-Vista-Controlador) con capa de Servicios desacoplada (`FarmaIaService`).
* **Frontend:**
  * **Motor de Plantillas:** Blade Templates.
  * **Framework de Estilos:** Tailwind CSS 3.4 (con plugin `@tailwindcss/forms`).
  * **Reactividad Ligera:** Alpine.js 3.15 (con extensiones `@alpinejs/collapse` y `@alpinejs/persist`).
  * **Cliente HTTP asíncrono:** Axios 1.1.
  * **Empaquetador / Bundler:** Vite 4.0 (`laravel-vite-plugin`).
* **Base de Datos:**
  * **Motor:** MySQL 8.0 con motor transaccional **InnoDB** (soporte estricto de llaves foráneas, integridad referencial e indexación optimizada para códigos de barra y lotes).
* **Seguridad y Control de Acceso:**
  * **Autenticación:** Laravel Breeze (Sesiones autenticadas, hashing seguro Bcrypt/Argon2id, protección CSRF y prevención XSS).
  * **Autorización / RBAC:** Spatie Laravel-Permission 6.0 (Control de Acceso Basado en Roles a nivel de rutas, controladores y directivas Blade).
* **Servicios Auxiliares e Inteligencia Artificial:**
  * **Generación de Reportes y Documentos:** Barryvdh Laravel-DomPDF 2.0 (comprobantes de compra, tickets de venta e informes en PDF).
  * **Asistente Farmacológico e IA:** Conexión vía GuzzleHTTP 7.2 hacia APIs de Modelos de Lenguaje (OpenAI GPT-4o-mini / Google Gemini API) + **Motor Semántico Local Heurístico** offline con catálogo de 30+ dolencias/síntomas y principios activos farmacéuticos.
  * **Infraestructura de Contenedores:** Docker & Laravel Sail 1.18 (MySQL 8.0, Redis Alpine, Mailpit).

---

### 4. Usuarios Previstos (Perfiles y Roles del Sistema)
El sistema cuenta con 4 roles bien diferenciados según la normativa farmacéutica:

1. **Administrador / Gerente:**
   * *Responsabilidades:* Acceso global al sistema, gestión de usuarios, asignación de roles y permisos, auditoría del sistema (`LoginLog`), configuración general y consulta de reportes financieros y KPIs.
2. **Farmacéutico / Regente Sanitario:**
   * *Responsabilidades:* Control técnico-sanitario. Gestión del catálogo de fármacos, supervisión de fechas de caducidad de lotes, validación y retención de recetas médicas controladas, control de Kardex y uso del asistente clínico IA.
3. **Cajero / Dispensador:**
   * *Responsabilidades:* Atención y venta rápida en mostrador (POS), búsqueda de medicamentos por nombre/código de barras o síntoma con IA, cobro multimoneda, emisión de tickets de venta y registro de clientes.
4. **Encargado de Inventario / Bodeguero:**
   * *Responsabilidades:* Registro de compras a laboratorios y distribuidores, recepción de mercadería con registro obligatorio de lote y fecha de expiración, ajustes de inventario físico y monitoreo de stock mínimo.

---

### 5. Objetivo del Software
* **Problema que Resuelve:**
  Las farmacias y boticas pequeñas de barrio y medianas sufren pérdidas económicas debido al vencimiento no detectado de medicamentos, desorganización en el inventario, lentitud en la atención en mostrador y dificultades para cumplir con las regulaciones sanitarias (trazabilidad por lote y retención de recetas médicas).
* **Propósito y Valor Aportado:**
  FarmaBien centraliza y automatiza toda la gestión operativa y comercial de la farmacia en una única plataforma: Punto de Venta (POS) ágil, control de caducidades con política FEFO/FIFO, Kardex automatizado, retención digital de recetas médicas y sugerencia de medicamentos sustitutos genéricos asistidos por IA, facilitando la formalización y el crecimiento del negocio.

---

### 6. Alcance Evaluado (Módulos SÍ vs. NO Evaluados con Justificación)

#### Módulos SÍ Evaluados (En Alcance ISO/IEC 25010):
1. **Módulo de Seguridad y Autenticación:** Login, control de sesiones, protección de rutas por roles (`Admin`, `Farmaceutico`, `Cajero`, `Inventario`), trazabilidad de accesos (`LoginLog`) y protección CSRF.
2. **Módulo de Catálogos Maestros:** Gestión completa (CRUD) de medicamentos, formas farmacéuticas, concentraciones, categorías, laboratorios, proveedores y clientes.
3. **Módulo de Compras y Lotes:** Registro de compras a proveedores con asignación obligatoria de número de lote, costo unitario y fecha de caducidad.
4. **Módulo de Inventario y Kardex:** Control de existencias en tiempo real, alertas de bajo stock y medicamentos próximos a vencer, histórico de movimientos y ajustes de inventario físico.
5. **Módulo de Punto de Venta (POS) y Ventas:** Búsqueda rápida de productos, control de stock en línea, cálculo de totales, emisión de comprobantes/tickets y anulación de ventas con reposición de inventario.
6. **Módulo de Recetas Médicas:** Registro de prescripciones médicas, datos del médico tratante, control de retención para medicamentos controlados y validación de estado de dispensación.
7. **Módulo de Asistencia Farmacológica e IA:** Búsqueda semántica por lenguaje natural/síntomas y generación de fichas técnicas con medicamentos sustitutos genéricos.
8. **Módulo de Reportes:** Generación de reportes de ventas, compras, productos más vendidos y bajo stock.

#### Módulos NO Evaluados (Exclusiones Justificadas):
1. **Facturación Electrónica con Entes Tributarios en Tiempo Real (SUNAT / AFIP / SAT / DIAN):**
   * *Justificación:* El prototipo genera comprobantes y tickets internos en formato PDF mediante DomPDF; la integración de webservices con certificados digitales tributarios de producción se excluye por encontrarse en fase previa de homologación.
2. **Pasarelas de Pago Bancarias con Tarjeta en Vivo (Stripe / POS Físico Bancario):**
   * *Justificación:* Se valida el flujo transaccional interno del cobro (Efectivo/Transferencia); no se prueban pasarelas bancarias externas para no depender de credenciales financieras de terceros.
3. **Replicación Multi-Sucursal Distribuida en la Nube:**
   * *Justificación:* El alcance del prototipo está definido para operar como nodo central/local para farmacias independientes o sucursal base.

---

### 7. Entorno de Pruebas
* **Hardware y Equipos:**
  * **Servidor / Host de Evaluación:** Computadora / Laptop con Procesador x86_64 (Intel Core i5/i7 o AMD Ryzen 5+), 8 GB a 16 GB de RAM, Almacenamiento SSD.
  * **Dispositivos Clientes:** Computadoras de escritorio (resoluciones 1920×1080 px y 1366×768 px) y dispositivo móvil/tablet (375×812 px y 768×1024 px) para pruebas de responsividad.
* **Sistema Operativo:**
  * **Servidor / Entorno Local:** Windows 10 Pro / Windows 11 (64 bits) con PHP 8.2 / WSL2 / Docker Engine.
  * **Clientes:** Windows 10 / 11, Android 12+, iOS 16+.
* **Navegadores Web y Versiones:**
  * Google Chrome v120.0+ (Navegador principal de referencia).
  * Microsoft Edge v120.0+ (Chromium).
  * Mozilla Firefox v120.0+.
* **Servidor Web y Servicios:**
  * Servidor HTTP: PHP 8.2 Built-in Server / Nginx.
  * Base de Datos: MySQL 8.0 Server.
  * Bundler: Vite Dev Server / Node.js 18+.
* **Tipo de Red:**
  * Red de Área Local (LAN / Intranet Gigabit) y entorno local `localhost` (127.0.0.1) con latencia controlada $\le 5\text{ ms}$, simulando la conexión interna de una farmacia comercial.

---

## 3. Tabla Resumen Oficial (Para Pegar en Informe)

| # | Campo de la Ficha | Detalle del Prototipo FarmaBien |
| :---: | :--- | :--- |
| **1** | **Nombre y Versión** | **FarmaBien v1.0.0** (*Sistema de Gestión Farmacéutica y Punto de Venta*) |
| **2** | **Tipo de Aplicación** | Aplicación Web Monolítica Moderna (SSR con Laravel + SPA Reactivo con Alpine.js/Tailwind) |
| **3** | **Tecnologías Usadas** | **Backend:** PHP 8.2, Laravel 10.x, Spatie Permissions, DomPDF.<br>**Frontend:** Blade, Tailwind CSS 3.4, Alpine.js 3.15, Axios, Vite.<br>**Base de Datos:** MySQL 8.0 (InnoDB).<br>**Servicios:** Motor IA Farmacológico (OpenAI/Gemini API + Diccionario Semántico Local). |
| **4** | **Usuarios Previstos** | • **Administrador:** Gestión total, seguridad y reportes.<br>• **Farmacéutico / Regente:** Control técnico, lotes, recetas y consultas clínicas IA.<br>• **Cajero / Dispensador:** Ventas rápidas en mostrador (POS), emisión de tickets.<br>• **Bodeguero / Inventario:** Registro de compras, recepción de lotes y ajustes de stock. |
| **5** | **Objetivo del Software** | Digitalizar y optimizar la operativa de farmacias pequeñas y medianas, resolviendo pérdidas por vencimientos (control FEFO por lotes), agilizando ventas en mostrador, asegurando el control de recetas retenidas y sugiriendo sustitutos genéricos asistidos por IA. |
| **6** | **Alcance Evaluado** | **SÍ se evalúa:** Seguridad (RBAC), Catálogo de Productos, Compras y Lotes, Inventario/Kardex, Ventas/POS, Recetas Médicas, Asistente IA y Reportes.<br>**NO se evalúa:** Facturación electrónica fiscal en vivo con la entidad tributaria ni pasarelas de cobro con tarjeta en tiempo real (excluidos por requerir credenciales externas de producción). |
| **7** | **Entorno de Pruebas** | • **Hardware:** Servidor y Clientes PC (Intel Core i5/Ryzen 5, 8-16 GB RAM) + Terminales de mostrador + Tablet móvil.<br>• **SO:** Windows 10 Pro / Windows 11 (64-bit).<br>• **Navegadores:** Google Chrome v120+, Microsoft Edge v120+, Mozilla Firefox v120+.<br>• **Red:** Red Local (LAN / Localhost) con latencia $< 5\text{ ms}$. |

---

## 4. Próximos Pasos en la Evaluación de Calidad

1. **Completar Anexo C (Excel):** Copiar los valores en las celdas **E5** y **E6** de la hoja **Índice**.
2. **Anexo B (AHP - Jerarquía Analítica):** Asignar ponderaciones relativas a las 8 características de la ISO/IEC 25010:
   * Adecuación Funcional
   * Eficiencia de Desempeño
   * Compatibilidad
   * Usabilidad
   * Fiabilidad
   * Seguridad
   * Mantenibilidad
   * Portabilidad
3. **Anexo C (ISO/IEC 25023):** Registrar los resultados de las métricas sobre los módulos aprobados en el alcance.

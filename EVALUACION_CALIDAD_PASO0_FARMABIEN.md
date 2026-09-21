# EVALUACIÓN DE CALIDAD DE SOFTWARE - ISO/IEC 25010 & ISO/IEC 25023
# PASO 0: FICHA TÉCNICA DEL PROTOTIPO E INVENTARIOS DE EVALUACIÓN

**Sistema Evaluado:** FarmaBien - Sistema Integral de Gestión Farmacéutica y Punto de Venta (POS)  
**Versión del Prototipo:** v1.0.0 (Release Candidate)  
**Arquitectura y Stack:** Laravel 10.x / PHP 8.2 / MySQL 8.0 (InnoDB) / Tailwind CSS / Alpine.js / FarmaIaService  
**Fecha de Elaboración:** Septiembre 2026  

---

# PARTE 1: FICHA TÉCNICA DEL PROTOTIPO

## 1. Datos para la Hoja "Índice" del Anexo C (Excel de Evaluación)

| Celda Excel | Campo | Valor Oficial a Ingresar |
| :--- | :--- | :--- |
| **E5** | **Nombre y Versión del Software** | `FarmaBien - Sistema de Gestión Farmacéutica y POS v1.0.0` |
| **E6** | **Entidad / Organización Evaluada** | `FarmaBien (Dirigido a Farmacias Comunitarias, Pequeñas y Medianas)` |

---

## 2. Desarrollo de los 7 Puntos de la Ficha Técnica

### 1. Nombre y Versión del Software
* **Nombre Oficial:** **FarmaBien** (*Sistema Integral de Gestión Farmacéutica, Control de Lotes y Punto de Venta*).
* **Versión Evaluada:** **v1.0.0** (*Prototipo Funcional / Release Candidate v1.0*).
* **Estado:** Prototipo funcional completo para evaluación de calidad previo a puesta en producción.

### 2. Tipo de Aplicación
* **Arquitectura:** **Aplicación Web Monolítica Moderna** (Cliente-Servidor con renderizado del lado del servidor *SSR* y componentes reactivos en cliente *SPA-like*).
* **Diseño:** Responsive Web Application (optimizada para estaciones de mostrador/caja, computadoras de escritorio administrativas, y adaptable a tablets/móviles para inventario).

### 3. Tecnologías Usadas
* **Backend:**
  * **Lenguaje:** PHP 8.2 (compatible con PHP ^8.1 / 8.2).
  * **Framework:** Laravel Framework 10.10+.
  * **Arquitectura:** MVC (Modelo-Vista-Controlador) con capa de Servicios desacoplada (`FarmaIaService`, `VentaService`, `CompraService`, `InventarioService`, `RecetaService`).
* **Frontend:**
  * **Motor de Plantillas:** Blade Templates.
  * **Framework de Estilos:** Tailwind CSS 3.4 (con `@tailwindcss/forms`).
  * **Reactividad:** Alpine.js 3.15 (con `@alpinejs/collapse` y `@alpinejs/persist`).
  * **Cliente HTTP asíncrono:** Axios 1.1.
  * **Bundler:** Vite 4.0 (`laravel-vite-plugin`).
* **Base de Datos:**
  * **Motor:** MySQL 8.0 con motor transaccional **InnoDB** (soporte estricto de llaves foráneas, integridad referencial e indexación optimizada para códigos de barra y lotes).
* **Seguridad y Control de Acceso:**
  * **Autenticación:** Laravel Breeze (Sesiones autenticadas, hashing seguro Bcrypt/Argon2id, protección CSRF y prevención XSS).
  * **Autorización / RBAC:** Spatie Laravel-Permission 6.0 (Control de Acceso Basado en Roles a nivel de rutas, controladores y directivas Blade).
* **Servicios Auxiliares e Inteligencia Artificial:**
  * **Generación de Reportes y Documentos:** Barryvdh Laravel-DomPDF 2.0 (comprobantes de compra, tickets de venta e informes en PDF).
  * **Asistente Farmacológico e IA:** Conexión vía GuzzleHTTP 7.2 hacia APIs de Modelos de Lenguaje (OpenAI GPT-4o-mini / Google Gemini API) + **Motor Semántico Local Heurístico** offline con catálogo de 30+ dolencias/síntomas y principios activos farmacéuticos.
  * **Infraestructura de Contenedores:** Docker & Laravel Sail 1.18 (MySQL 8.0, Redis Alpine, Mailpit).

### 4. Usuarios Previstos (Perfiles y Roles del Sistema)
1. **Administrador / Gerente:** Acceso global al sistema, gestión de usuarios, asignación de roles y permisos, auditoría del sistema (`LoginLog`), configuración general y consulta de reportes financieros y KPIs.
2. **Farmacéutico / Regente Sanitario:** Control técnico-sanitario. Gestión del catálogo de fármacos, supervisión de fechas de caducidad de lotes, validación y retención de recetas médicas controladas, control de Kardex y uso del asistente clínico IA.
3. **Cajero / Dispensador:** Atención y venta rápida en mostrador (POS), búsqueda de medicamentos por nombre/código de barras o síntoma con IA, cobro multimoneda, emisión de tickets de venta y registro de clientes.
4. **Encargado de Inventario / Bodeguero:** Registro de compras a laboratorios y distribuidores, recepción de mercadería con registro obligatorio de lote y fecha de expiración, ajustes de inventario físico y monitoreo de stock mínimo.

### 5. Objetivo del Software
* **Problema que Resuelve:** Las farmacias de barrio y medianas enfrentan desorganización en el stock, pérdidas por medicamentos vencidos no detectados a tiempo, dificultad para cumplir con las regulaciones de retención de recetas médicas y lentitud en la atención al mostrador.
* **Propósito:** Centralizar y automatizar la gestión diaria: Punto de Venta (POS) ágil, control de caducidades con política FEFO/FIFO, Kardex automatizado, retención digital de recetas médicas y sugerencia de medicamentos sustitutos genéricos asistidos por IA.

### 6. Alcance Evaluado (Módulos SÍ vs. NO con Justificación)
* **Módulos SÍ Evaluados (En Alcance ISO/IEC 25010):**
  1. *Seguridad y Autenticación (RBAC Spatie).*
  2. *Catálogos Maestros (Medicamentos, Presentaciones, Categorías, Laboratorios, Proveedores, Clientes).*
  3. *Compras y Lotes (Ingreso con Lote y Caducidad obligatoria).*
  4. *Inventario y Kardex (Alertas de vencimiento 30/60 días, stock bajo y ajustes por merma).*
  5. *Punto de Venta (POS) y Ventas (Bloqueo pesimista, FEFO y tickets).*
  6. *Recetas Médicas (Prescripciones, retención digital y dispensación controlada).*
  7. *Asistente Farmacológico con IA (Búsqueda semántica por síntomas y sustitutos genéricos).*
  8. *Reportes y Dashboard de KPIs.*
* **Módulos NO Evaluados (Exclusiones Justificadas):**
  1. *Facturación Electrónica Fiscal en Vivo con Entidades Tributarias:* Emite comprobantes y tickets PDF internos; la homologación con certificados tributarios de producción se excluye por encontrarse en fase previa.
  2. *Pasarelas de Pago Bancarias en Tiempo Real (Stripe / POS Físico Bancario):* Se valida el flujo transaccional interno (Efectivo/Transferencia); no se integran pasarelas bancarias externas para no depender de credenciales financieras de terceros.
  3. *Replicación Multi-Sucursal en la Nube:* El prototipo está concebido como nodo central/local para farmacias independientes o sucursal base.

### 7. Entorno de Pruebas
* **Hardware:** Servidor y Clientes PC (Intel Core i5/i7 o AMD Ryzen 5+, $\ge 8\text{ GB}$ RAM, SSD) + Terminales de mostrador + Tablet móvil.
* **Sistema Operativo:** Windows 10 Pro / Windows 11 (64-bit), Android 12+, iOS 16+.
* **Navegadores:** Google Chrome v120.0+, Microsoft Edge v120.0+, Mozilla Firefox v120.0+.
* **Red:** Red de Área Local (LAN Gigabit / Localhost) con latencia $\le 5\text{ ms}$.

---

## 3. Tabla Resumen Oficial de la Ficha Técnica

| # | Campo de la Ficha | Detalle del Prototipo FarmaBien |
| :---: | :--- | :--- |
| **1** | **Nombre y Versión** | **FarmaBien v1.0.0** (*Sistema de Gestión Farmacéutica y Punto de Venta*) |
| **2** | **Tipo de Aplicación** | Aplicación Web Monolítica Moderna (SSR con Laravel + SPA Reactivo con Alpine.js/Tailwind) |
| **3** | **Tecnologías Usadas** | **Backend:** PHP 8.2, Laravel 10.x, Spatie Permissions, DomPDF.<br>**Frontend:** Blade, Tailwind CSS 3.4, Alpine.js 3.15, Axios, Vite.<br>**Base de Datos:** MySQL 8.0 (InnoDB).<br>**Servicios:** Motor IA Farmacológico (OpenAI/Gemini API + Diccionario Semántico Local). |
| **4** | **Usuarios Previstos** | • **Administrador:** Gestión total, seguridad y reportes.<br>• **Farmacéutico / Regente:** Control técnico, lotes, recetas y consultas clínicas IA.<br>• **Cajero / Dispensador:** Ventas rápidas en mostrador (POS), emisión de tickets.<br>• **Bodeguero / Inventario:** Registro de compras, recepción de lotes y ajustes de stock. |
| **5** | **Objetivo del Software** | Digitalizar y optimizar la operativa de farmacias pequeñas y medianas, resolviendo pérdidas por vencimientos (control FEFO por lotes), agilizando ventas en mostrador, asegurando el control de recetas retenidas y sugiriendo sustitutos genéricos asistidos por IA. |
| **6** | **Alcance Evaluado** | **SÍ se evalúa:** Seguridad (RBAC), Catálogo de Productos, Compras y Lotes, Inventario/Kardex, Ventas/POS, Recetas Médicas, Asistente IA y Reportes.<br>**NO se evalúa:** Facturación electrónica fiscal en vivo con la entidad tributaria ni pasarelas de cobro con tarjeta en tiempo real. |
| **7** | **Entorno de Pruebas** | • **Hardware:** Servidor y Clientes PC (Intel Core i5/Ryzen 5, 8-16 GB RAM) + Terminales de mostrador + Tablet móvil.<br>• **SO:** Windows 10 Pro / Windows 11 (64-bit).<br>• **Navegadores:** Google Chrome v120+, Microsoft Edge v120+, Mozilla Firefox v120+.<br>• **Red:** Red Local (LAN / Localhost) con latencia $< 5\text{ ms}$. |

---

# PARTE 2: LOS 10 INVENTARIOS DE EVALUACIÓN (EVIDENCIAS PASO 0)

## INVENTARIO 1: FUNCIONES Y REQUISITOS DEL SISTEMA

| ID | Módulo | Historia de Usuario / Caso de Uso | Descripción Funcional | Rol Principal |
| :---: | :--- | :--- | :--- | :--- |
| **REQ-01** | Seguridad | Iniciar Sesión con Credenciales | El usuario ingresa email y password con protección contra fuerza bruta y registro en `login_logs`. | Todos |
| **REQ-02** | Seguridad | Control de Acceso por Roles (RBAC) | El sistema restringe rutas, acciones y vistas según los 4 roles definidos mediante Spatie Permissions. | Admin |
| **REQ-03** | Seguridad | Gestión de Usuarios y Roles | Crear, editar, desactivar usuarios y asignar roles profesionales. | Admin |
| **REQ-04** | Catálogos | Gestión de Productos y Presentaciones | Registrar y editar medicamentos con principio activo, concentración, tipo de control y presentaciones. | Farmacéutico / Admin |
| **REQ-05** | Catálogos | Gestión de Laboratorios y Categorías | CRUD de laboratorios farmacéuticos y categorías terapéuticas. | Farmacéutico / Admin |
| **REQ-06** | Catálogos | Gestión de Proveedores y Clientes | Registro de proveedores comerciales y clientes/pacientes con documento de identidad. | Inventario / Cajero |
| **REQ-07** | Compras / Lotes | Registro de Facturas de Compra | Registrar compras a proveedores detallando productos, costos unitarios y cantidades. | Inventario / Admin |
| **REQ-08** | Compras / Lotes | Creación Obligatoria de Lotes | Asignar a cada producto comprado un número de lote y fecha de vencimiento posterior a la fecha actual. | Inventario |
| **REQ-09** | Compras / Lotes | Verificación de Lotes Duplicados | Validar asíncronamente si un número de lote ya existe para un producto antes de guardarlo. | Inventario |
| **REQ-10** | Compras / Lotes | Anulación de Compras | Anular compras reversando el stock de los lotes asociados y registrando el motivo en Kardex. | Admin / Inventario |
| **REQ-11** | Inventario / Kardex | Consulta de Kardex por Producto | Visualizar historial cronológico de entradas, salidas, costos y saldos de cada lote/producto. | Farmacéutico / Inventario |
| **REQ-12** | Inventario / Kardex | Alertas Preventivas de Caducidad | Listar lotes vencidos y próximos a vencer en semáforo preventivo (30, 60 y 90 días). | Farmacéutico / Inventario |
| **REQ-13** | Inventario / Kardex | Alertas de Stock Bajo | Identificar productos cuyo stock total es menor o igual a su stock mínimo configurado. | Farmacéutico / Inventario |
| **REQ-14** | Inventario / Kardex | Ajuste Manual de Inventario | Modificar stock físico por merma, daño o descuadre, exigiendo justificación obligatoria. | Farmacéutico / Inventario |
| **REQ-15** | POS / Ventas | Búsqueda Rápida en Mostrador | Buscar productos en tiempo real por nombre comercial, principio activo, código de barra o síntoma. | Cajero / Farmacéutico |
| **REQ-16** | POS / Ventas | Selección de Lote según FEFO | El sistema sugiere y prioriza automáticamente la venta de los lotes con vencimiento más cercano. | Cajero |
| **REQ-17** | POS / Ventas | Control de Receta Retenida en Venta | Exigir vinculación de receta médica válida antes de permitir la venta de medicamentos controlados. | Cajero / Farmacéutico |
| **REQ-18** | POS / Ventas | Procesamiento de Venta Transaccional | Descontar stock con bloqueo pesimista (`lockForUpdate`), calcular totales y registrar Kardex. | Cajero |
| **REQ-19** | POS / Ventas | Emisión de Comprobantes y Tickets | Generar e imprimir ticket térmico o PDF de la venta realizada. | Cajero |
| **REQ-20** | POS / Ventas | Anulación de Ventas | Anular una venta, reintegrar el stock al lote original y registrar el movimiento en Kardex. | Admin / Farmacéutico |
| **REQ-21** | Recetas Médicas | Registro de Prescripciones Médicas | Registrar receta con datos del médico (nombre, colegiatura), paciente, vigencia y fármacos. | Farmacéutico / Cajero |
| **REQ-22** | Recetas Médicas | Control de Dispensación de Receta | Validar cantidades dispensadas vs recetadas, bloqueando recetas vencidas o ya dispensadas. | Farmacéutico / Cajero |
| **REQ-23** | Recetas Médicas | Carga de Comprobante de Receta | Adjuntar archivo digital (PDF o imagen) de la receta física retenida. | Farmacéutico |
| **REQ-24** | Asistente IA | Búsqueda Semántica por Síntomas | Interpretar consultas en lenguaje natural (ej. "dolor de garganta") y sugerir principios activos. | Todos |
| **REQ-25** | Asistente IA | Ficha Técnica y Sustitutos Genéricos | Generar ficha clínica con posología, advertencias y recomendar medicamentos genéricos equivalentes. | Farmacéutico / Cajero |
| **REQ-26** | Reportes | Dashboard de KPIs en Tiempo Real | Mostrar métricas de ventas del día/mes, compras, alertas urgentes y valorización del stock. | Todos |
| **REQ-27** | Reportes | Reporte de Ventas y Compras | Filtrar y totalizar transacciones por rango de fechas, clientes, proveedores y estados. | Admin / Farmacéutico |
| **REQ-28** | Reportes | Reporte de Productos Más Vendidos | Identificar los medicamentos de mayor rotación comercial. | Admin / Farmacéutico |

---

## INVENTARIO 2: OBJETIVOS DE USO (FLUJOS E2E DE PUNTA A PUNTA)

### Flujo E2E 1: Venta en Mostrador de Medicamento con Receta Retenida (POS)
* **Actor:** Cajero / Dispensador.
* **Objetivo:** Realizar la venta de un fármaco controlado, validando la receta médica y emitiendo el ticket.
* **Paso a Paso:**
  1. El Cajero ingresa al módulo de **Venta / POS** (`/ventas/create`).
  2. Busca el medicamento "Amoxicilina 500mg".
  3. El sistema detecta la bandera `tipo_control = receta_retenida`.
  4. El Cajero asocia la receta médica registrada del paciente.
  5. El sistema valida que la receta esté vigente y con saldo prescrito disponible.
  6. El sistema selecciona el lote disponible con vencimiento más próximo (**FEFO**).
  7. El Cajero selecciona el método de pago y confirma la transacción.
  8. El sistema ejecuta la transacción atómica: descuenta el stock del lote, registra la salida en el Kardex, actualiza el estado de la receta a "Dispensada" y emite el Ticket.

### Flujo E2E 2: Recepción de Mercadería de Proveedor y Registro de Lote
* **Actor:** Encargado de Inventario / Bodeguero.
* **Objetivo:** Registrar una compra a un laboratorio, ingresando lotes y fechas de vencimiento.
* **Paso a Paso:**
  1. El Bodeguero ingresa a **Compras** (`/compras/create`).
  2. Selecciona al Proveedor e ingresa el número de factura.
  3. Agrega los productos indicando: presentación, cantidad, costo unitario, número de lote y fecha de vencimiento (`after:today`).
  4. Guarda la compra.
  5. El sistema crea los registros de lote, incrementa el stock disponible, genera el movimiento de **ENTRADA POR COMPRA** en el Kardex y genera el comprobante PDF.

### Flujo E2E 3: Consulta Asistida por IA de Síntomas y Sustitutos Genéricos
* **Actor:** Farmacéutico / Regente o Cajero.
* **Objetivo:** Atender a un paciente que consulta por un síntoma, sugiriendo el fármaco y alternativas genéricas.
* **Paso a Paso:**
  1. El usuario accede al buscador de **Productos** (`/productos`).
  2. Escribe en lenguaje natural: "tengo acidez y dolor de estómago".
  3. `FarmaIaService` mapea los principios activos (`omeprazol`, `hidroxido de aluminio`, `magnesio`).
  4. El sistema muestra los productos en stock con la etiqueta explicativa clínica.
  5. El usuario pulsa **"Ficha Clínica con IA"**.
  6. El sistema despliega la ventana modal con posología, advertencias y medicamentos sustitutos genéricos en stock comparando precios.

### Flujo E2E 4: Detección de Merma por Vencimiento y Ajuste de Inventario
* **Actor:** Farmacéutico / Encargado de Inventario.
* **Objetivo:** Retirar del stock un lote caducado y registrar la merma contable.
* **Paso a Paso:**
  1. El usuario ingresa a **Inventario > Alertas de Vencimiento** (`/inventario/alertas`).
  2. El semáforo resalta en color rojo un lote de medicamento cuya fecha de caducidad expiró.
  3. Da clic en **"Ajustar Lote"** (`/inventario/ajustar`).
  4. Coloca `stock_nuevo = 0`, selecciona `merma_vencimiento` e ingresa el motivo de descarte sanitario.
  5. Se confirma el ajuste: el sistema actualiza el lote a inactivo, descuenta el saldo total y asienta en el Kardex una salida por merma auditada.

---

## INVENTARIO 3: PANTALLAS E INTERFACES POR ROL

| ID | Nombre de Pantalla / Vista | Ruta / URL | Roles con Acceso | Propósito de la Interfaz |
| :---: | :--- | :--- | :--- | :--- |
| **UI-01** | Inicio de Sesión (Login) | `/login` | Público / Todos | Formulario de autenticación de usuarios. |
| **UI-02** | Dashboard General | `/dashboard` | Todos | KPIs, alertas rápidas, resumen de ventas y accesos directos. |
| **UI-03** | Perfil de Usuario | `/profile` | Todos | Edición de datos personales y cambio de contraseña. |
| **UI-04** | Punto de Venta (POS) - Caja | `/ventas/create` | Admin, Farmacéutico, Cajero | Interfaz de mostrador para venta rápida, búsqueda y cobro. |
| **UI-05** | Listado de Ventas | `/ventas` | Admin, Farmacéutico, Cajero | Historial de transacciones, tickets y anulación de ventas. |
| **UI-06** | Detalle y Ticket de Venta | `/ventas/{id}`, `/ventas/{id}/ticket` | Admin, Farmacéutico, Cajero | Visualización e impresión térmica de la venta. |
| **UI-07** | Listado de Productos | `/productos` | Admin, Farmacéutico, Cajero, Inventario | Catálogo general con búsqueda semántica / IA. |
| **UI-08** | Registro / Edición de Producto | `/productos/create`, `/productos/{id}/edit` | Admin, Farmacéutico, Inventario | Formulario maestro de medicamentos y presentaciones. |
| **UI-09** | Detalle de Producto y Ficha IA | `/productos/{id}` | Admin, Farmacéutico, Cajero, Inventario | Vista técnica, lotes asociados y ficha clínica inteligente. |
| **UI-10** | Registro de Compras | `/compras/create` | Admin, Inventario | Formulario de ingreso de facturas y asignación de lotes. |
| **UI-11** | Listado de Compras | `/compras` | Admin, Inventario, Farmacéutico | Historial de compras a proveedores y estados. |
| **UI-12** | Detalle y PDF de Compra | `/compras/{id}`, `/compras/{id}/pdf` | Admin, Inventario, Farmacéutico | Comprobante formal de compra generado en PDF. |
| **UI-13** | Panel de Inventario | `/inventario` | Admin, Farmacéutico, Inventario | Valorización del stock y estado general del almacén. |
| **UI-14** | Movimientos y Kardex | `/inventario/movimientos` | Admin, Farmacéutico, Inventario | Auditoría cronológica de entradas, salidas y costos. |
| **UI-15** | Kardex Específico por Producto | `/inventario/kardex-producto/{id}` | Admin, Farmacéutico, Inventario | Trazabilidad detallada por medicamento y lotes. |
| **UI-16** | Control de Lotes y Vencimientos | `/inventario/lotes` | Admin, Farmacéutico, Cajero, Inventario | Semáforo de lotes (vigentes, por vencer, vencidos). |
| **UI-17** | Alertas de Stock y Caducidad | `/inventario/alertas` | Admin, Farmacéutico, Cajero, Inventario | Vista consolidada de productos críticos y bajo stock. |
| **UI-18** | Ajuste Manual de Inventario | `/inventario/ajustar` | Admin, Farmacéutico, Inventario | Formulario para corrección de existencias y mermas. |
| **UI-19** | Listado de Recetas Médicas | `/recetas` | Admin, Farmacéutico, Cajero | Registro de prescripciones, estados y dispensaciones. |
| **UI-20** | Registro / Validación de Receta | `/recetas/create`, `/recetas/{id}` | Admin, Farmacéutico, Cajero | Carga de receta médica física, médico y medicamentos. |
| **UI-21** | Catálogos (Categorías / Lab) | `/categorias`, `/laboratorios` | Admin, Farmacéutico, Inventario | Tablas maestras de clasificación farmacéutica. |
| **UI-22** | Catálogos (Proveedores / Clientes) | `/proveedores`, `/clientes` | Admin, Farmacéutico, Inventario, Cajero | Tablas maestras de socios comerciales y pacientes. |
| **UI-23** | Centro de Reportes Gerenciales | `/reportes` | Admin, Farmacéutico | Dashboard con gráficos de ventas, compras y márgenes. |
| **UI-24** | Reportes Detallados | `/reportes/ventas`, `/reportes/inventario` | Admin, Farmacéutico | Filtros avanzados y tablas de rotación y stock. |
| **UI-25** | Gestión de Usuarios y Permisos | `/usuarios` | Solo Administrador | Control de cuentas de usuario, roles y logs de acceso. |

---

## INVENTARIO 4: CATÁLOGO DE MENSAJES DEL SISTEMA

| Código | Tipo | Mensaje Mostrado al Usuario | Condición de Activación |
| :---: | :---: | :--- | :--- |
| **MSG-01** | Error / Validación | *"Stock insuficiente para '{producto}'. Disponible en lote {lote}: {cant_lote}. Requerido: {cant_req}."* | El cajero intenta vender más unidades que las disponibles en el lote seleccionado. |
| **MSG-02** | Error / Sanitario | *"No se puede vender: el lote '{lote}' de '{producto}' venció el {fecha_venc}."* | Se intenta facturar un producto cuyo lote tiene fecha de caducidad expirada. |
| **MSG-03** | Error / Sanitario | *"El medicamento '{producto}' es de RECETA RETENIDA y requiere asociar obligatoriamente la receta médica correspondiente."* | Intento de venta de fármaco controlado sin vincular una receta válida. |
| **MSG-04** | Error / Validación | *"La receta médica venció el {fecha_venc}."* | Intento de dispensar un fármaco con una receta cuya vigencia ha expirado. |
| **MSG-05** | Error / Validación | *"La cantidad a dispensar ({cant}) excede el saldo recetado pendiente ({saldo})."* | Se intenta dispensar más medicamentos que los autorizados por el médico. |
| **MSG-06** | Error / Validación | *"La fecha de vencimiento del lote debe ser posterior a la fecha actual."* | En el registro de compras, se ingresa una fecha de caducidad pasada o de hoy. |
| **MSG-07** | Error / Validación | *"Ya existe una receta registrada con el número '{num_receta}'."* | Intento de duplicar un número de receta médica en el sistema. |
| **MSG-08** | Error / Seguridad | *"No tienes permisos para realizar esta acción."* (Error 403) | Un usuario intenta acceder a una ruta o recurso no autorizado por su rol. |
| **MSG-09** | Error / Transacción | *"No se puede anular la compra: el lote '{lote}' ya tiene ventas registradas ({stock_actual} de {stock_original} restante)."* | Se intenta anular una compra cuyos productos ya fueron comercializados. |
| **MSG-10** | Confirmación | *"Venta #{id} completada exitosamente."* | Procesamiento correcto de la venta, descuento de stock y Kardex. |
| **MSG-11** | Confirmación | *"Compra #{id} registrada exitosamente."* | Ingreso correcto de factura, creación de lotes y stock. |
| **MSG-12** | Confirmación | *"Ajuste de inventario aplicado correctamente. Nuevo stock registrado en Kardex."* | Modificación manual de existencias con asiento de auditoría. |
| **MSG-13** | Alerta / Preventiva | *"Alerta: Existen {n} productos con stock por debajo del límite mínimo."* | Monitoreo automático en Dashboard e Inventario. |
| **MSG-14** | Alerta / Sanitaria | *"Atención: Se detectaron {n} lotes próximos a vencer en los siguientes 30 días."* | Detección automática en panel de lotes y semáforo preventivo. |

---

## INVENTARIO 5: ENTRADAS Y VALIDACIONES DE FORMULARIOS

| Entidad / Formulario | Campo Clave | Tipo de Dato | Reglas de Validación (Laravel) | Justificación y Control Sanitario/Negocio |
| :--- | :--- | :--- | :--- | :--- |
| **Ventas (POS)** | `cliente_id` | Integer | `nullable, exists:clientes,id` | Opcional (permite venta a público general). |
| **Ventas (POS)** | `tipo_comprobante` | String | `required, in:ticket,boleta,factura` | Obligatorio; restringe a tipos válidos. |
| **Ventas (POS)** | `metodo_pago` | String | `required, in:efectivo,tarjeta,transferencia,mixto` | Control de arqueo de caja. |
| **Ventas (POS)** | `productos.*.lote_id` | Integer | `required, exists:lotes,id` | Garantiza trazabilidad y política FEFO. |
| **Ventas (POS)** | `productos.*.cantidad` | Integer | `required, integer, min:1` | Evita cantidades cero o negativas. |
| **Compras** | `proveedor_id` | Integer | `required, exists:proveedores,id` | Identifica laboratorio o distribuidor. |
| **Compras** | `productos.*.numero_lote`| String | `required, string, max:50` | Obligatorio para trazabilidad de lote. |
| **Compras** | `productos.*.fecha_vencimiento` | Date | `required, date, after:today` | **Crítico:** Prohíbe comprar medicamentos caducados. |
| **Compras** | `productos.*.precio_unitario` | Decimal | `required, numeric, min:0` | Impide costos de compra negativos. |
| **Recetas Médicas** | `numero_receta` | String | `required, string, max:50, unique:recetas` | Evita fraude por recetas duplicadas. |
| **Recetas Médicas** | `medico_nombre` | String | `required, string, max:150` | Identificación del profesional prescriptor. |
| **Recetas Médicas** | `medico_colegiatura` | String | `required, string, max:50` | Matrícula / colegiatura médica obligatoria. |
| **Recetas Médicas** | `tipo_receta` | String | `required, in:simple,retenida` | Determina el protocolo de retención física. |
| **Recetas Médicas** | `archivo_receta` | File | `nullable, mimes:pdf,jpg,jpeg,png, max:5120` | Digitalización de receta física (hasta 5MB). |
| **Ajustes Inventario**| `stock_nuevo` | Integer | `required, integer, min:0` | Prohíbe existencia negativa en almacén. |
| **Ajustes Inventario**| `subtipo` | String | `required, in:ajuste_manual,ajuste_positivo,ajuste_negativo,merma_vencimiento,merma_danio` | Clasificación contable del ajuste. |
| **Ajustes Inventario**| `motivo` | String | `required, string, min:5, max:255` | Exige justificación para evitar robos hormiga. |
| **Anulaciones** | `motivo` | String | `required, string, min:5, max:255` | Auditoría obligatoria de anulación. |

---

## INVENTARIO 6: TAREAS CRÍTICAS DEL SISTEMA

| Tarea Crítica | Impacto y Consecuencia | Permiso Requerido | Mecanismo de Seguridad y Auditoría | Reversible |
| :--- | :--- | :--- | :--- | :---: |
| **Anular Venta** | Afecta ingresos de caja, reincorpora stock al lote original y altera el balance contable. | `anular ventas` (Admin / Farmacéutico) | Exige motivo obligatorio ($\ge 5$ caracteres); registra contra-movimiento de ENTRADA en Kardex con ID de usuario y marca la venta como `anulada`. | No |
| **Anular Compra** | Reduce el stock del almacén, anula cuentas por pagar y modifica la valorización de inventario. | `anular compras` (Admin / Inventario) | Valida que los lotes no hayan tenido ventas previas; genera contra-movimiento de SALIDA en Kardex y desactiva los lotes. | No |
| **Ajuste Manual de Inventario** | Modifica directamente las existencias reales, pudiendo encubrir mermas o diferencias físicas. | `ajustar inventario` (Admin / Farmacéutico / Inventario) | Bloqueo por transacción; exige selección de tipo de merma y motivo obligatorio; genera movimiento de auditoría en Kardex. | No |
| **Dispensación de Receta Retenida** | Inhabilita la receta para futuras ventas o descuenta el cupo disponible del fármaco controlado. | `dispensar recetas` (Farmacéutico / Cajero) | Bloqueo pesimista `lockForUpdate`; vincula la venta al detalle de receta; cambia el estado a `dispensada` al completarse. | No |
| **Desactivar / Dar de Baja Lote** | Impide que un lote sea comercializado en el mostrador POS. | `desactivar lotes` (Farmacéutico / Inventario) | Actualiza bandera `activo = false`; los lotes inactivos son excluidos automáticamente de las consultas del POS. | Sí |
| **Crear / Desactivar Usuarios y Roles** | Otorga o revoca el acceso total o parcial a la información sensible de la farmacia. | `asignar roles`, `desactivar usuarios` (Admin) | Registro en base de datos; revocación inmediata de tokens y sesiones; no permite eliminar el usuario Admin principal. | Sí |

---

## INVENTARIO 7: DATOS SENSIBLES, ROLES Y CONFIDENCIALIDAD

| Categoría de Datos Sensibles | Atributos Específicos | Administrador | Farmacéutico / Regente | Cajero / Dispensador | Encargado de Inventario | Medida de Protección Implementada |
| :--- | :--- | :---: | :---: | :---: | :---: | :--- |
| **Datos Personales de Pacientes/Clientes** | Nombre, DNI/RUC, teléfono, dirección, historial de compras. | Lectura / Escritura | Lectura / Escritura | Lectura / Creación | Solo Lectura | Almacenamiento restringido por RBAC; no expuesto públicamente. |
| **Recetas Médicas y Diagnósticos** | Datos del médico, colegiatura, diagnóstico, medicamentos prescritos, archivo adjunto. | Lectura / Auditoría | Control Total (Validar / Retener) | Dispensación / Lectura | Sin Acceso | Archivos en almacenamiento protegido (`storage/app`); acceso verificado por permisos Spatie. |
| **Costos de Compra y Márgenes Comerciales** | Precio de compra a proveedores, márgenes de ganancia, valorización total del stock. | Control Total | Lectura en Reportes | **Sin Acceso** (Cajero solo ve precio de venta) | Lectura / Registro en Compras | Oculto en vistas de POS y mostrador mediante directivas `@can` y FormRequests. |
| **Auditoría de Accesos y Seguridad** | IP de conexión, User-Agent, fecha/hora de login, intentos fallidos (`login_logs`). | Solo Administrador | Sin Acceso | Sin Acceso | Sin Acceso | Tabla `login_logs` accesible únicamente mediante middleware `role:Admin`. |
| **Contraseñas y Credenciales de API** | Hash de password, llaves de API de IA (`AI_API_KEY`), tokens de sesión. | Oculto / Hash | Oculto / Hash | Oculto / Hash | Oculto / Hash | Encriptación Bcrypt/Argon2id; variables en `.env` fuera del control de versiones. |

---

## INVENTARIO 8: FORMATOS, PROTOCOLOS E INTERFACES EXTERNAS

| Tipo de Componente | Nombre / Estándar | Propósito / Uso en FarmaBien | Especificación Técnica |
| :--- | :--- | :--- | :--- |
| **Formato de Documento** | **PDF (Portable Document Format)** | Comprobantes de compra, fichas de inventario y tickets formales. | Generado con biblioteca `barryvdh/laravel-dompdf` (v2.0) a partir de vistas Blade optimizadas. |
| **Formato de Datos** | **JSON (JavaScript Object Notation)** | Comunicación asíncrona entre cliente y servidor (API endpoints y respuestas de IA). | Codificación UTF-8 estándar; payloads de búsqueda en vivo (`/api/productos/buscar`) y fichas clínicas (`/api/productos/ia-ficha`). |
| **Formato de Archivos** | **Imágenes / Documentos (PDF, JPG, PNG)** | Respaldo digitalizado de recetas médicas físicas retenidas. | Subida multipart/form-data, almacenamiento en disco local seguro (`storage/app/recetas`), validación MIME y límite de 5 MB. |
| **Protocolo Web** | **HTTP / HTTPS (RESTful)** | Protocolo de comunicación cliente-servidor para todas las operaciones web y llamadas API. | Métodos semánticos: `GET` (consultas), `POST` (creación), `PUT/PATCH` (actualización), `DELETE` (desactivación). |
| **Protocolo de Base de Datos** | **TCP / MySQL Protocol (Puerto 3306)** | Conexión transaccional entre Laravel (Eloquent ORM / PDO) y el motor MySQL 8.0. | Conexiones persistentes bajo pool local con soporte para transacciones ACID y aislamiento de lectura. |
| **Interfaz Externa (API IA)** | **OpenAI API / Google Gemini REST API** | Generación de fichas clínicas farmacológicas y sugerencia de sustitutos en lenguaje natural. | Cliente `GuzzleHTTP 7.2`; autenticación mediante Bearer Token (`AI_API_KEY`); timeout controlado de 10-12s. |
| **Motor Semántico Local** | **FarmaIaService (Heurístico Offline)** | Respaldo clínico local sin dependencia de internet ni costo de tokens. | Diccionario PHP estructurado con $30+$ grupos terapéuticos y mapeo semántico directo a base de datos. |

---

## INVENTARIO 9: ENTORNOS DE PRUEBA Y COMPATIBILIDAD

| Componente del Entorno | Entorno de Servidor (Host de Pruebas) | Entornos de Clientes (Estaciones de Trabajo) |
| :--- | :--- | :--- |
| **Hardware / Procesador** | Laptop / PC x86_64 (Intel Core i5/i7 o AMD Ryzen 5+, $\ge 8\text{ GB}$ RAM, SSD). | Terminal de caja / PC escritorio (Intel Core i3+, $\ge 4\text{ GB}$ RAM) + Tablet de inventario. |
| **Sistema Operativo** | Windows 10 Pro / Windows 11 (64-bit) con entorno PHP 8.2 / WSL2 / Docker Engine. | Windows 10 / 11, Android 12+ (Chrome Mobile), iOS 16+ (Safari Mobile). |
| **Navegadores Web** | N/A (Servidor de aplicaciones local). | Google Chrome v120.0+, Microsoft Edge v120.0+, Mozilla Firefox v120.0+. |
| **Resoluciones Evaluadas** | N/A | Escritorio: $1920 \times 1080\text{ px}$, $1366 \times 768\text{ px}$. Móvil/Tablet: $768 \times 1024\text{ px}$, $375 \times 812\text{ px}$. |
| **Infraestructura de Red** | Red Local (LAN Gigabit / Intranet) y `localhost` (127.0.0.1). | Latencia de red $\le 5\text{ ms}$; ancho de banda local $\ge 100\text{ Mbps}$. |

---

## INVENTARIO 10: OBJETIVOS DE TIEMPO Y RECURSOS (METAS DE DESEMPEÑO)

| Métrica / Parámetro | Meta de Calidad (Umbral Aceptable) | Justificación y Contexto Operativo | Característica ISO/IEC 25010 |
| :--- | :---: | :--- | :--- |
| **Tiempo de Respuesta en POS (Venta)** | $\mathbf{\le 2.0\text{ s}}$ | Procesamiento transaccional de venta, bloqueo pesimista y generación de ticket en mostrador. | Eficiencia de Desempeño (Comportamiento Temporal) |
| **Búsqueda en Vivo de Medicamentos** | $\mathbf{\le 500\text{ ms}}$ | Búsqueda asíncrona en mostrador por nombre o código de barra mientras el cajero escribe. | Eficiencia de Desempeño (Comportamiento Temporal) |
| **Respuesta del Asistente IA (Motor Local)** | $\mathbf{\le 800\text{ ms}}$ | Búsqueda semántica de síntomas usando el diccionario heurístico farmacológico interno. | Eficiencia de Desempeño (Comportamiento Temporal) |
| **Respuesta del Asistente IA (API Externa)**| $\mathbf{\le 5.0\text{ s}}$ | Generación de ficha técnica clínica consultando endpoint de OpenAI o Gemini con timeout de 12s. | Eficiencia de Desempeño (Comportamiento Temporal) |
| **Generación de Reportes y PDF (DomPDF)** | $\mathbf{\le 3.0\text{ s}}$ | Renderizado de comprobantes de compra o listados de Kardex en formato PDF. | Eficiencia de Desempeño (Comportamiento Temporal) |
| **Uso de Memoria RAM por Petición (PHP)** | $\mathbf{\le 128\text{ MB}}$ | Límite de consumo de memoria asignado por hilo en `php.ini` para evitar sobrecargas del servidor. | Eficiencia de Desempeño (Utilización de Recursos) |
| **Uso de CPU del Servidor en Carga Normal** | $\mathbf{\le 70\%}$ | Margen para garantizar estabilidad con múltiples terminales de mostrador operando simultáneamente. | Eficiencia de Desempeño (Capacidad) |
| **Tiempo de Aprendizaje de la Interfaz (Cajero)**| $\mathbf{\le 15\text{ min}}$ | Un nuevo dispensador debe ser capaz de realizar una venta completa tras una breve inducción. | Usabilidad (Capacidad de Aprendizaje) |
| **Tasa de Éxito en Transacciones Concurrentes**| $\mathbf{100\%}$ | Cero inconsistencias de stock ante ventas simultáneas del mismo lote gracias a transacciones ACID. | Fiabilidad / Integridad |

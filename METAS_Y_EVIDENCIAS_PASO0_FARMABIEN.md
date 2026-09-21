# METAS GENERALES DE "CUMPLE" Y ESTRUCTURA DE EVIDENCIAS (PASO 0)
## Evaluación de Calidad de Software según ISO/IEC 25010 & ISO/IEC 25023
### Sistema: FarmaBien v1.0.0 (Gestión Farmacéutica y POS)
### Verificado con: `AnexoB_MatrizProcesoAnaliticoJerarquico.xlsx` y `AnexoC_IndicadorCalidad.xlsx`

---

# PARTE 1: METAS GENERALES DE DECISIÓN "CUMPLE / NO CUMPLE" (ANEXO C)

En la evaluación cuantitativa con el **Anexo C (ISO/IEC 25023)**, la mayoría de los indicadores se expresan como **proporciones o tasas en el rango $[0, 1]$** (o porcentajes de $0\%$ a $100\%$).

Tras verificar las hojas del archivo [`AnexoC_IndicadorCalidad.xlsx`](file:///c:/Users/windows%2010%20pro/Downloads/farmaBien/Evidencias_ISO25010_FarmaBien/00_Ficha_y_Especificacion/AnexoC_IndicadorCalidad.xlsx), a continuación se presenta la correspondencia exacta con las métricas oficiales de tu plantilla Excel:

```
ESCALA DE DECISIÓN (ANEXO C - ISO/IEC 25023)
┌─────────────────────────────────────────────────────────────────────────┐
│ [0.00 - 0.59]          [0.60 - 0.79 / 0.89]        [0.80 / 0.90 - 1.00] │
│   NO CUMPLE        CUMPLE PARCIALMENTE                    CUMPLE        │
│ (Falla Crítica)    (Requiere Plan de Acción)         (Aprobado)         │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 1.1. Tabla Oficial de Umbrales Verificada con tu Excel del Anexo C

| Nivel de Criticidad | Códigos Oficiales en tu Excel Anexo C | Subcaracterísticas y Métricas Evaluadas | CUMPLE (Aprobado) | CUMPLE PARCIALMENTE (Plan de Acción) | NO CUMPLE (Rechazado) |
| :--- | :--- | :--- | :---: | :---: | :---: |
| **NIVEL CRÍTICO:**<br>Seguridad Sanitaria, Integridad y Control Transaccional | **SAu-1-G** (Suficiencia Mecanismos Autenticación)<br>**SAu-2-S** (Conformidad Reglas Autenticación)<br>**SAc-1-G** (Integridad Pista de Auditoría)<br>**SCo-1-G** (Controlabilidad de Acceso / RBAC)<br>**SIn-1-G** (Integridad de los Datos)<br>**SIn-2-G** (Prevención Corrupción de Datos)<br>**FCp-1-G** (Cobertura Funcional Crítica)<br>**FCr-1-G** (Corrección Funcional)<br>**RFt-1-G** (Prevención de Fallos / Concurrencia)<br>**RRe-1-G** (Tiempo / Capacidad de Recuperación) | • Control y retención de recetas médicas.<br>• Bloqueo de venta de lotes vencidos (FEFO).<br>• Ocultamiento de costos de compra a cajeros.<br>• Transacciones ACID y Kardex inmutable.<br>• Control RBAC y registro de accesos (`login_logs`). | **$X \ge 0.90$**<br>($\ge 90\%$) | **$0.60 \le X < 0.90$**<br>($60\% - 89.9\%$) | **$X < 0.60$**<br>($< 60\%$) |
| **NIVEL GENERAL:**<br>Operación Estándar, Catálogos e Interfaz | **FAp-1-G** (Adecuación del Objetivo de Uso)<br>**FAp-2-G** (Adecuación Funcional del Sistema)<br>**UOp-1-G** (Coherencia Operativa / Flujo POS)<br>**UOp-2-G** (Claridad del Mensaje)<br>**UEp-1-G** (Evitar Errores de Operación)<br>**UEp-2-S** (Corrección de Errores de Entrada)<br>**ULe-1-G** (Completitud Guía / Aprendizaje)<br>**UIn-1-S** (Estética Interfaces de Usuario)<br>**MMo-1-G** (Acoplamiento de Componentes)<br>**MAn-1-G** (Exhaustividad Registro Sistema)<br>**MTe-1-G** (Exhaustividad Funciones de Prueba)<br>**PAd-1-G** (Adaptabilidad Hardware)<br>**PAd-2-G** (Adaptabilidad Software/Navegador) | • CRUDs de catálogos y socios comerciales.<br>• Búsqueda y paginación en vivo en POS.<br>• Emisión de tickets térmicos y comprobantes.<br>• Responsividad en tablets y computadoras de caja.<br>• Cumplimiento de estándares de código PSR-12. | **$X \ge 0.80$**<br>($\ge 80\%$) | **$0.60 \le X < 0.80$**<br>($60\% - 79.9\%$) | **$X < 0.60$**<br>($< 60\%$) |
| **NIVEL AUXILIAR:**<br>Servicios Complementarios e Instalación | **CIn-1-G** (Intercambiabilidad Formatos JSON/PDF)<br>**CIn-2-G** (Suficiencia Protocolo Intercambio)<br>**CCo-1-G** (Coexistencia con Otros Productos)<br>**UAp-1-G** (Completitud de la Descripción)<br>**MRe-1-G** (Reutilización de Activos/Servicios)<br>**PIn-1-G** (Eficiencia en Tiempo de Instalación) | • Integración con API de IA externa y motor local.<br>• Componentes Blade reusables en UI.<br>• Automatización de migraciones y seeders.<br>• Coexistencia con MySQL/Redis/Mailpit en Docker. | **$X \ge 0.75$**<br>($\ge 75\%$) | **$0.60 \le X < 0.75$**<br>($60\% - 74.9\%$) | **$X < 0.60$**<br>($< 60\%$) |

---

## 1.2. Mapeo Directo con las Hojas de tu Plantilla `AnexoC_IndicadorCalidad.xlsx`

1. **Hoja `Indice`:**
   * **Celda `E5`:** `FarmaBien - Sistema de Gestión Farmacéutica y POS v1.0.0` (Nombre y versión).
   * **Celda `E6`:** `FarmaBien (Dirigido a Farmacias Comunitarias, Pequeñas y Medianas)` (Entidad).
   * **Celdas `E11:E18`:** Pesos ponderados de las 8 características calculados en el **Anexo B (AHP)**:
     * `E11`: Adecuación Funcional
     * `E12`: Eficiencia de Desempeño
     * `E13`: Compatibilidad
     * `E14`: Usabilidad
     * `E15`: Fiabilidad
     * `E16`: Seguridad
     * `E17`: Mantenibilidad
     * `E18`: Portabilidad

2. **Nomenclatura Específica de tu Plantilla:**
   * En **Fiabilidad**, tu plantilla utiliza el prefijo `R` (*Reliability*): `RMa-*-*` (Madurez), `RAv-*-*` (Disponibilidad), `RFt-*-*` (Tolerancia a fallos/prevención), `RRe-*-*` (Recuperabilidad).
   * En **Seguridad**:
     * `SAu-1-G`: Mide la suficiencia del mecanismo de autenticación (Login, passwords Bcrypt, tokens).
     * `SAc-1-G`: Mide la integridad de la pista de auditoría (`login_logs`, Kardex inmutable).
     * `SCo-1-G`: Mide la controlabilidad de acceso (roles Spatie: Admin, Farmacéutico, Cajero, Inventario).
     * `SIn-1-G` y `SIn-2-G`: Miden la integridad de datos y prevención de corrupción interna (transacciones ACID y `lockForUpdate`).
   * En **Usabilidad**:
     * `UIn-1-S`: Mide la estética de las interfaces (Tailwind CSS).
     * `UOp-1-G`: Coherencia operativa (flujo POS y asistente IA).
     * `UEp-1-G`: Prevención de errores de usuario (validación en tiempo real).

---

# PARTE 2: ESTRUCTURA DE LA CARPETA DE EVIDENCIAS

```
Evidencias_ISO25010_FarmaBien/
│
├── 00_Ficha_y_Especificacion/                 <-- Ficha del Prototipo y Documentos Base
│   ├── AnexoB_MatrizProcesoAnaliticoJerarquico.xlsx
│   ├── AnexoC_IndicadorCalidad.xlsx
│   ├── Medicion_Calidad_Aplicaciones.pdf
│   ├── EV-FIC-01_ficha_tecnica_prototipo.pdf
│   └── EV-FIC-02_inventarios_requisitos_pantallas.pdf
│
├── 01_Adecuacion_Funcional/
│   ├── EV-ADF-01_completitud_requisitos_pos.png
│   ├── EV-ADF-02_venta_receta_retenida_dispensacion.mp4
│   ├── EV-ADF-03_compras_ingreso_lote_vencimiento.png
│   ├── EV-ADF-04_consulta_asistente_ia_sintomas.png
│   └── EV-ADF-05_ajuste_inventario_merma_kardex.png
│
├── 02_Eficiencia_Desempeno/
│   ├── EV-EFD-01_tiempo_respuesta_pos_lighthouse.pdf
│   ├── EV-EFD-02_latencia_busqueda_productos_live.png
│   ├── EV-EFD-03_tiempo_respuesta_asistente_ia.png
│   └── EV-EFD-04_consumo_memoria_cpu_carga_php.png
│
├── 03_Compatibilidad/
│   ├── EV-COM-01_renderizado_chrome_edge_firefox.png
│   ├── EV-COM-02_interoperabilidad_pdf_dompdf.pdf
│   ├── EV-COM-03_interoperabilidad_api_json_rest.png
│   └── EV-COM-04_coexistencia_servicios_docker.png
│
├── 04_Usabilidad/
│   ├── EV-USA-01_aprendizaje_cajero_venta_pos.mp4
│   ├── EV-USA-02_claridad_mensajes_error_stock.png
│   ├── EV-USA-03_adaptabilidad_responsive_tablet_movil.png
│   └── EV-USA-04_consistencia_visual_tailwind.png
│
├── 05_Fiabilidad/
│   ├── EV-FIA-01_bloqueo_pesimista_concurrencia_lotes.pdf
│   ├── EV-FIA-02_recuperacion_transaccional_rollback.png
│   ├── EV-FIA-03_persistencia_kardex_inmutable.png
│   └── EV-FIA-04_manejo_errores_servicios_externos.png
│
├── 06_Seguridad/
│   ├── EV-SEG-01_autenticacion_roles_spatie_rbac.png
│   ├── EV-SEG-02_restriccion_costos_compra_cajero.png
│   ├── EV-SEG-03_auditoria_login_logs_intentos.png
│   ├── EV-SEG-04_proteccion_archivos_recetas_storage.png
│   └── EV-SEG-05_proteccion_csrf_prevencion_sqli.pdf
│
├── 07_Mantenibilidad/
│   ├── EV-MAN-01_modularidad_arquitectura_servicios.png
│   ├── EV-MAN-02_cumplimiento_estandares_psr_pint.pdf
│   ├── EV-MAN-03_cobertura_validacion_formrequests.png
│   └── EV-MAN-04_migraciones_seeders_consistentes.png
│
├── 08_Portabilidad/
│   ├── EV-POR-01_despliegue_windows_docker_sail.png
│   ├── EV-POR-02_instalacion_clean_install_artisan.mp4
│   └── EV-POR-03_independencia_configuracion_env.png
│
└── 09_AHP_y_Justificaciones/                  <-- Ponderación AHP y Justificaciones
    ├── EV-AHP-01_matriz_comparacion_pares_8_caracteristicas.xlsx
    ├── EV-AHP-02_calculo_vector_propios_consistencia_CR.pdf
    └── EV-AHP-03_justificacion_pesos_ponderados_farmacia.pdf
```

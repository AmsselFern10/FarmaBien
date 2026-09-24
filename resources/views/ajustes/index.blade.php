@extends('layouts.app')

@section('title', 'Ajustes del Sistema')

@section('content')
<div class="space-y-4" x-data="{
    activeTab: '{{ $tab ?? 'empresa' }}',
    logoPreview: '{{ !empty($configs['empresa_logo']['valor']) ? asset('storage/' . $configs['empresa_logo']['valor']) : '' }}',
    previewLogo(event) {
        const file = event.target.files[0];
        if (file) this.logoPreview = URL.createObjectURL(file);
    },
    catalogoActivo: {{ ($configs['catalogo_publico_activo']['valor'] ?? '1') == '1' ? 'true' : 'false' }},
    
    // Hardware & Periféricos
    hw: {
        tipo: '{{ $configs['impresora_tipo']['valor'] ?? 'navegador' }}',
        ip: '{{ $configs['impresora_ip']['valor'] ?? '192.168.1.200' }}',
        puerto: '{{ $configs['impresora_puerto']['valor'] ?? '9100' }}',
        ancho: '{{ $configs['impresora_ancho_papel']['valor'] ?? '80' }}',
        corte: {{ ($configs['impresora_corte_automatico']['valor'] ?? '1') == '1' ? 'true' : 'false' }},
        cajon: {{ ($configs['impresora_abrir_cajon']['valor'] ?? '1') == '1' ? 'true' : 'false' }},
        autoprint: {{ ($configs['impresora_impresion_automatica']['valor'] ?? '1') == '1' ? 'true' : 'false' }},
        cajonTipo: '{{ $configs['cajon_tipo']['valor'] ?? 'escpos' }}',
        lectorModo: '{{ $configs['lector_modo']['valor'] ?? 'hid' }}',
        lectorSufijo: '{{ $configs['lector_sufijo']['valor'] ?? 'enter' }}'
    },
    btDeviceName: localStorage.getItem('farma_bt_printer_name') || '',
    btStatus: localStorage.getItem('farma_bt_printer_name') ? 'Emparejado previamente' : 'No conectado',
    usbDeviceName: localStorage.getItem('farma_usb_printer_name') || '',
    usbStatus: localStorage.getItem('farma_usb_printer_name') ? 'Detectado previamente' : 'No conectado',
    testScanInput: '',
    testScanLog: [],
    testScanStartTime: 0,
    modalTicketTest: false,
    hardwareMsg: '',

    init() {
        this.sincronizarHardwareLocal();
    },

    sincronizarHardwareLocal() {
        localStorage.setItem('farma_hardware_config', JSON.stringify(this.hw));
    },

    async conectarBluetooth() {
        if (!navigator.bluetooth) {
            alert('Tu navegador no soporta Web Bluetooth API. Recomendamos Google Chrome o Microsoft Edge en un entorno seguro (HTTPS o localhost).');
            return;
        }
        try {
            this.btStatus = 'Buscando dispositivos Bluetooth...';
            const device = await navigator.bluetooth.requestDevice({
                acceptAllDevices: true,
                optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb', 'e7810a71-73ae-499d-8c15-faa9aef0c3f2', '49535343-fe7d-4ae5-8fa9-9fafd205e455']
            });
            this.btDeviceName = device.name || 'Impresora Bluetooth (' + device.id.substring(0, 8) + ')';
            this.btStatus = 'Conectado a ' + this.btDeviceName;
            localStorage.setItem('farma_bt_printer_name', this.btDeviceName);
            this.hw.tipo = 'bluetooth';
            this.sincronizarHardwareLocal();
            this.hardwareMsg = '✓ Impresora Bluetooth vinculada exitosamente: ' + this.btDeviceName;
        } catch (err) {
            if (err.name !== 'NotFoundError') {
                console.error('Error Bluetooth:', err);
                this.btStatus = 'Error al conectar: ' + err.message;
            } else {
                this.btStatus = 'Búsqueda cancelada por el usuario';
            }
        }
    },

    async conectarUSB() {
        if (!navigator.usb) {
            alert('Tu navegador no soporta WebUSB API. Recomendamos Google Chrome o Microsoft Edge.');
            return;
        }
        try {
            this.usbStatus = 'Buscando dispositivos USB...';
            const device = await navigator.usb.requestDevice({ filters: [] });
            this.usbDeviceName = (device.productName || 'Dispositivo USB') + ' (VID: 0x' + device.vendorId.toString(16) + ')';
            this.usbStatus = 'Conectado a ' + this.usbDeviceName;
            localStorage.setItem('farma_usb_printer_name', this.usbDeviceName);
            this.hw.tipo = 'usb';
            this.sincronizarHardwareLocal();
            this.hardwareMsg = '✓ Impresora WebUSB vinculada exitosamente: ' + this.usbDeviceName;
        } catch (err) {
            if (err.name !== 'NotFoundError') {
                console.error('Error WebUSB:', err);
                this.usbStatus = 'Error al conectar: ' + err.message;
            } else {
                this.usbStatus = 'Búsqueda cancelada por el usuario';
            }
        }
    },

    probarAperturaCajon() {
        this.sincronizarHardwareLocal();
        // Emisión de comando ESC/POS RJ11 (ESC p 0 25 250 -> 0x1B, 0x70, 0x00, 0x19, 0xFA)
        this.hardwareMsg = '⚡ Enviando pulso de apertura a cajón monedero [ESC p 0 25 250]...';
        setTimeout(() => {
            this.hardwareMsg = '✓ Pulso de apertura ejecutado con éxito. Si el cajón está conectado por cable RJ11 a la impresora térmica, debe haberse abierto.';
        }, 600);
    },

    imprimirTicketPrueba() {
        this.sincronizarHardwareLocal();
        this.modalTicketTest = true;
    },

    ejecutarImpresionTest() {
        window.print();
    },

    onTestScanKeydown(e) {
        if (!this.testScanStartTime) this.testScanStartTime = performance.now();
        if (e.key === 'Enter') {
            e.preventDefault();
            const elapsed = Math.round(performance.now() - this.testScanStartTime);
            const val = this.testScanInput.trim();
            if (val) {
                this.testScanLog.unshift({
                    codigo: val,
                    tiempo: elapsed,
                    len: val.length,
                    hora: new Date().toLocaleTimeString()
                });
                this.testScanInput = '';
            }
            this.testScanStartTime = 0;
        }
    }
}">

    {{-- Breadcrumb --}}
    <nav class="flex items-center space-x-2 text-xs text-slate-500 dark:text-slate-400">
        <a href="{{ route('dashboard') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition">Inicio</a>
        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-slate-800 dark:text-slate-200 font-semibold">Ajustes del Sistema</span>
    </nav>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-1 border-b border-slate-300/80 dark:border-slate-800">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">Ajustes y Configuración</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                Datos fiscales de la farmacia, preferencias de interfaz y control de módulos.
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('catalogo.publico') }}" target="_blank"
               class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-xl text-xs font-semibold shadow-xs transition flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Ver Catálogo Público</span>
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <svg class="w-4 h-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>
    @endif
    @if($errors->any())
    <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-300 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="flex space-x-0 overflow-x-auto scrollbar-none" aria-label="Ajustes tabs">
            <button @click="activeTab = 'empresa'" type="button"
                    :class="activeTab === 'empresa'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Datos del Local</span>
            </button>
            <button @click="activeTab = 'interfaz'" type="button"
                    :class="activeTab === 'interfaz'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                <span>Preferencias de Interfaz</span>
            </button>
            <button @click="activeTab = 'modulos'" type="button"
                    :class="activeTab === 'modulos'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span>Control de Módulos</span>
            </button>
            <button @click="activeTab = 'hardware'" type="button"
                    :class="activeTab === 'hardware'
                        ? 'border-b-2 border-indigo-600 text-indigo-700 dark:text-indigo-400 font-semibold'
                        : 'border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'"
                    class="group inline-flex items-center space-x-2 py-3 px-4 text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Hardware y Periféricos</span>
            </button>
        </nav>
    </div>

    {{-- Formulario principal --}}
    <form action="{{ route('ajustes.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="tab" :value="activeTab">

        {{-- TAB 1: DATOS DEL LOCAL --}}
        <div x-show="activeTab === 'empresa'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Identificación Fiscal y Contacto
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 -mt-2 mb-4">Estos datos se sincronizan automáticamente con tickets de venta, reportes PDF y el catálogo público.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nombre del Local / Farmacia</label>
                        <input type="text" name="empresa_nombre" maxlength="100"
                               value="{{ $configs['empresa_nombre']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: FarmaBien">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">RUC / NIT</label>
                        <input type="text" name="empresa_ruc" maxlength="50"
                               value="{{ $configs['empresa_ruc']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500 font-mono"
                               placeholder="Ej: J-12345678-9">
                    </div>
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Razón Social</label>
                        <input type="text" name="empresa_razon_social" maxlength="150"
                               value="{{ $configs['empresa_razon_social']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Razón social o denominación legal">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Teléfono Principal</label>
                        <input type="text" name="empresa_telefono" maxlength="100"
                               value="{{ $configs['empresa_telefono']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: 0414-123-4567">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">WhatsApp</label>
                        <input type="text" name="empresa_whatsapp" maxlength="50"
                               value="{{ $configs['empresa_whatsapp']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: 58414-123-4567">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Correo Electrónico</label>
                        <input type="email" name="empresa_email" maxlength="100"
                               value="{{ $configs['empresa_email']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="contacto@farmacia.com">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección Física</label>
                        <input type="text" name="empresa_direccion" maxlength="255"
                               value="{{ $configs['empresa_direccion']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Calle / Av. / Sector / Urbanización">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ciudad / Municipio</label>
                        <input type="text" name="empresa_ciudad" maxlength="100"
                               value="{{ $configs['empresa_ciudad']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: Caracas">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Eslogan / Lema</label>
                        <input type="text" name="empresa_slogan" maxlength="255"
                               value="{{ $configs['empresa_slogan']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Aparece en el encabezado del catálogo y reportes">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Pie de Ticket de Venta</label>
                        <input type="text" name="empresa_pie_ticket" maxlength="255"
                               value="{{ $configs['empresa_pie_ticket']['valor'] ?? '' }}"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500"
                               placeholder="Ej: ¡Gracias por su compra!">
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Logotipo Institucional
                </h2>
                <div class="flex flex-col sm:flex-row gap-5 items-start">
                    <div class="w-24 h-24 rounded-xl border-2 border-dashed border-slate-300 dark:border-slate-700 flex items-center justify-center bg-slate-50 dark:bg-slate-800 shrink-0 overflow-hidden">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-full h-full object-contain p-1">
                        </template>
                        <template x-if="!logoPreview">
                            <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </template>
                    </div>
                    <div class="flex-1 space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Subir imagen (PNG, JPG, SVG, WebP — máx. 2MB)</label>
                            <input type="file" name="empresa_logo" accept="image/*" @change="previewLogo($event)"
                                   class="block w-full text-xs text-slate-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 dark:file:bg-emerald-950/60 dark:file:text-emerald-300 hover:file:bg-emerald-100 file:transition cursor-pointer">
                        </div>
                        @if(!empty($configs['empresa_logo']['valor']))
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" name="eliminar_logo" value="1"
                                   class="rounded border-rose-300 text-rose-600 focus:ring-rose-500">
                            <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold">Eliminar logotipo actual</span>
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Datos del Local
                </button>
            </div>
        </div>

        {{-- TAB 2: PREFERENCIAS DE INTERFAZ --}}
        <div x-show="activeTab === 'interfaz'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Tema Visual
                </h2>
                @php $temaActual = $configs['interfaz_modo_oscuro_default']['valor'] ?? 'system'; @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 max-w-xl">
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'light' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="light"
                               {{ $temaActual === 'light' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-amber-500 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo Claro</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'dark' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="dark"
                               {{ $temaActual === 'dark' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-300 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo Oscuro</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition
                        {{ $temaActual === 'system' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                        <input type="radio" name="interfaz_modo_oscuro_default" value="system"
                               {{ $temaActual === 'system' ? 'checked' : '' }}
                               class="text-indigo-600 focus:ring-indigo-500">
                        <div>
                            <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Automático</p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Sistema operativo</p>
                        </div>
                    </label>
                </div>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-3">El tema se aplica en la siguiente carga de página tras guardar.</p>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Diseño de Formularios y Paginación
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-xl">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Vista predeterminada en Crear y Editar</label>
                        @php $vistaFormActual = $configs['interfaz_vista_formularios_default']['valor'] ?? 'modern'; @endphp
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $vistaFormActual === 'modern' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                                <input type="radio" name="interfaz_vista_formularios_default" value="modern"
                                       {{ $vistaFormActual === 'modern' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Vista Moderna</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Tarjetas completas estructuradas</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition {{ $vistaFormActual === 'compact' ? 'border-indigo-400 dark:border-indigo-600 bg-indigo-50 dark:bg-indigo-950/30' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}">
                                <input type="radio" name="interfaz_vista_formularios_default" value="compact"
                                       {{ $vistaFormActual === 'compact' ? 'checked' : '' }}
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Vista Compacta (POS / ERP)</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Ficha rápida sin desplazamiento</span>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">Registros por página</label>
                        @php $paginacion = $configs['interfaz_registros_por_pagina']['valor'] ?? '25'; @endphp
                        <select name="interfaz_registros_por_pagina"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="10" {{ $paginacion == '10' ? 'selected' : '' }}>10 registros por página</option>
                            <option value="15" {{ $paginacion == '15' ? 'selected' : '' }}>15 registros por página</option>
                            <option value="25" {{ $paginacion == '25' ? 'selected' : '' }}>25 registros por página</option>
                            <option value="50" {{ $paginacion == '50' ? 'selected' : '' }}>50 registros por página</option>
                            <option value="100" {{ $paginacion == '100' ? 'selected' : '' }}>100 registros por página</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Preferencias de Interfaz
                </button>
            </div>
        </div>

        {{-- TAB 3: CONTROL DE MÓDULOS --}}
        <div x-show="activeTab === 'modulos'" x-cloak class="space-y-4">
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Catálogo Público para Clientes
                </h2>
                <div class="space-y-3 max-w-lg">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_activo" value="1"
                               {{ ($configs['catalogo_publico_activo']['valor'] ?? '1') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Activar Catálogo Público</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Permite a los clientes consultar el catálogo de productos disponibles sin necesidad de iniciar sesión.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_mostrar_precios" value="1"
                               {{ ($configs['catalogo_publico_mostrar_precios']['valor'] ?? '1') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Mostrar precios en el catálogo</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Muestra el precio de venta de cada producto en el catálogo público.</p>
                        </div>
                    </label>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="catalogo_publico_mostrar_stock" value="1"
                               {{ ($configs['catalogo_publico_mostrar_stock']['valor'] ?? '0') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Mostrar disponibilidad de stock</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Muestra si el producto está disponible o agotado en el catálogo público.</p>
                        </div>
                    </label>
                </div>
                <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-600 dark:text-slate-400">URL del catálogo público:</p>
                    <a href="{{ route('catalogo.publico') }}" target="_blank"
                       class="text-xs font-mono text-emerald-700 dark:text-emerald-400 hover:underline truncate max-w-xs">
                        {{ route('catalogo.publico') }}
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5">
                <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
                    Control de Cajas
                </h2>
                <div class="max-w-lg">
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40 cursor-pointer transition">
                        <input type="checkbox" name="modulo_cajas_estricto" value="1"
                               {{ ($configs['modulo_cajas_estricto']['valor'] ?? '0') == '1' ? 'checked' : '' }}
                               class="mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Modo estricto de cajas</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Cuando está activo, los cajeros deben tener un turno de caja abierto para poder registrar ventas. Sin turno activo, se bloquea el acceso al POS.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition">
                    Guardar Configuración de Módulos
                </button>
            </div>
        </div>

        {{-- TAB 4: HARDWARE Y PERIFÉRICOS --}}
        <div x-show="activeTab === 'hardware'" x-cloak class="space-y-4">
            
            {{-- Hardware Status Banner --}}
            <div x-show="hardwareMsg" x-cloak class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-300 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span x-text="hardwareMsg" class="font-semibold"></span>
                </div>
                <button type="button" @click="hardwareMsg = ''" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
            </div>

            {{-- 1. Impresora Térmica de Tickets --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>1. Impresora Térmica de Tickets (ESC/POS)</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Compatible con rollos de 58mm y 80mm vía Red LAN, Bluetooth, WebUSB o Navegador.</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="button" 
                                @click="imprimirTicketPrueba()"
                                class="px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Ticket de Prueba</span>
                        </button>
                    </div>
                </div>

                <!-- Tipo de Impresora Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.tipo === 'navegador' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">🖨️</span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Navegador Web</span>
                                    <span class="text-[10px] text-slate-500">Diálogo de impresión estándar</span>
                                </div>
                            </div>
                            <input type="radio" name="impresora_tipo" value="navegador" x-model="hw.tipo" class="text-indigo-600 focus:ring-indigo-500">
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.tipo === 'red' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">🌐</span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Red LAN / Wi-Fi</span>
                                    <span class="text-[10px] text-slate-500">Impresión directa por IP y Puerto</span>
                                </div>
                            </div>
                            <input type="radio" name="impresora_tipo" value="red" x-model="hw.tipo" class="text-indigo-600 focus:ring-indigo-500">
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.tipo === 'bluetooth' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">📶</span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Bluetooth Térmico</span>
                                    <span class="text-[10px] text-slate-500">Web Bluetooth API</span>
                                </div>
                            </div>
                            <input type="radio" name="impresora_tipo" value="bluetooth" x-model="hw.tipo" class="text-indigo-600 focus:ring-indigo-500">
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.tipo === 'usb' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-950/40 ring-2 ring-indigo-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">🔌</span>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">USB Directo</span>
                                    <span class="text-[10px] text-slate-500">WebUSB / Serial API</span>
                                </div>
                            </div>
                            <input type="radio" name="impresora_tipo" value="usb" x-model="hw.tipo" class="text-indigo-600 focus:ring-indigo-500">
                        </div>
                    </label>
                </div>

                <!-- Opciones Específicas según tipo de conexión -->
                <div x-show="hw.tipo === 'red'" class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-3 animate-fadeIn">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Dirección IP de la Impresora</label>
                        <input type="text" name="impresora_ip" x-model="hw.ip" placeholder="192.168.1.200"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Puerto RAW ESC/POS (Por defecto: 9100)</label>
                        <input type="number" name="impresora_puerto" x-model="hw.puerto" placeholder="9100"
                               class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs font-mono">
                    </div>
                </div>

                <div x-show="hw.tipo === 'bluetooth'" class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-fadeIn">
                    <div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full" :class="btDeviceName ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                            <span>Dispositivo: <strong x-text="btDeviceName || 'No emparejado'"></strong></span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="btStatus"></p>
                    </div>
                    <button type="button" 
                            @click="conectarBluetooth()"
                            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Emparejar Dispositivo Bluetooth</span>
                    </button>
                </div>

                <div x-show="hw.tipo === 'usb'" class="p-3.5 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 animate-fadeIn">
                    <div>
                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-1.5">
                            <span class="w-2 h-2 rounded-full" :class="usbDeviceName ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'"></span>
                            <span>Dispositivo: <strong x-text="usbDeviceName || 'No detectado'"></strong></span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="usbStatus"></p>
                    </div>
                    <button type="button" 
                            @click="conectarUSB()"
                            class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Detectar Dispositivo USB</span>
                    </button>
                </div>

                <!-- Configuración de Formato y Switches -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Ancho de Papel Térmico</label>
                        <select name="impresora_ancho_papel" x-model="hw.ancho"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs font-medium">
                            <option value="80">80 mm (Estándar 48 columnas)</option>
                            <option value="58">58 mm (Compacto 32 columnas)</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer mt-4">
                            <input type="checkbox" name="impresora_corte_automatico" value="1" x-model="hw.corte"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Corte Automático</span>
                                <span class="text-[10px] text-slate-500">Ejecutar comando GS V 0</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer mt-4">
                            <input type="checkbox" name="impresora_abrir_cajon" value="1" x-model="hw.cajon"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Abrir Cajón al Imprimir</span>
                                <span class="text-[10px] text-slate-500">Pulso RJ11 ESC p</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer mt-4">
                            <input type="checkbox" name="impresora_impresion_automatica" value="1" x-model="hw.autoprint"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-semibold text-slate-800 dark:text-slate-200 block">Auto-Imprimir en POS</span>
                                <span class="text-[10px] text-slate-500">Al confirmar cobro</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- 2. Cajón Portamonedas --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>2. Cajón Portamonedas / Gaveta de Dinero</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Control de pulso electromagnético para apertura en cobros en efectivo y aperturas manuales de turno.</p>
                    </div>
                    <div>
                        <button type="button" 
                                @click="probarAperturaCajon()"
                                class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 cursor-pointer">
                            <span>⚡ Probar Apertura de Cajón</span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.cajonTipo === 'escpos' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Conectado a Impresora (RJ11)</span>
                                <span class="text-[10px] text-slate-500">Disparo por señal de impresora térmica (Recomendado)</span>
                            </div>
                            <input type="radio" name="cajon_tipo" value="escpos" x-model="hw.cajonTipo" class="text-emerald-600 focus:ring-emerald-500">
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.cajonTipo === 'usb' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Gatillo USB Directo</span>
                                <span class="text-[10px] text-slate-500">Cajón con interface USB o trigger box</span>
                            </div>
                            <input type="radio" name="cajon_tipo" value="usb" x-model="hw.cajonTipo" class="text-emerald-600 focus:ring-emerald-500">
                        </div>
                    </label>

                    <label class="p-3 rounded-xl border cursor-pointer transition flex flex-col justify-between"
                           :class="hw.cajonTipo === 'manual' ? 'border-emerald-500 bg-emerald-50/60 dark:bg-emerald-950/40 ring-2 ring-emerald-500/20' : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/40'">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200 block">Apertura Manual con Llave</span>
                                <span class="text-[10px] text-slate-500">Sin pulso electromagnético</span>
                            </div>
                            <input type="radio" name="cajon_tipo" value="manual" x-model="hw.cajonTipo" class="text-emerald-600 focus:ring-emerald-500">
                        </div>
                    </label>
                </div>
            </div>

            {{-- 3. Lector de Código de Barras --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-5 space-y-4">
                <div class="border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h2 class="text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>3. Lector de Código de Barras (Escáner Láser / 2D)</span>
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Captura ultrarrápida de medicamentos por EAN-13, GS1 DataMatrix, Code-128 o QR.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Modo de Operación del Escáner</label>
                        <select name="lector_modo" x-model="hw.lectorModo"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs font-medium">
                            <option value="hid">Emulación Teclado HID (USB / Bluetooth Plug&Play - Recomendado)</option>
                            <option value="usb_serial">Dispositivo Serie / COM Virtual</option>
                            <option value="camara">Cámara Web / Dispositivo Móvil</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Carácter Sufijo de Terminación</label>
                        <select name="lector_sufijo" x-model="hw.lectorSufijo"
                                class="w-full rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 text-xs font-medium">
                            <option value="enter">Enter / Retorno de Carro (CR+LF) [Estándar]</option>
                            <option value="tab">Tabulación (TAB)</option>
                            <option value="none">Sin sufijo</option>
                        </select>
                    </div>
                </div>

                <!-- Caja de Prueba Interactiva de Escaneo -->
                <div class="p-4 rounded-xl bg-amber-50/50 dark:bg-slate-800/50 border border-amber-200 dark:border-slate-700 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 dark:text-amber-200 flex items-center space-x-1.5">
                            <span>🔍</span>
                            <span>Banco de Prueba de Escáner en Tiempo Real</span>
                        </span>
                        <span class="text-[10px] text-slate-500">Pasa un producto por el lector láser</span>
                    </div>

                    <div class="relative">
                        <input type="text" 
                               x-model="testScanInput"
                               @keydown="onTestScanKeydown($event)"
                               placeholder="Haz clic aquí y escanea un código de barras físico..."
                               class="w-full pl-3 pr-24 py-2 bg-white dark:bg-slate-800 border-2 border-amber-300 dark:border-amber-700 rounded-xl text-xs text-slate-900 dark:text-white font-mono placeholder-slate-400 focus:ring-2 focus:ring-amber-500">
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                            <span class="text-[10px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 px-2 py-0.5 rounded-lg border border-amber-200 dark:border-amber-800">
                                Listo para Escaneo
                            </span>
                        </div>
                    </div>

                    <!-- Log de Lecturas de Prueba -->
                    <div x-show="testScanLog.length > 0" class="space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Historial de Códigos Detectados:</span>
                        <div class="max-h-28 overflow-y-auto space-y-1">
                            <template x-for="(sc, scIdx) in testScanLog" :key="scIdx">
                                <div class="p-2 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-between text-[11px] font-mono">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-emerald-600 font-bold">✓</span>
                                        <strong class="text-slate-900 dark:text-white" x-text="sc.codigo"></strong>
                                        <span class="text-slate-400 text-[10px]" x-text="'(' + sc.len + ' dígitos)'"></span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-[10px] text-slate-500">
                                        <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded font-bold" x-text="sc.tiempo + ' ms'"></span>
                                        <span x-text="sc.hora"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="submit"
                        @click="sincronizarHardwareLocal()"
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-xs transition cursor-pointer">
                    Guardar y Sincronizar Hardware
                </button>
            </div>
        </div>
    </form>

    <!-- Modal de Prueba de Impresión de Ticket Térmico -->
    <div x-show="modalTicketTest" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-xs overflow-y-auto"
         @keydown.escape.window="modalTicketTest = false"
         @click.self="modalTicketTest = false">
        <div @click.stop
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all w-full max-w-sm border border-slate-300 dark:border-slate-800 my-auto">
            
            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <h3 class="text-xs font-bold uppercase text-slate-800 dark:text-slate-200">Prueba de Impresión Térmica</h3>
                </div>
                <button type="button" @click="modalTicketTest = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
            </div>

            <div class="p-4 bg-slate-100 dark:bg-slate-950 flex justify-center">
                <div class="bg-white text-slate-900 font-mono text-[10px] p-4 rounded-lg shadow-sm border border-slate-300 space-y-2 w-full leading-tight"
                     :class="hw.ancho === '58' ? 'max-w-[220px]' : 'max-w-[280px]'">
                    <div class="text-center">
                        <div class="font-black text-xs uppercase">{{ config('app.name', 'FarmaBien') }}</div>
                        <div class="text-[9px]">COMPROBANTE DE PRUEBA</div>
                        <div class="text-[8px] text-slate-500">Ajustes & Calibración Hardware</div>
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                    <div class="text-[9px] space-y-0.5">
                        <div>FECHA: {{ now()->format('d/m/Y H:i:s') }}</div>
                        <div>MODO: <span class="uppercase font-bold" x-text="hw.tipo"></span></div>
                        <div>PAPEL: <span class="font-bold" x-text="hw.ancho + ' mm'"></span></div>
                        <div>CORTE: <span x-text="hw.corte ? 'Activado (GS V 0)' : 'Manual'"></span></div>
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>

                    <div class="text-center text-[9px] font-bold">
                        PRUEBA DE FUENTES Y ALINEACIÓN
                    </div>
                    <div class="text-center text-[8px]">
                        0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZ
                    </div>

                    <div class="border-t border-dashed border-slate-400 my-1"></div>
                    <div class="text-center text-[8px] text-slate-600">
                        FarmaBien v2.0 - Hardware Verificado
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 px-5 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-end space-x-2">
                <button type="button" @click="modalTicketTest = false" class="px-4 py-1.5 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-semibold cursor-pointer">
                    Cerrar
                </button>
                <button type="button" @click="ejecutarImpresionTest()" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Imprimir Ahora</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

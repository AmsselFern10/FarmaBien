<x-guest-layout>
    <!-- Left Hero Column (Branding & Features) -->
    <div class="hidden lg:flex lg:col-span-6 bg-gradient-to-br from-emerald-800 via-teal-900 to-slate-950 p-10 flex-col justify-between relative overflow-hidden">
        <!-- Background Glows -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>

        <div>
            <!-- Brand Header -->
            <div class="flex items-center space-x-3.5 mb-8">
                <div class="w-12 h-12 bg-white rounded-2xl shadow-xl flex items-center justify-center text-emerald-600 font-black text-2xl shrink-0">
                    +
                </div>
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Farma<span class="text-emerald-400">Bien</span></h1>
                    <p class="text-emerald-300 text-xs font-bold uppercase tracking-wider">Sistema de Gestión de Farmacia</p>
                </div>
            </div>

            <!-- Value Propositions -->
            <div class="space-y-6 mt-10">
                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center shrink-0 text-emerald-300 text-lg shadow-inner">
                        ⚡
                    </div>
                    <div>
                        <h3 class="text-white text-base font-bold">Punto de Venta Rápido (POS)</h3>
                        <p class="text-emerald-100/90 text-sm mt-0.5 leading-relaxed">
                            Búsqueda instantánea de medicamentos, lectura por código de barras y cobro en segundos.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center shrink-0 text-emerald-300 text-lg shadow-inner">
                        ⏳
                    </div>
                    <div>
                        <h3 class="text-white text-base font-bold">Control de Lotes y Vencimientos</h3>
                        <p class="text-emerald-100/90 text-sm mt-0.5 leading-relaxed">
                            Alertas automáticas de caducidad para rotar medicamentos a tiempo y evitar pérdidas.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center shrink-0 text-emerald-300 text-lg shadow-inner">
                        📦
                    </div>
                    <div>
                        <h3 class="text-white text-base font-bold">Venta Fraccionada y Kardex</h3>
                        <p class="text-emerald-100/90 text-sm mt-0.5 leading-relaxed">
                            Vende por caja, blíster o unidad con conversión automática y registro de movimientos.
                        </p>
                    </div>
                </div>

                <div class="flex items-start space-x-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center shrink-0 text-emerald-300 text-lg shadow-inner">
                        📋
                    </div>
                    <div>
                        <h3 class="text-white text-base font-bold">Validación de Recetas Médicas</h3>
                        <p class="text-emerald-100/90 text-sm mt-0.5 leading-relaxed">
                            Registro de cédula médica y control de dispensación de medicamentos controlados.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-sm font-semibold text-emerald-200/80 pt-6 border-t border-emerald-600/40 flex justify-between items-center">
            <span>FarmaBien &bull; Control Farmacéutico Seguro</span>
            <span class="inline-flex items-center text-emerald-300 font-bold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse mr-2"></span>
                Operativo
            </span>
        </div>
    </div>

    <!-- Right Column (Form) -->
    {{-- lockoutUntil viene del controlador (ms epoch), o null si no hay bloqueo activo --}}
    <div
        x-data="loginForm({{ $lockoutUntil ?? 'null' }})"
        x-init="init()"
        class="col-span-12 lg:col-span-6 p-8 sm:p-12 flex flex-col justify-between bg-slate-900 border-l border-slate-800"
    >

        <!-- Mobile Logo -->
        <div class="lg:hidden flex items-center space-x-3 mb-6">
            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white font-black text-xl shadow">
                +
            </div>
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight">Farma<span class="text-emerald-400">Bien</span></h2>
                <p class="text-slate-400 text-xs font-semibold">Sistema de Farmacia</p>
            </div>
        </div>

        <div>
            <div class="mb-6">
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Iniciar Sesión</h2>
                <p class="text-slate-400 text-sm font-medium mt-1">Ingresa tus credenciales para acceder al sistema</p>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Lockout Banner -->
            <div
                x-show="locked"
                x-cloak
                class="mb-4 p-4 rounded-xl bg-red-950/70 border border-red-700/60 flex items-start space-x-3"
                role="alert"
            >
                <span class="text-red-400 text-xl mt-0.5">🔒</span>
                <div>
                    <p class="text-red-300 font-bold text-sm">Acceso bloqueado temporalmente</p>
                    <p class="text-red-400/80 text-xs mt-0.5">
                        Demasiados intentos fallidos. Puedes volver a intentarlo en
                        <span class="font-mono font-bold text-red-300" x-text="countdown"></span>.
                    </p>
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4" @submit.prevent="submitForm($event)">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">
                        Correo Electrónico
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                        </div>
                        <input id="email"
                               name="email"
                               type="email"
                               x-model="email"
                               required
                               autofocus
                               autocomplete="username"
                               placeholder="correo@farmabien.com"
                               :disabled="locked"
                               :class="locked ? 'opacity-50 cursor-not-allowed' : ''"
                               class="block w-full pl-11 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium transition" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <!-- Password Input -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 transition">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input id="password"
                               name="password"
                               type="password"
                               x-model="password"
                               required
                               autocomplete="current-password"
                               placeholder="••••••••"
                               :disabled="locked"
                               :class="locked ? 'opacity-50 cursor-not-allowed' : ''"
                               class="block w-full pl-11 pr-4 py-3 bg-slate-800 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium transition" />
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me"
                               type="checkbox"
                               name="remember"
                               :disabled="locked"
                               class="rounded bg-slate-800 border-slate-700 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-slate-900">
                        <span class="ms-2 text-xs font-semibold text-slate-300">Mantener sesión activa</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button
                        type="submit"
                        :disabled="locked || loading"
                        :class="{
                            'opacity-50 cursor-not-allowed bg-slate-700 shadow-none': locked,
                            'bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-lg shadow-emerald-600/30': !locked && !loading,
                            'bg-emerald-700 cursor-wait': loading && !locked
                        }"
                        class="w-full py-3.5 px-4 text-white font-extrabold rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition flex items-center justify-center space-x-2 text-sm"
                    >
                        <!-- Estado bloqueado -->
                        <template x-if="locked">
                            <span class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <span>Bloqueado — espera <span class="font-mono" x-text="countdown"></span></span>
                            </span>
                        </template>
                        <!-- Estado cargando -->
                        <template x-if="!locked && loading">
                            <span class="flex items-center space-x-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span>Verificando...</span>
                            </span>
                        </template>
                        <!-- Estado normal -->
                        <template x-if="!locked && !loading">
                            <span class="flex items-center space-x-2">
                                <span>Ingresar al Sistema</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </template>
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Access Roles for Testing -->
        <div class="mt-8 pt-6 border-t border-slate-800">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                Selecciona un rol para probar (Demo):
            </p>
            <div class="grid grid-cols-2 gap-2.5">
                <button type="button"
                        @click="if(!locked){ email='admin@farmabien.com'; password='password'; }"
                        :class="locked ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-700/80'"
                        class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold text-left flex items-center justify-between transition">
                    <span>👑 Administrador</span>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded">Rellenar</span>
                </button>
                <button type="button"
                        @click="if(!locked){ email='farmaceutico@farmabien.com'; password='password'; }"
                        :class="locked ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-700/80'"
                        class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold text-left flex items-center justify-between transition">
                    <span>💊 Farmacéutico</span>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded">Rellenar</span>
                </button>
                <button type="button"
                        @click="if(!locked){ email='cajero@farmabien.com'; password='password'; }"
                        :class="locked ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-700/80'"
                        class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold text-left flex items-center justify-between transition">
                    <span>🧾 Cajero / POS</span>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded">Rellenar</span>
                </button>
                <button type="button"
                        @click="if(!locked){ email='inventario@farmabien.com'; password='password'; }"
                        :class="locked ? 'opacity-40 cursor-not-allowed' : 'hover:bg-slate-700/80'"
                        class="p-2.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 text-xs font-bold text-left flex items-center justify-between transition">
                    <span>📦 Inventario</span>
                    <span class="text-[10px] font-bold text-emerald-400 bg-emerald-950/60 px-1.5 py-0.5 rounded">Rellenar</span>
                </button>
            </div>
        </div>
    </div>

    <script>
    /**
     * Alpine JS component for the login form.
     *
     * Lockout logic:
     *  - The server passes lockoutUntil (ms epoch) when the IP is already blocked.
     *  - On a 429 response the form reads the lockout_until error from the session
     *    and saves it to localStorage['fb_lockout'] so the state persists across reloads
     *    on THIS device only. Other devices are unaffected.
     *  - Every second the countdown is recalculated. When it reaches 0 the button re-enables.
     */
    function loginForm(serverLockoutUntil) {
        return {
            email:     '{{ old('email') }}',
            password:  '',
            locked:    false,
            loading:   false,
            countdown: '',
            _timer:    null,

            init() {
                const LS_KEY = 'fb_lockout';

                // 1. Server says this IP is blocked (on page load / redirect)
                if (serverLockoutUntil && serverLockoutUntil > Date.now()) {
                    localStorage.setItem(LS_KEY, serverLockoutUntil);
                }

                // 2. Check localStorage (persists across F5 on same device)
                const stored = parseInt(localStorage.getItem(LS_KEY) || '0', 10);
                if (stored > Date.now()) {
                    this._startLockout(stored);
                } else {
                    localStorage.removeItem(LS_KEY);
                }

                // 3. If the server returned a lockout_until validation error (422→session),
                //    it will be embedded in the page as a PHP var below.
                @if ($errors->has('lockout_until'))
                const phpLockout = {{ $errors->first('lockout_until') }};
                if (phpLockout && phpLockout > Date.now()) {
                    localStorage.setItem(LS_KEY, phpLockout);
                    this._startLockout(phpLockout);
                }
                @endif
            },

            _startLockout(until) {
                this.locked = true;
                this._tick(until);
                this._timer = setInterval(() => {
                    if (! this._tick(until)) {
                        clearInterval(this._timer);
                        this.locked = false;
                        this.countdown = '';
                        localStorage.removeItem('fb_lockout');
                    }
                }, 1000);
            },

            _tick(until) {
                const remaining = Math.ceil((until - Date.now()) / 1000);
                if (remaining <= 0) return false;
                const m = String(Math.floor(remaining / 60)).padStart(2, '0');
                const s = String(remaining % 60).padStart(2, '0');
                this.countdown = `${m}:${s}`;
                return true;
            },

            submitForm(event) {
                if (this.locked) return;
                this.loading = true;
                // Submit the native form
                event.target.submit();
            },
        };
    }
    </script>
</x-guest-layout>

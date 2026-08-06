<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ContaFit Agenda') }} | Agenda Corporativa</title>

    <!-- ÍCONO EN LA PESTAÑA DEL NAVEGADOR (FAVICON ISOTIPO CONTAFIT) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><circle cx='50' cy='50' r='42' fill='none' stroke='%2300A896' stroke-width='8'/><rect x='34' y='45' width='8' height='25' fill='%2300A896' rx='2'/><rect x='46' y='35' width='8' height='35' fill='%2300A896' rx='2'/><rect x='58' y='48' width='8' height='22' fill='%2300A896' rx='2'/><path d='M 22 65 L 42 45 L 55 56 L 78 26 L 62 26 L 78 26 L 78 42' fill='none' stroke='%230B2545' stroke-width='9' stroke-linecap='round' stroke-linejoin='round'/></svg>">

    <!-- FUENTES OFICIALES (SANS-SERIF MODERNA CORPORATIVA) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --color-navy-primary: #0B2545;
            --color-navy-dark: #0A192F;
            --color-teal-growth: #00A896;
            --color-teal-hover: #028090;
            --color-bg-clean: #0F172A;
            --color-card-bg: #1E293B;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .card-panel {
            background-color: #1E293B;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-panel-active {
            border-color: rgba(0, 168, 150, 0.4);
        }
        .modal-bg {
            background-color: rgba(10, 25, 47, 0.85);
            backdrop-filter: blur(8px);
        }
        .modal-box {
            background-color: #1E293B;
            border: 1px solid rgba(0, 168, 150, 0.3);
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.6); }
        ::-webkit-scrollbar-thumb { background: rgba(0, 168, 150, 0.4); border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0, 168, 150, 0.7); }
    </style>
</head>
<body class="font-sans text-slate-100 bg-[#0B1320] min-h-screen">

    <!-- CONTENEDOR TOAST DE NOTIFICACIONES CORPORATIVAS CONTAFIT (UI/UX) -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[100] flex flex-col gap-2.5 pointer-events-none max-w-sm w-full px-4"></div>

    <!-- BARRA SUPERIOR CORPORATIVA CON LOGOTIPO CONTAFIT -->
    <nav class="w-full bg-[#0B2545] border-b border-[#00A896]/30 px-6 py-3.5 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3.5">
            <!-- LOGOTIPO VECTORIAL ISOTIPO CONTAFIT (FLECHA AZUL ASCENDENTE Y CÍRCULO TURQUESA) -->
            <div class="h-10 w-10 rounded-2xl bg-white flex items-center justify-center p-1.5 shadow-md border border-[#00A896]/40">
                <svg viewBox="0 0 100 100" class="w-full h-full">
                    <!-- Círculo Turquesa -->
                    <circle cx="50" cy="50" r="42" fill="none" stroke="#00A896" stroke-width="8" />
                    <!-- Barras de Gráfico Turquesa -->
                    <rect x="34" y="45" width="8" height="25" fill="#00A896" rx="2" />
                    <rect x="46" y="35" width="8" height="35" fill="#00A896" rx="2" />
                    <rect x="58" y="48" width="8" height="22" fill="#00A896" rx="2" />
                    <!-- Flecha Ascendente Azul Principal -->
                    <path d="M 22 65 L 42 45 L 55 56 L 78 26 L 62 26 L 78 26 L 78 42" fill="none" stroke="#0B2545" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-extrabold text-lg text-white tracking-tight">CONTAFIT</span>
                    <span class="text-xs font-semibold text-[#00A896] uppercase tracking-widest bg-[#00A896]/15 px-2 py-0.5 rounded-md border border-[#00A896]/30">Contadores</span>
                </div>
                <p class="text-[10px] text-slate-300 font-medium tracking-wide">MACHALA - ECUADOR</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 relative">
            <div id="authHeader" class="flex items-center gap-3"></div>
        </div>
    </nav>

    <!-- DASHBOARD PRINCIPAL -->
    <div class="p-4 lg:p-6">
        <div class="mx-auto flex min-h-[calc(100vh-6.5rem)] max-w-[1600px] flex-col gap-5 xl:flex-row">
            
            <!-- SIDEBAR IZQUIERDO: DASHBOARD DE TAREAS Y RECORDATORIOS DEL DÍA -->
            <aside class="w-full xl:w-[380px] flex flex-col gap-5 rounded-3xl card-panel p-5 shadow-xl border border-white/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#00A896]">DASHBOARD DIARIO</p>
                        <h1 class="mt-0.5 text-2xl font-extrabold text-white">Tareas del Día</h1>
                    </div>
                </div>

                <button onclick="openCreateModal()" title="Crear un nuevo evento, tarea o recordatorio en la agenda" class="w-full flex items-center justify-center gap-2 rounded-xl bg-[#00A896] hover:bg-[#028090] py-3 text-xs font-bold text-slate-950 hover:text-white transition-all shadow-lg shadow-[#00A896]/20">
                    <span class="text-base font-black">+</span> Crear Evento / Tarea
                </button>

                <!-- LISTA CHECKLIST DE TAREAS Y RECORDATORIOS DE HOY -->
                <section class="flex-1 overflow-y-auto max-h-[460px] space-y-3">
                    <div class="flex items-center justify-between text-xs border-b border-white/10 pb-2.5">
                        <h2 class="font-bold text-white">Pendientes de Hoy</h2>
                        <div class="flex items-center gap-2">
                            <label class="text-[11px] text-slate-300 flex items-center gap-1.5 cursor-pointer" title="Mostrar u ocultar tareas ya completadas">
                                <input type="checkbox" id="showCompletedToggle" onchange="fetchTodayTasks()" class="rounded border-white/20 text-[#00A896] focus:ring-[#00A896]">
                                Ver completadas
                            </label>
                            <span id="taskCountBadge" class="rounded-full bg-[#00A896]/20 text-[#00A896] border border-[#00A896]/40 px-2.5 py-0.5 text-[11px] font-bold">0 pendientes</span>
                        </div>
                    </div>

                    <div id="todayTaskList" class="space-y-2">
                        <div class="p-4 text-center text-slate-400 text-xs">Cargando tareas del día...</div>
                    </div>
                </section>

                <!-- PRÓXIMOS FERIADOS DE ECUADOR (PRÓXIMOS 6 MESES) -->
                <section class="rounded-2xl border border-[#00A896]/30 bg-[#0B2545]/40 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#00A896]">🇪🇨 PRÓXIMOS FERIADOS</p>
                    </div>
                    <div id="holidaysWidget" class="text-xs text-slate-200 space-y-2">
                        <div class="text-slate-400 text-[11px]">Cargando próximos feriados...</div>
                    </div>
                </section>
            </aside>

            <!-- CALENDARIO PRINCIPAL Y FILTROS -->
            <main class="flex-1 flex flex-col gap-4 rounded-3xl card-panel p-5 shadow-xl min-w-0 border border-white/10">
                
                <!-- CONTROLES Y BARRA DE FILTROS EN TIEMPO REAL -->
                <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between border-b border-white/10 pb-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-[#00A896]">CALENDARIO Y EVENTOS</p>
                        <div class="mt-1 flex items-center gap-3">
                            <button onclick="navigateMonth(-1)" title="Mes anterior" class="h-9 w-9 rounded-xl bg-white/5 hover:bg-[#00A896]/20 text-white font-bold transition flex items-center justify-center border border-white/10">‹</button>
                            <h2 id="calendarTitle" class="text-2xl font-extrabold text-white min-w-[240px] text-center">Mes {{ date('Y') }}</h2>
                            <button onclick="navigateMonth(1)" title="Mes siguiente" class="h-9 w-9 rounded-xl bg-white/5 hover:bg-[#00A896]/20 text-white font-bold transition flex items-center justify-center border border-white/10">›</button>
                        </div>
                    </div>

                    <!-- FILTROS Y BÚSQUEDA EN TIEMPO REAL -->
                    <div class="flex flex-wrap items-center gap-3">
                        <input type="text" id="searchInput" oninput="applyFilters()" title="Buscar eventos por título, nota o descripción" placeholder="🔎 Buscar evento o nota..." class="rounded-xl border border-white/10 bg-[#0B1320] px-3.5 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-[#00A896] focus:ring-1 focus:ring-[#00A896] min-w-[200px]">

                        <select id="typeFilter" onchange="applyFilters()" title="Filtrar eventos por categoría" class="rounded-xl border border-white/10 bg-[#0B2545] px-3.5 py-2 text-xs text-white focus:outline-none focus:border-[#00A896]">
                            <option value="">Todas las Categorías</option>
                            <option value="tarea">📝 Tarea</option>
                            <option value="recordatorio">⏰ Recordatorio</option>
                            <option value="fecha_importante">📌 Fecha Importante</option>
                        </select>

                        <select id="statusFilter" onchange="applyFilters()" title="Filtrar eventos por estado de avance" class="rounded-xl border border-white/10 bg-[#0B2545] px-3.5 py-2 text-xs text-white focus:outline-none focus:border-[#00A896]">
                            <option value="">Todos los Estados</option>
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="en_progreso">🚀 En Progreso</option>
                            <option value="completada">✅ Completada</option>
                        </select>
                    </div>
                </header>

                <!-- GRID DEL CALENDARIO MENSUAL -->
                <section class="flex-1 flex flex-col">
                    <div class="grid grid-cols-7 gap-2 pb-2 text-center text-xs font-bold text-slate-300 uppercase tracking-wider">
                        <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                    </div>

                    <div id="calendarGrid" class="grid grid-cols-7 gap-2 flex-1 overflow-y-auto max-h-[580px]">
                        <!-- Renderizado JS -->
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- MODAL DE DETALLE DE DÍA Y GESTIÓN DE EVENTOS -->
    <div id="dayViewModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-xl rounded-3xl modal-box p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <h3 id="dayViewTitle" class="text-base font-extrabold text-white">📅 Eventos del Día</h3>
                    <p class="text-xs text-slate-300">Detalles de eventos para la fecha seleccionada</p>
                </div>
                <button onclick="closeDayViewModal()" title="Cerrar ventana" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <div id="dayViewContent" class="flex-1 overflow-y-auto space-y-3 pr-1">
                <!-- Carga dinámica JS de eventos y feriados del día -->
            </div>

            <div class="border-t border-white/10 pt-3 flex justify-end gap-3">
                <button onclick="closeDayViewModal()" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-xs text-slate-300 hover:bg-white/10">Cerrar</button>
                <button id="addEventOnDayBtn" onclick="openCreateModalFromDayView()" class="px-4 py-2 rounded-xl bg-[#00A896] hover:bg-[#028090] text-xs font-bold text-slate-950 hover:text-white transition shadow-md">+ Añadir Evento en esta Fecha</button>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMACIÓN BORRADO DE EVENTO RECURRENTE -->
    <div id="deleteRecurringModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-extrabold text-white">Eliminar Evento</h3>
                <button onclick="closeDeleteRecurringModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-300">¿Qué deseas eliminar de la agenda?</p>

            <div class="space-y-2.5 pt-1">
                <button onclick="confirmDeleteRecurringInstance()" class="w-full py-3 px-4 rounded-xl border border-[#00A896]/40 bg-[#0B2545] hover:bg-[#00A896]/20 text-white text-xs font-bold text-left transition flex items-center justify-between shadow-md">
                    <span>1. Eliminar solo en esta fecha</span>
                    <span class="text-[#00A896] font-bold">→</span>
                </button>
                <button onclick="confirmDeleteRecurringSeries()" class="w-full py-3 px-4 rounded-xl border border-red-500/40 bg-red-950/30 hover:bg-red-900/40 text-red-200 text-xs font-bold text-left transition flex items-center justify-between shadow-md">
                    <span>2. Eliminar todas las repeticiones</span>
                    <span class="text-red-400 font-bold">→</span>
                </button>
            </div>

            <div class="pt-2 flex justify-end">
                <button onclick="closeDeleteRecurringModal()" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-xs text-slate-300 font-bold hover:bg-white/10">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMACIÓN CIERRE DE SESIÓN CORPORATIVO -->
    <div id="confirmLogoutModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-sm rounded-3xl modal-box p-6 shadow-2xl space-y-4 text-center">
            <div class="h-12 w-12 rounded-2xl bg-[#0B2545] border border-[#00A896]/40 flex items-center justify-center text-2xl mx-auto shadow-md">
                🚪
            </div>
            <div>
                <h3 class="text-base font-extrabold text-white">¿Cerrar Sesión?</h3>
                <p class="text-xs text-slate-300 mt-1">¿Estás seguro de que quieres cerrar la sesión?</p>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeConfirmLogoutModal()" class="flex-1 py-2.5 rounded-xl border border-white/10 bg-white/5 text-slate-300 text-xs font-bold hover:bg-white/10 transition">No, Cancelar</button>
                <button onclick="confirmLogout()" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 text-white text-xs font-bold transition shadow-md">Sí, Cerrar Sesión</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE AUTENTICACIÓN OBLIGATORIO AL INICIAR (LOGIN / REGISTRO) -->
    <div id="authModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <!-- HEADER LOGO DENTRO DEL LOGIN -->
            <div class="flex flex-col items-center justify-center text-center pt-2 pb-1">
                <div class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center p-2 shadow-md border border-[#00A896]/40 mb-2">
                    <svg viewBox="0 0 100 100" class="w-full h-full">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="#00A896" stroke-width="8" />
                        <rect x="34" y="45" width="8" height="25" fill="#00A896" rx="2" />
                        <rect x="46" y="35" width="8" height="35" fill="#00A896" rx="2" />
                        <rect x="58" y="48" width="8" height="22" fill="#00A896" rx="2" />
                        <path d="M 22 65 L 42 45 L 55 56 L 78 26 L 62 26 L 78 26 L 78 42" fill="none" stroke="#0B2545" stroke-width="9" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h2 class="text-xl font-extrabold text-white">CONTAFIT CONTADORES</h2>
                <p class="text-[10px] text-[#00A896] font-bold tracking-widest">MACHALA - ECUADOR</p>
            </div>

            <div class="flex border-b border-white/10 mb-4">
                <button id="tabLoginBtn" onclick="switchAuthTab('login')" class="flex-1 py-3 text-center text-xs font-bold text-[#00A896] border-b-2 border-[#00A896]">Iniciar Sesión</button>
                <button id="tabRegisterBtn" onclick="switchAuthTab('register')" class="flex-1 py-3 text-center text-xs font-bold text-slate-400">Registrarse</button>
            </div>

            <!-- LOGIN FORM -->
            <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off" class="space-y-4">
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Correo Electrónico *</label>
                    <input type="email" id="loginEmail" required placeholder="tuemail@ejemplo.com" autocomplete="off" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-3 text-xs text-white focus:outline-none focus:border-[#00A896]">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Contraseña *</label>
                    <input type="password" id="loginPassword" required placeholder="••••••••" autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-3 text-xs text-white focus:outline-none focus:border-[#00A896]">
                </div>
                <div id="loginError" class="text-xs text-red-400 hidden"></div>
                <button type="submit" class="w-full py-3 rounded-xl bg-[#00A896] hover:bg-[#028090] font-bold text-slate-950 hover:text-white text-xs transition shadow-lg">Ingresar</button>
            </form>

            <!-- REGISTER FORM -->
            <form id="registerForm" onsubmit="handleRegister(event)" autocomplete="off" class="space-y-3 hidden">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Primer Nombre *</label>
                        <input type="text" id="regFirstName" required placeholder="Juan" autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]/g, '')" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Segundo Nombre</label>
                        <input type="text" id="regMiddleName" placeholder="Carlos" autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]/g, '')" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Primer Apellido *</label>
                        <input type="text" id="regLastName" required placeholder="Pérez" autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]/g, '')" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Segundo Apellido</label>
                        <input type="text" id="regSecondLastName" placeholder="Gómez" autocomplete="off" oninput="this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s'-]/g, '')" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Correo Electrónico *</label>
                    <input type="email" id="regEmail" required placeholder="tuemail@ejemplo.com" autocomplete="off" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Contraseña Segura *</label>
                    <input type="password" id="regPassword" required placeholder="••••••••" autocomplete="new-password" oninput="checkPasswordRequirements(this.value)" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    
                    <!-- REQUISITOS DE CONTRASEÑA EN TIEMPO REAL (SOLO SE MUESTRA AL ESCRIBIR O SI HAY ERRORES) -->
                    <div id="passwordRequirements" class="mt-1.5 p-2.5 rounded-xl bg-[#0B2545]/90 border border-[#00A896]/40 text-[11px] space-y-1 text-slate-300 hidden">
                        <p class="font-bold text-[#00A896] text-[11px] mb-1">🔒 La contraseña debe incluir:</p>
                        <div class="grid grid-cols-2 gap-x-2 gap-y-1">
                            <div id="reqMinLen" class="flex items-center gap-1.5 text-slate-400 font-medium"><span>⚪</span> Mínimo 8 caracteres</div>
                            <div id="reqLetters" class="flex items-center gap-1.5 text-slate-400 font-medium"><span>⚪</span> Mayúsculas y minúsculas</div>
                            <div id="reqNumbers" class="flex items-center gap-1.5 text-slate-400 font-medium"><span>⚪</span> Al menos 1 número (0-9)</div>
                            <div id="reqSymbols" class="flex items-center gap-1.5 text-slate-400 font-medium"><span>⚪</span> 1 símbolo (!@#$%^&*)</div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Confirmar Contraseña *</label>
                    <input type="password" id="regPasswordConfirm" required placeholder="••••••••" autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                </div>
                <div id="regError" class="text-xs text-red-400 hidden"></div>
                <button type="submit" class="w-full py-3 rounded-xl bg-[#00A896] hover:bg-[#028090] font-bold text-slate-950 hover:text-white text-xs transition shadow-lg">Crear Cuenta</button>
            </form>
        </div>
    </div>

    <!-- MODAL CREAR / EDITAR EVENTO -->
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-lg rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 id="eventFormModalTitle" class="text-base font-extrabold text-white">Nuevo Evento / Tarea</h3>
                <button onclick="closeCreateModal()" title="Cerrar modal" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <form onsubmit="handleSaveEvent(event)" class="space-y-3">
                <input type="hidden" id="editingEventId" value="">

                <div>
                    <label class="block text-xs text-slate-300 mb-1">Título *</label>
                    <input type="text" id="evtTitle" required placeholder="ej. Pagar impuestos SRI o Reunión con Cliente" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Descripción / Nota</label>
                    <textarea id="evtDescription" rows="2" placeholder="Detalles de la tarea o nota..." class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Tipo de Evento *</label>
                        <select id="evtType" class="w-full rounded-xl border border-white/10 bg-[#0B2545] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                            <option value="tarea">📝 Tarea</option>
                            <option value="recordatorio">⏰ Recordatorio</option>
                            <option value="fecha_importante">📌 Fecha Importante</option>
                        </select>
                    </div>

                    <!-- SELECTOR DE COLORES VISIBLES PARA EVENTOS -->
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Color Identificativo del Evento</label>
                        <div class="flex items-center gap-2">
                            <input type="color" id="evtColor" value="#00A896" title="Selector libre de color" class="w-10 h-9 rounded-xl border border-white/10 bg-white/5 p-1 cursor-pointer">
                            <div class="flex items-center gap-1.5 flex-1 overflow-x-auto py-1" title="Paleta de colores llamativos y visibles para eventos">
                                <span onclick="setCorporateColor('#00A896')" class="h-6 w-6 rounded-lg bg-[#00A896] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Verde Turquesa Contafit"></span>
                                <span onclick="setCorporateColor('#06B6D4')" class="h-6 w-6 rounded-lg bg-[#06B6D4] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Cian Vibrante"></span>
                                <span onclick="setCorporateColor('#10B981')" class="h-6 w-6 rounded-lg bg-[#10B981] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Verde Esmeralda"></span>
                                <span onclick="setCorporateColor('#8B5CF6')" class="h-6 w-6 rounded-lg bg-[#8B5CF6] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Púrpura Místico"></span>
                                <span onclick="setCorporateColor('#F59E0B')" class="h-6 w-6 rounded-lg bg-[#F59E0B] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Ámbar Warm"></span>
                                <span onclick="setCorporateColor('#F43F5E')" class="h-6 w-6 rounded-lg bg-[#F43F5E] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Coral Prioridad"></span>
                                <span onclick="setCorporateColor('#EC4899')" class="h-6 w-6 rounded-lg bg-[#EC4899] border border-white/30 cursor-pointer hover:scale-110 transition shadow-sm" title="Rosa Neón"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Hora Inicio *</label>
                        <input type="datetime-local" id="evtStartAt" required class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Hora Fin (Opcional)</label>
                        <input type="datetime-local" id="evtEndAt" class="w-full rounded-xl border border-white/10 bg-[#0B1320] p-2 text-xs text-white focus:outline-none focus:border-[#00A896]">
                    </div>
                </div>

                <!-- ANTICIPACIÓN DE NOTIFICACIÓN / ALERTA ASÍNCRONA -->
                <div>
                    <label class="block text-xs text-slate-300 mb-1">⏰ Anticipación de Notificación</label>
                    <select id="evtReminderMinutes" title="Elige con cuánta anticipación recibirás el correo de alerta" class="w-full rounded-xl border border-white/10 bg-[#0B2545] p-2.5 text-xs text-white focus:outline-none focus:border-[#00A896]">
                        <option value="0">En el momento del evento</option>
                        <option value="15" selected>15 minutos antes</option>
                        <option value="30">30 minutos antes</option>
                        <option value="60">1 hora antes</option>
                        <option value="120">2 horas antes</option>
                        <option value="180">3 horas antes</option>
                        <option value="1440">1 día antes</option>
                        <option value="2880">2 días antes</option>
                        <option value="10080">1 semana antes</option>
                    </select>
                </div>

                <!-- EVENTOS RECURRENTES -->
                <div class="border-t border-white/10 pt-3">
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer" title="Configurar repetición periódica de este evento">
                        <input type="checkbox" id="evtIsRecurring" onchange="toggleRecurrenceOptions()" class="rounded border-white/10 text-[#00A896] focus:ring-[#00A896]">
                        Eventos Recurrentes
                    </label>
                    
                    <div id="recurrenceOptions" class="mt-2 hidden">
                        <label class="block text-xs text-slate-300 mb-1">Regla de Repetición</label>
                        <select id="evtRecurrenceFreq" class="w-full rounded-xl border border-white/10 bg-[#0B2545] p-2 text-xs text-white">
                            <option value="diaria">Diaria (Todos los días)</option>
                            <option value="semanal">Semanal (Mismo día de la semana)</option>
                            <option value="mensual">Mensual (Mismo día del mes)</option>
                            <option value="anual">Anual (Misma fecha cada año)</option>
                        </select>
                    </div>
                </div>

                <div id="createError" class="text-xs text-red-400 hidden"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 py-2.5 rounded-xl border border-white/10 bg-white/5 text-slate-300 text-xs hover:bg-white/10">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-[#00A896] hover:bg-[#028090] font-bold text-slate-950 hover:text-white text-xs transition shadow-md">Guardar Evento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PAPELERA DE RECICLAJE -->
    <div id="trashModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-xl rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-extrabold text-white">🗑️ Papelera de Reciclaje</h3>
                <button onclick="closeTrashModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-300">Eventos eliminados recientemente.</p>

            <div id="trashList" class="space-y-2.5 max-h-[350px] overflow-y-auto pr-1">
                <div class="p-4 text-center text-slate-400 text-xs">Cargando papelera...</div>
            </div>

            <div class="flex justify-end pt-2 border-t border-white/10">
                <button type="button" onclick="closeTrashModal()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- MODAL BORRADO DE CUENTA -->
    <div id="deleteAccountModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-bold text-red-400">Solicitar Borrado de Cuenta</h3>
                <button onclick="closeDeleteAccountModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-300">Ingresa tu contraseña y el código enviado a tu correo.</p>

            <form onsubmit="handleDeleteAccount(event)" autocomplete="off" class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-300 mb-1">1. Contraseña actual *</label>
                    <input type="password" id="deletePasswordInput" required placeholder="••••••••" autocomplete="new-password" class="w-full rounded-xl border border-red-500/30 bg-black/50 p-2.5 text-xs text-white focus:outline-none">
                </div>

                <div>
                    <button type="button" id="btnSendDelCode" onclick="handleSendDeletionCode()" class="w-full py-2 rounded-xl border border-[#00A896]/40 bg-[#00A896]/20 hover:bg-[#00A896]/30 text-[#00A896] text-xs font-semibold transition disabled:opacity-50 disabled:cursor-not-allowed">
                        📧 Solicitar Código de Verificación al Correo
                    </button>
                    <p id="deleteCodeStatus" class="text-[11px] text-emerald-400 mt-1 hidden"></p>
                </div>

                <div>
                    <label class="block text-xs text-slate-300 mb-1">2. Código de Verificación (6 dígitos)</label>
                    <input type="text" id="deleteCodeInput" placeholder="Ej. 123456" autocomplete="off" class="w-full rounded-xl border border-red-500/30 bg-black/50 p-2.5 text-xs text-white focus:outline-none font-mono">
                </div>

                <div id="deleteAccountError" class="text-xs text-red-400 hidden"></div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeDeleteAccountModal()" class="flex-1 py-2.5 rounded-xl border border-white/10 bg-white/5 text-slate-300 text-xs">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 font-semibold text-white text-xs transition">Confirmar Baja Definitiva</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT: LOGICA Y FORMATO DE FECHAS ECUADOR (dd/mm/yyyy) -->
    <script>
        let token = localStorage.getItem('auth_token') || null;
        let user = JSON.parse(localStorage.getItem('user_data') || 'null');
        let currentHolidays = [];
        let currentEvents = [];
        let currentDate = new Date();
        let selectedDayDateStr = null;
        let pendingDeleteEventId = null;

        // VALIDACIÓN INTERACTIVA DE REQUISITOS DE CONTRASEÑA EN TIEMPO REAL (DESPLIEGUE DINÁMICO)
        function checkPasswordRequirements(val, forceShow = false) {
            const box = document.getElementById('passwordRequirements');
            if (!box) return;

            if (!val || val.trim() === '') {
                if (forceShow) {
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                    return;
                }
            } else {
                box.classList.remove('hidden');
            }

            const minLen = val.length >= 8;
            const hasUpper = /[A-Z]/.test(val);
            const hasLower = /[a-z]/.test(val);
            const hasLetters = hasUpper && hasLower;
            const hasNumbers = /[0-9]/.test(val);
            const hasSymbols = /[^a-zA-Z0-9]/.test(val);

            updateReqItem('reqMinLen', minLen, 'Mínimo 8 caracteres');
            updateReqItem('reqLetters', hasLetters, 'Mayúsculas y minúsculas');
            updateReqItem('reqNumbers', hasNumbers, 'Al menos 1 número (0-9)');
            updateReqItem('reqSymbols', hasSymbols, '1 símbolo (!@#$%^&*)');
        }

        function updateReqItem(id, isMet, text) {
            const el = document.getElementById(id);
            if (!el) return;
            if (isMet) {
                el.className = 'flex items-center gap-1.5 text-emerald-400 font-bold';
                el.innerHTML = `<span class="text-emerald-400 font-black">✓</span> ${text}`;
            } else {
                el.className = 'flex items-center gap-1.5 text-red-300 font-medium';
                el.innerHTML = `<span class="text-red-400 font-bold">❌</span> ${text}`;
            }
        }

        // LIMPIEZA DE SEGURIDAD EN CAMPOS DE AUTENTICACIÓN Y FORMULARIOS
        function clearAuthForms() {
            const loginForm = document.getElementById('loginForm');
            if (loginForm) loginForm.reset();

            const registerForm = document.getElementById('registerForm');
            if (registerForm) registerForm.reset();
            checkPasswordRequirements('');

            const loginErr = document.getElementById('loginError');
            if (loginErr) { loginErr.innerText = ''; loginErr.classList.add('hidden'); }

            const regErr = document.getElementById('regError');
            if (regErr) { regErr.innerText = ''; regErr.classList.add('hidden'); }

            const delPass = document.getElementById('deletePasswordInput');
            if (delPass) delPass.value = '';
            const delCode = document.getElementById('deleteCodeInput');
            if (delCode) delCode.value = '';
            const delErr = document.getElementById('deleteAccountError');
            if (delErr) { delErr.innerText = ''; delErr.classList.add('hidden'); }
        }

        // SISTEMA DE NOTIFICACIONES TOAST CORPORATIVAS CONTAFIT (UI/UX)
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('toastContainer');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center gap-3 p-3.5 rounded-2xl shadow-2xl border text-xs font-bold transform transition-all duration-300 translate-x-10 opacity-0`;
            
            if (type === 'success') {
                toast.className += ` bg-[#0B2545] border-[#00A896] text-white shadow-[#00A896]/20`;
                toast.innerHTML = `<span class="text-base">✅</span> <div class="flex-1">${message}</div>`;
            } else if (type === 'error') {
                toast.className += ` bg-[#1E1B2E] border-red-500/50 text-red-200 shadow-red-500/20`;
                toast.innerHTML = `<span class="text-base">❌</span> <div class="flex-1">${message}</div>`;
            } else if (type === 'warning') {
                toast.className += ` bg-[#2E201B] border-amber-500/50 text-amber-200 shadow-amber-500/20`;
                toast.innerHTML = `<span class="text-base">⚠️</span> <div class="flex-1">${message}</div>`;
            } else {
                toast.className += ` bg-[#0B2545] border-sky-500/50 text-sky-200 shadow-sky-500/20`;
                toast.innerHTML = `<span class="text-base">ℹ️</span> <div class="flex-1">${message}</div>`;
            }

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-10', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            });

            setTimeout(() => {
                toast.classList.remove('translate-x-0', 'opacity-100');
                toast.classList.add('translate-x-10', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkAuth();
        });

        // CONTROL DEL DROPDOWN DE USUARIO
        function toggleUserDropdown(e) {
            if (e) e.stopPropagation();
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        function closeUserDropdown() {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown) dropdown.classList.add('hidden');
        }

        document.addEventListener('click', () => closeUserDropdown());

        function setCorporateColor(hexColor) {
            document.getElementById('evtColor').value = hexColor;
        }

        // FORMATO DE FECHA ECUATORIANO (dd/mm/yyyy)
        function formatDateEC(isoOrYmdStr) {
            if (!isoOrYmdStr) return '';
            const cleanStr = isoOrYmdStr.slice(0, 10);
            const parts = cleanStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return isoOrYmdStr;
        }

        function handleApiResponse(res) {
            if (res.status === 401) {
                handleUnauthorized();
                return null;
            }
            return res;
        }

        function handleUnauthorized() {
            localStorage.clear();
            token = null;
            user = null;
            currentEvents = [];
            currentHolidays = [];
            clearAuthForms();
            document.getElementById('todayTaskList').innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">Sesión expirada o usuario no registrado. Por favor inicia sesión.</div>`;
            document.getElementById('calendarGrid').innerHTML = '';
            document.getElementById('authModal').classList.remove('hidden');
            checkAuth();
        }

        function getLocalYYYYMMDD(dateObj = null) {
            const d = dateObj ? new Date(dateObj) : new Date();
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function getLocalDateTimeString(addHours = 0, dateObj = null) {
            const now = dateObj ? new Date(dateObj) : new Date();
            if (!dateObj) now.setHours(now.getHours() + addHours);
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        function formatDateTimeForApi(dtStr) {
            if (!dtStr || dtStr.trim() === '') return null;
            let s = dtStr.trim().replace('T', ' ');
            if (s.length === 16) s += ':00';
            return s;
        }

        function goToToday() {
            currentDate = new Date();
            const todayStr = getLocalYYYYMMDD();
            renderCalendar();
            openDayViewModal(todayStr);
        }

        function openConfirmLogoutModal() {
            document.getElementById('confirmLogoutModal').classList.remove('hidden');
        }

        function closeConfirmLogoutModal() {
            document.getElementById('confirmLogoutModal').classList.add('hidden');
        }

        function confirmLogout() {
            closeConfirmLogoutModal();
            handleLogout();
        }

        function checkAuth() {
            const header = document.getElementById('authHeader');

            if (!token || !user) {
                document.getElementById('authModal').classList.remove('hidden');
                header.innerHTML = `<button onclick="openAuthModal()" class="px-4 py-2 rounded-xl bg-[#00A896] hover:bg-[#028090] text-xs font-bold text-slate-950 hover:text-white transition shadow-md">Ingresar / Registro</button>`;
            } else {
                document.getElementById('authModal').classList.add('hidden');
                header.innerHTML = `
                    <!-- MENÚ DESPLEGABLE DE USUARIO (DROPDOWN CORPORATIVO) -->
                    <div class="relative inline-block text-left">
                        <button onclick="toggleUserDropdown(event)" title="Opciones de perfil de usuario" class="px-3.5 py-2 rounded-xl bg-[#00A896] hover:bg-[#028090] text-slate-950 font-extrabold text-xs shadow-lg transition-all flex items-center gap-2 cursor-pointer border border-[#00A896]/40">
                            <span class="h-6 w-6 rounded-lg bg-[#0B2545] text-white flex items-center justify-center text-[11px] font-black shadow-inner">${user.name.charAt(0).toUpperCase()}</span>
                            <span>${user.name}</span>
                            <span class="text-[10px] text-slate-950">▼</span>
                        </button>

                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-[#1E293B] border border-[#00A896]/40 shadow-2xl z-50 p-2 space-y-1">
                            <div class="px-3 py-2 border-b border-white/10 mb-1">
                                <p class="text-xs font-bold text-white">${user.name}</p>
                                <p class="text-[10px] text-slate-400 truncate">${user.email}</p>
                            </div>
                            
                            <button onclick="closeUserDropdown(); openTrashModal();" class="w-full text-left px-3 py-2 rounded-xl text-xs text-amber-300 hover:bg-amber-500/20 transition font-bold flex items-center gap-2">
                                <span>🗑️</span> <span>Papelera de Reciclaje</span>
                            </button>

                            <button onclick="closeUserDropdown(); openDeleteAccountModal();" class="w-full text-left px-3 py-2 rounded-xl text-xs text-red-300 hover:bg-red-950/40 hover:text-red-200 transition font-bold flex items-center gap-2">
                                <span>⚠️</span> <span>Borrar Cuenta</span>
                            </button>

                            <button onclick="closeUserDropdown(); openConfirmLogoutModal();" class="w-full text-left px-3 py-2 rounded-xl text-xs text-slate-200 hover:bg-white/10 transition font-bold flex items-center gap-2 border-t border-white/10 pt-1.5">
                                <span>🚪</span> <span>Cerrar Sesión</span>
                            </button>
                        </div>
                    </div>
                `;
                loadDashboardData();
            }
        }

        function switchAuthTab(tab) {
            clearAuthForms();
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                tabLoginBtn.className = 'flex-1 py-3 text-center text-xs font-bold text-[#00A896] border-b-2 border-[#00A896]';
                tabRegisterBtn.className = 'flex-1 py-3 text-center text-xs font-bold text-slate-400';
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                tabRegisterBtn.className = 'flex-1 py-3 text-center text-xs font-bold text-[#00A896] border-b-2 border-[#00A896]';
                tabLoginBtn.className = 'flex-1 py-3 text-center text-xs font-bold text-slate-400';
            }
        }

        function openAuthModal() { 
            clearAuthForms();
            document.getElementById('authModal').classList.remove('hidden'); 
        }

        async function handleLogin(e) {
            e.preventDefault();
            const errDiv = document.getElementById('loginError');
            errDiv.classList.add('hidden');

            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        email: document.getElementById('loginEmail').value,
                        password: document.getElementById('loginPassword').value
                    })
                });
                const data = await res.json();
                if (data.success) {
                    currentEvents = [];
                    currentHolidays = [];
                    token = data.data.auth_token;
                    user = data.data.user;
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user_data', JSON.stringify(user));
                    clearAuthForms();
                    checkAuth();
                    showToast(`¡Bienvenido a ContaFit, ${user.name}!`, 'success');
                } else {
                    if (data.data?.errors) {
                        const errorItems = Object.values(data.data.errors).flat().map(err => `<li class="flex items-start gap-1.5"><span class="text-red-400 font-bold">⚠️</span> <span>${err}</span></li>`).join('');
                        errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs space-y-1"><p class="font-bold text-red-300">No se pudo iniciar sesión:</p><ul class="space-y-1 mt-1">${errorItems}</ul></div>`;
                    } else {
                        errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs flex items-center gap-2"><span class="text-base">⚠️</span> <span>${data.message || 'Credenciales inválidas. Verifica tu correo y contraseña.'}</span></div>`;
                    }
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs flex items-center gap-2"><span class="text-base">📡</span> <span>Error de conexión. Intenta nuevamente.</span></div>`;
                errDiv.classList.remove('hidden');
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const errDiv = document.getElementById('regError');
            errDiv.classList.add('hidden');

            try {
                const res = await fetch('/api/register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        first_name: document.getElementById('regFirstName').value,
                        middle_name: document.getElementById('regMiddleName').value,
                        last_name: document.getElementById('regLastName').value,
                        second_last_name: document.getElementById('regSecondLastName').value,
                        email: document.getElementById('regEmail').value,
                        password: document.getElementById('regPassword').value,
                        password_confirmation: document.getElementById('regPasswordConfirm').value
                    })
                });
                const data = await res.json();
                if (data.success) {
                    currentEvents = [];
                    currentHolidays = [];
                    token = data.data.auth_token;
                    user = data.data.user;
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user_data', JSON.stringify(user));
                    clearAuthForms();
                    checkAuth();
                    showToast(`¡Cuenta creada exitosamente! Bienvenido, ${user.name}`, 'success');
                } else {
                    checkPasswordRequirements(document.getElementById('regPassword').value, true);
                    if (data.data?.errors) {
                        const errorItems = Object.values(data.data.errors).flat().map(err => `<li class="flex items-start gap-1.5"><span class="text-red-400 font-bold">⚠️</span> <span>${err}</span></li>`).join('');
                        errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs space-y-1"><p class="font-bold text-red-300">Por favor corrige los siguientes datos:</p><ul class="space-y-1 mt-1">${errorItems}</ul></div>`;
                    } else {
                        errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs flex items-center gap-2"><span class="text-base">⚠️</span> <span>${data.message || 'No se pudo crear la cuenta.'}</span></div>`;
                    }
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerHTML = `<div class="p-3 rounded-xl bg-red-950/70 border border-red-500/40 text-red-200 text-xs flex items-center gap-2"><span class="text-base">📡</span> <span>Error de conexión. Intenta nuevamente.</span></div>`;
                errDiv.classList.remove('hidden');
            }
        }

        function handleLogout() {
            localStorage.clear();
            token = null; user = null;
            currentEvents = []; currentHolidays = [];
            clearAuthForms();
            document.getElementById('todayTaskList').innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">Por favor inicia sesión.</div>`;
            document.getElementById('calendarGrid').innerHTML = '';
            showToast('Sesión cerrada correctamente.', 'info');
            checkAuth();
        }

        async function loadDashboardData() {
            await fetchTodayTasks();
            await fetchHolidays();
            await applyFilters();
        }

        // DASHBOARD CHECKLIST
        async function fetchTodayTasks() {
            const list = document.getElementById('todayTaskList');
            const badge = document.getElementById('taskCountBadge');
            const showCompleted = document.getElementById('showCompletedToggle')?.checked || false;
            if (!token) return;

            const localTodayStr = getLocalYYYYMMDD();

            try {
                let res = await fetch(`/api/dashboard/today?date=${localTodayStr}&include_completed=${showCompleted ? 1 : 0}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    let tasks = data.data.events || [];
                    
                    if (!showCompleted) {
                        tasks = tasks.filter(t => t.status !== 'completada');
                    }

                    const pendingCount = tasks.filter(t => t.status !== 'completada').length;
                    badge.innerText = `${pendingCount} pendientes`;

                    if (tasks.length === 0) {
                        list.innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">🎉 No tienes tareas pendientes para hoy.</div>`;
                        return;
                    }

                    list.innerHTML = tasks.map(t => {
                        const timeStr = new Date(t.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const isCompleted = t.status === 'completada';

                        return `
                            <div class="rounded-xl border border-white/10 bg-[#0B2545]/60 p-3 space-y-2 ${isCompleted ? 'opacity-60 line-through' : ''}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" ${isCompleted ? 'checked' : ''} onchange="changeTaskStatus(${t.id}, this.checked ? 'completada' : 'pendiente')" class="h-4 w-4 rounded border-white/20 text-[#00A896] focus:ring-0 cursor-pointer" title="Marcar como completada">
                                        <div>
                                            <p class="text-[10px] font-bold text-[#00A896]">${timeStr} - ${t.type.toUpperCase()}</p>
                                            <h3 class="text-xs font-bold text-white">${t.title}</h3>
                                        </div>
                                    </div>
                                    <span class="h-3 w-3 rounded-full shrink-0 border border-white/20" style="background-color: ${t.color}"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-300 pt-1.5 border-t border-white/5">
                                    <span class="text-[10px] font-medium text-slate-300">Estado: <strong class="text-white">${t.status}</strong></span>
                                    <div class="flex gap-2">
                                        ${isCompleted ? 
                                            `<button onclick="changeTaskStatus(${t.id}, 'pendiente')" class="text-amber-400 hover:text-amber-300 text-[10px] font-bold cursor-pointer">↩️ Desmarcar</button>` : 
                                            `<button onclick="changeTaskStatus(${t.id}, 'en_progreso')" class="text-[#00A896] hover:text-teal-300 text-[10px] font-bold cursor-pointer">🚀 En Progreso</button>`
                                        }
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (err) { console.error(err); }
        }

        // PRÓXIMOS FERIADOS ECUADOR (PRÓXIMOS 6 MESES)
        async function fetchHolidays() {
            const widget = document.getElementById('holidaysWidget');
            if (!token) return;

            const now = new Date();
            const currentYear = now.getFullYear();
            const todayStr = getLocalYYYYMMDD(now);

            const sixMonthsLater = new Date();
            sixMonthsLater.setMonth(sixMonthsLater.getMonth() + 6);
            const sixMonthsStr = getLocalYYYYMMDD(sixMonthsLater);

            try {
                let resCurr = await fetch(`/api/holidays?year=${currentYear}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                resCurr = handleApiResponse(resCurr);
                if (!resCurr) return;

                const dataCurr = await resCurr.json();
                let allHolidays = dataCurr.data?.holidays || [];

                // Si el rango de 6 meses pasa al siguiente año, cargar también el próximo año
                if (sixMonthsLater.getFullYear() > currentYear) {
                    let resNext = await fetch(`/api/holidays?year=${currentYear + 1}`, {
                        headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                    });
                    resNext = handleApiResponse(resNext);
                    if (resNext) {
                        const dataNext = await resNext.json();
                        if (dataNext.success && dataNext.data?.holidays) {
                            allHolidays = allHolidays.concat(dataNext.data.holidays);
                        }
                    }
                }

                currentHolidays = allHolidays;

                // Filtrar solo feriados futuros en los próximos 6 meses (hoy <= fecha <= hoy+6meses)
                const upcoming = allHolidays.filter(h => h.date >= todayStr && h.date <= sixMonthsStr);

                if (upcoming.length === 0) {
                    widget.innerHTML = `<div class="text-slate-400 text-[11px]">No hay feriados en los próximos 6 meses.</div>`;
                } else {
                    widget.innerHTML = upcoming.slice(0, 5).map(h => `
                        <div class="flex items-center justify-between text-slate-200">
                            <span class="font-medium">🇪🇨 ${h.name}</span>
                            <span class="text-[#00A896] font-mono text-[11px] font-bold">${formatDateEC(h.date)}</span>
                        </div>
                    `).join('');
                }
            } catch (err) { console.error(err); }
        }

        // FILTROS Y BÚSQUEDA EN TIEMPO REAL
        async function applyFilters() {
            if (!token) return;

            const search = document.getElementById('searchInput').value;
            const type = document.getElementById('typeFilter').value;
            const status = document.getElementById('statusFilter').value;

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (type) params.append('type', type);
            if (status) params.append('status', status);

            try {
                let res = await fetch(`/api/events?${params.toString()}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    currentEvents = data.data.events || [];

                    // Si hay un término de búsqueda o filtro activo y existen eventos encontrados,
                    // navegar automáticamente al año y mes del primer evento coincidente
                    const hasActiveFilter = (search && search.trim() !== '') || type !== '' || status !== '';
                    if (hasActiveFilter && currentEvents.length > 0) {
                        const firstMatch = currentEvents[0];
                        const matchDateStr = getEventLocalDateStr(firstMatch.start_at);
                        if (matchDateStr && matchDateStr.length >= 10) {
                            const parts = matchDateStr.slice(0, 10).split('-');
                            if (parts.length === 3) {
                                const targetYear = parseInt(parts[0]);
                                const targetMonth = parseInt(parts[1]) - 1;

                                if (currentDate.getFullYear() !== targetYear || currentDate.getMonth() !== targetMonth) {
                                    currentDate = new Date(targetYear, targetMonth, 1);
                                    await fetchHolidays();
                                }
                            }
                        }
                    }

                    renderCalendar();
                }
            } catch (err) { console.error(err); }
        }

        async function navigateMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            await fetchHolidays();
            renderCalendar();
        }

        function getEventLocalDateStr(dtStr) {
            if (!dtStr) return '';
            const s = dtStr.replace(' ', 'T');
            const d = new Date(s);
            if (isNaN(d.getTime())) return dtStr.substring(0, 10);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function getEventLocalTimeStr(dtStr) {
            if (!dtStr) return '';
            const s = dtStr.replace(' ', 'T');
            const d = new Date(s);
            if (isNaN(d.getTime())) return dtStr.slice(11, 16);
            const hh = String(d.getHours()).padStart(2, '0');
            const mm = String(d.getMinutes()).padStart(2, '0');
            return `${hh}:${mm}`;
        }

        function eventMatchesDate(e, dateStr) {
            if (e.status === 'excluded' || e.deleted_at) return false;

            const isExcluded = currentEvents.some(child => 
                String(child.recurrence_parent_id) === String(e.id) && 
                getEventLocalDateStr(child.start_at) === dateStr &&
                (child.status === 'excluded' || child.deleted_at)
            );
            if (isExcluded) return false;

            const eventStart = getEventLocalDateStr(e.start_at);
            if (eventStart === dateStr) return true;
            if (!e.is_recurring) return false;
            if (dateStr < eventStart) return false;

            const evalDate = new Date(dateStr + 'T00:00:00');
            const startDate = new Date(eventStart + 'T00:00:00');
            const freq = e.recurrence_frequency || e.recurrence_type || e.recurrence;

            switch (freq) {
                case 'diaria': return true;
                case 'semanal': return evalDate.getDay() === startDate.getDay();
                case 'mensual': return evalDate.getDate() === startDate.getDate();
                case 'anual': return evalDate.getMonth() === startDate.getMonth() && evalDate.getDate() === startDate.getDate();
                default: return false;
            }
        }

        function renderCalendar() {
            const grid = document.getElementById('calendarGrid');
            const title = document.getElementById('calendarTitle');
            const searchVal = document.getElementById('searchInput')?.value.trim() || '';
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            title.innerText = `${monthNames[month]} ${year}`;

            const firstDayIndex = new Date(year, month, 1).getDay();
            const totalDays = new Date(year, month + 1, 0).getDate();
            const today = new Date();

            let cellsHTML = '';

            for (let i = 0; i < firstDayIndex; i++) {
                cellsHTML += `<div class="min-h-[85px] rounded-xl border border-white/5 bg-white/2 opacity-20"></div>`;
            }

            for (let day = 1; day <= totalDays; day++) {
                const monthStr = (month + 1) < 10 ? `0${month + 1}` : `${month + 1}`;
                const dayStr = day < 10 ? `0${day}` : `${day}`;
                const dateKey = `${year}-${monthStr}-${dayStr}`;
                const isToday = (day === today.getDate() && month === today.getMonth() && year === today.getFullYear());

                const dayEvents = currentEvents.filter(e => eventMatchesDate(e, dateKey));
                const dayHolidays = currentHolidays.filter(h => h.date.startsWith(dateKey));

                const isMatchDay = searchVal !== '' && dayEvents.length > 0;

                let cardStyle = 'bg-[#1E293B]/60 hover:bg-[#1E293B]';
                if (isToday) {
                    cardStyle = 'ring-2 ring-[#00A896] bg-[#0B2545]/80 shadow-md shadow-[#00A896]/20';
                } else if (isMatchDay) {
                    cardStyle = 'ring-2 ring-[#00A896] bg-[#00A896]/15 shadow-lg shadow-[#00A896]/30';
                }

                cellsHTML += `
                    <div onclick="openDayViewModal('${dateKey}')" class="relative flex min-h-[95px] flex-col overflow-hidden rounded-2xl border border-white/10 p-2.5 text-white cursor-pointer hover:border-[#00A896] transition-all ${cardStyle}" title="Ver eventos para la fecha ${formatDateEC(dateKey)}">
                        <div class="flex items-start justify-between">
                            <span class="text-xs font-bold ${isToday ? 'text-[#00A896]' : (isMatchDay ? 'text-[#00A896]' : 'text-slate-200')}">${day}</span>
                            ${isToday ? `<span class="rounded bg-[#00A896] px-1.5 py-0.2 text-[8px] font-extrabold text-slate-950">HOY</span>` : (isMatchDay ? `<span class="rounded bg-[#00A896] px-1.5 py-0.2 text-[8px] font-extrabold text-slate-950">ENCONTRADO</span>` : '')}
                        </div>

                        <div class="mt-1 space-y-1 overflow-y-auto max-h-[65px]">
                            ${dayHolidays.map(h => `
                                <div class="rounded-lg bg-emerald-600/90 px-1.5 py-0.5 text-[9px] font-bold text-white truncate" title="${h.name}">
                                    🇪🇨 ${h.name}
                                </div>
                            `).join('')}

                            ${dayEvents.map(e => `
                                <div class="rounded-lg px-1.5 py-0.5 text-[9px] font-bold text-white truncate flex items-center justify-between shadow-sm" style="background-color: ${e.color}" title="${e.title}">
                                    <span>${e.is_recurring ? '🔄 ' : ''}${e.title}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            grid.innerHTML = cellsHTML;
        }

        // MODAL VER EVENTOS DEL DÍA
        function openDayViewModal(dateStr) {
            selectedDayDateStr = dateStr;
            const dateEC = formatDateEC(dateStr);
            document.getElementById('dayViewTitle').innerText = `📅 Eventos del Día (${dateEC})`;
            
            const container = document.getElementById('dayViewContent');
            const dayEvents = currentEvents.filter(e => eventMatchesDate(e, dateStr));
            const dayHolidays = currentHolidays.filter(h => h.date.startsWith(dateStr));

            let html = '';

            if (dayHolidays.length > 0) {
                html += `<div class="mb-3 space-y-1">
                    <h4 class="text-xs font-bold text-[#00A896] uppercase tracking-wider">Feriados de Ecuador</h4>
                    ${dayHolidays.map(h => `
                        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-2.5 text-xs text-emerald-200 font-medium">
                            🇪🇨 <strong>${h.name}</strong>
                        </div>
                    `).join('')}
                </div>`;
            }

            if (dayEvents.length === 0) {
                html += `<div class="p-6 text-center text-slate-400 text-xs">No hay eventos registrados para esta fecha (${dateEC}).</div>`;
            } else {
                html += `<div class="space-y-2">
                    <h4 class="text-xs font-bold text-[#00A896] uppercase tracking-wider">Eventos Registrados</h4>
                    ${dayEvents.map(e => `
                        <div class="rounded-xl border border-white/10 bg-[#0B2545]/70 p-3 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-extrabold text-[#00A896] uppercase">${e.type} ${e.is_recurring ? '🔄' : ''}</span>
                                    <h4 class="text-xs font-bold text-white">${e.title}</h4>
                                    <p class="text-[11px] text-slate-300 mt-0.5">${e.description || 'Sin descripción'}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Horario: ${getEventLocalTimeStr(e.start_at)} ${e.end_at ? '- ' + getEventLocalTimeStr(e.end_at) : ''}</p>
                                </div>
                                <span class="h-3.5 w-3.5 rounded-full shrink-0 border border-white/20 shadow-sm" style="background-color: ${e.color}"></span>
                            </div>

                            <div class="flex items-center justify-between text-xs border-t border-white/10 pt-2">
                                <span class="text-[10px] text-slate-300">Estado: <strong class="text-white">${e.status}</strong></span>
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(${e.id})" class="px-2.5 py-1 rounded-lg bg-[#00A896] hover:bg-[#028090] text-slate-950 hover:text-white text-[10px] font-bold transition">✏️ Editar</button>
                                    <button onclick="promptDeleteEvent(${e.id})" class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-500 text-white text-[10px] font-bold transition">🗑️ Eliminar</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>`;
            }

            container.innerHTML = html;
            document.getElementById('dayViewModal').classList.remove('hidden');
        }

        function closeDayViewModal() { document.getElementById('dayViewModal').classList.add('hidden'); }

        function openCreateModalFromDayView() {
            closeDayViewModal();
            openCreateModal(selectedDayDateStr);
        }

        function promptDeleteEvent(id) {
            pendingDeleteEventId = id;
            document.getElementById('deleteRecurringModal').classList.remove('hidden');
        }

        function closeDeleteRecurringModal() {
            document.getElementById('deleteRecurringModal').classList.add('hidden');
            pendingDeleteEventId = null;
        }

        // ELIMINA SOLO ESTA FECHA
        async function confirmDeleteRecurringInstance() {
            if (!pendingDeleteEventId || !selectedDayDateStr) return;
            const id = pendingDeleteEventId;
            const dateStr = selectedDayDateStr;
            closeDeleteRecurringModal();
            
            try {
                let res = await fetch(`/api/events/${id}/instance?date=${encodeURIComponent(dateStr)}`, {
                    method: 'DELETE',
                    headers: { 
                        'Authorization': `Bearer ${token}`, 
                        'Accept': 'application/json' 
                    }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    closeDayViewModal();
                    await fetchTodayTasks();
                    await applyFilters();
                    showToast('Fecha eliminada correctamente.', 'info');
                }
            } catch (err) { console.error(err); }
        }

        // ELIMINA SERIE COMPLETA
        async function confirmDeleteRecurringSeries() {
            if (!pendingDeleteEventId) return;
            const id = pendingDeleteEventId;
            closeDeleteRecurringModal();
            await executeDeleteEvent(id);
        }

        async function executeDeleteEvent(id) {
            try {
                let res = await fetch(`/api/events/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    closeDayViewModal();
                    await fetchTodayTasks();
                    await applyFilters();
                    showToast('Evento movido a la papelera.', 'warning');
                }
            } catch (err) { console.error(err); }
        }

        // EDITAR EVENTO
        function openEditModal(eventId) {
            closeDayViewModal();
            const event = currentEvents.find(e => e.id === eventId);
            if (!event) return;

            document.getElementById('editingEventId').value = event.id;
            document.getElementById('eventFormModalTitle').innerText = '✏️ Editar Evento';
            document.getElementById('evtTitle').value = event.title || '';
            document.getElementById('evtDescription').value = event.description || '';
            document.getElementById('evtType').value = event.type || 'tarea';
            document.getElementById('evtColor').value = event.color || '#00A896';
            
            document.getElementById('evtStartAt').value = event.start_at ? event.start_at.replace(' ', 'T').slice(0, 16) : getLocalDateTimeString(0);
            document.getElementById('evtEndAt').value = event.end_at ? event.end_at.replace(' ', 'T').slice(0, 16) : '';
            document.getElementById('evtReminderMinutes').value = event.reminder_minutes_before !== null ? String(event.reminder_minutes_before) : '15';

            document.getElementById('evtIsRecurring').checked = !!event.is_recurring;
            document.getElementById('evtRecurrenceFreq').value = event.recurrence_frequency || 'diaria';
            toggleRecurrenceOptions();

            document.getElementById('createModal').classList.remove('hidden');
        }

        function openCreateModal(customDate = null) {
            document.getElementById('editingEventId').value = '';
            document.getElementById('eventFormModalTitle').innerText = 'Nuevo Evento / Tarea';
            document.getElementById('evtTitle').value = '';
            document.getElementById('evtDescription').value = '';
            document.getElementById('evtType').value = 'tarea';
            document.getElementById('evtColor').value = '#00A896';
            document.getElementById('evtIsRecurring').checked = false;
            document.getElementById('evtRecurrenceFreq').value = 'diaria';
            document.getElementById('evtReminderMinutes').value = '15';
            document.getElementById('createError').classList.add('hidden');
            toggleRecurrenceOptions();

            if (customDate) {
                document.getElementById('evtStartAt').value = `${customDate}T09:00`;
                document.getElementById('evtEndAt').value = `${customDate}T10:00`;
            } else {
                document.getElementById('evtStartAt').value = getLocalDateTimeString(0);
                document.getElementById('evtEndAt').value = getLocalDateTimeString(1);
            }

            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() { document.getElementById('createModal').classList.add('hidden'); }

        function toggleRecurrenceOptions() {
            const chk = document.getElementById('evtIsRecurring').checked;
            const opts = document.getElementById('recurrenceOptions');
            if (chk) opts.classList.remove('hidden');
            else opts.classList.add('hidden');
        }

        async function changeTaskStatus(id, status) {
            try {
                let res = await fetch(`/api/events/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status: status })
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    await fetchTodayTasks();
                    await applyFilters();
                    showToast(`Estado cambiado a: ${status}`, 'success');
                }
            } catch (err) { console.error(err); }
        }

        // GUARDAR EVENTO (CREAR O EDITAR)
        async function handleSaveEvent(e) {
            e.preventDefault();
            const errDiv = document.getElementById('createError');
            errDiv.classList.add('hidden');

            const editingId = document.getElementById('editingEventId').value;
            const isRecurring = document.getElementById('evtIsRecurring').checked;
            const reminderVal = document.getElementById('evtReminderMinutes').value;

            const rawStartAt = document.getElementById('evtStartAt').value;
            const rawEndAt = document.getElementById('evtEndAt').value;

            const formattedStartAt = formatDateTimeForApi(rawStartAt);
            let formattedEndAt = formatDateTimeForApi(rawEndAt);

            if (!formattedEndAt || formattedEndAt.trim() === '') {
                formattedEndAt = null;
            } else if (rawStartAt && rawEndAt && new Date(rawEndAt) < new Date(rawStartAt)) {
                formattedEndAt = formattedStartAt;
            }

            const payload = {
                title: document.getElementById('evtTitle').value,
                description: document.getElementById('evtDescription').value,
                type: document.getElementById('evtType').value,
                color: document.getElementById('evtColor').value,
                start_at: formattedStartAt,
                end_at: formattedEndAt,
                reminder_minutes_before: reminderVal !== '' ? parseInt(reminderVal) : 15,
                is_recurring: isRecurring,
            };

            if (isRecurring) payload.recurrence_frequency = document.getElementById('evtRecurrenceFreq').value;

            const url = editingId ? `/api/events/${editingId}` : '/api/events';
            const method = editingId ? 'PATCH' : 'POST';

            try {
                let res = await fetch(url, {
                    method: method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    closeCreateModal();
                    await fetchTodayTasks();
                    await applyFilters();
                    showToast(editingId ? 'Evento actualizado correctamente' : 'Evento guardado exitosamente', 'success');
                } else {
                    let msg = data.message || 'Error de validación.';
                    if (data.data && data.data.errors) {
                        msg += '<br>' + Object.values(data.data.errors).flat().join('<br>');
                    }
                    errDiv.innerHTML = msg;
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión.';
                errDiv.classList.remove('hidden');
            }
        }

        // PAPELERA DE RECICLAJE Y PURGA
        function openTrashModal() {
            document.getElementById('trashModal').classList.remove('hidden');
            fetchTrashEvents();
        }
        function closeTrashModal() { document.getElementById('trashModal').classList.add('hidden'); }

        async function fetchTrashEvents() {
            const list = document.getElementById('trashList');
            if (!token) return;

            try {
                let res = await fetch('/api/events/trash', {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    const events = data.data.events || [];
                    if (events.length === 0) {
                        list.innerHTML = `<div class="p-6 text-center text-slate-400 text-xs">🎉 La papelera está vacía.</div>`;
                        return;
                    }

                    list.innerHTML = events.map(e => `
                        <div class="rounded-xl border border-white/10 bg-[#0B2545]/70 p-3.5 flex items-center justify-between gap-3 shadow-md">
                            <div>
                                <span class="text-[10px] font-bold text-[#00A896] uppercase">${e.type}</span>
                                <h4 class="text-xs font-bold text-white">${e.title}</h4>
                                <p class="text-[10px] text-slate-400 mt-0.5">Eliminado el ${formatDateEC(e.deleted_at)}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="restoreTrashedEvent(${e.id})" class="px-3 py-1.5 rounded-xl bg-[#00A896] hover:bg-[#028090] text-slate-950 hover:text-white font-extrabold text-xs transition shadow-sm">♻️ Restaurar</button>
                                <button onclick="forceDeleteTrashedEvent(${e.id})" class="px-3 py-1.5 rounded-xl bg-red-600 hover:bg-red-500 text-white font-bold text-xs transition shadow-sm">Eliminar</button>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (err) {
                console.error(err);
                list.innerHTML = `<div class="p-4 text-center text-red-400 text-xs">Error al cargar la papelera.</div>`;
            }
        }

        async function restoreTrashedEvent(id) {
            try {
                let res = await fetch(`/api/events/${id}/restore`, {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    await fetchTrashEvents();
                    await fetchTodayTasks();
                    await applyFilters();
                    showToast('Evento restaurado de la papelera.', 'success');
                }
            } catch (err) { console.error(err); }
        }

        async function forceDeleteTrashedEvent(id) {
            try {
                let res = await fetch(`/api/events/${id}/force-delete`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    await fetchTrashEvents();
                    showToast('Evento eliminado permanentemente.', 'info');
                }
            } catch (err) { console.error(err); }
        }

        // SOLICITUD DE BORRADO DE CUENTA
        let delCodeTimer = null;

        function checkDelCodeCooldown() {
            const btn = document.getElementById('btnSendDelCode');
            if (!btn) return;
            const cooldownUntil = parseInt(localStorage.getItem('del_code_cooldown') || '0');
            const now = Date.now();

            if (cooldownUntil > now) {
                btn.disabled = true;
                updateDelCodeTimer(cooldownUntil);
            } else {
                btn.disabled = false;
                btn.innerText = '📧 Solicitar Código de Verificación al Correo';
                if (delCodeTimer) clearInterval(delCodeTimer);
            }
        }

        function updateDelCodeTimer(cooldownUntil) {
            const btn = document.getElementById('btnSendDelCode');
            if (!btn) return;
            if (delCodeTimer) clearInterval(delCodeTimer);

            delCodeTimer = setInterval(() => {
                const remainingSec = Math.ceil((cooldownUntil - Date.now()) / 1000);
                if (remainingSec <= 0) {
                    clearInterval(delCodeTimer);
                    localStorage.removeItem('del_code_cooldown');
                    btn.disabled = false;
                    btn.innerText = '📧 Solicitar Código de Verificación al Correo';
                } else {
                    btn.disabled = true;
                    const mins = String(Math.floor(remainingSec / 60)).padStart(2, '0');
                    const secs = String(remainingSec % 60).padStart(2, '0');
                    btn.innerText = `⏳ Reenviar código en (${mins}:${secs})`;
                }
            }, 1000);
        }

        function openDeleteAccountModal() { 
            const delPass = document.getElementById('deletePasswordInput');
            if (delPass) delPass.value = '';
            const delCode = document.getElementById('deleteCodeInput');
            if (delCode) delCode.value = '';
            const delErr = document.getElementById('deleteAccountError');
            if (delErr) { delErr.innerText = ''; delErr.classList.add('hidden'); }
            document.getElementById('deleteAccountModal').classList.remove('hidden'); 
            checkDelCodeCooldown();
        }

        function closeDeleteAccountModal() { document.getElementById('deleteAccountModal').classList.add('hidden'); }

        async function handleSendDeletionCode() {
            const pass = document.getElementById('deletePasswordInput').value;
            const errDiv = document.getElementById('deleteAccountError');
            const statusDiv = document.getElementById('deleteCodeStatus');
            errDiv.classList.add('hidden');
            statusDiv.classList.add('hidden');

            if (!pass) {
                errDiv.innerText = 'Por favor ingresa tu contraseña primero para solicitar el código.';
                errDiv.classList.remove('hidden');
                return;
            }

            try {
                let res = await fetch('/api/account/send-deletion-code', {
                    method: 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ password: pass })
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    statusDiv.innerText = `✅ ${data.message}`;
                    statusDiv.classList.remove('hidden');
                    const cooldownUntil = Date.now() + (5 * 60 * 1000);
                    localStorage.setItem('del_code_cooldown', cooldownUntil);
                    checkDelCodeCooldown();
                    showToast('Código de verificación enviado a tu correo.', 'info');
                } else {
                    errDiv.innerText = data.message || 'Error al enviar código.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión al solicitar el código.';
                errDiv.classList.remove('hidden');
            }
        }

        async function handleDeleteAccount(e) {
            e.preventDefault();
            const pass = document.getElementById('deletePasswordInput').value;
            const code = document.getElementById('deleteCodeInput').value;
            const errDiv = document.getElementById('deleteAccountError');
            errDiv.classList.add('hidden');

            try {
                let res = await fetch('/api/account', {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ password: pass, verification_code: code })
                });
                res = handleApiResponse(res);
                if (!res) return;

                const data = await res.json();
                if (data.success) {
                    closeDeleteAccountModal();
                    showToast('Cuenta eliminada de forma permanente.', 'info');
                    localStorage.clear();
                    token = null;
                    user = null;
                    clearAuthForms();
                    switchAuthTab('login');
                    document.getElementById('authModal').classList.remove('hidden');
                    checkAuth();
                } else {
                    errDiv.innerText = data.message || 'Error al eliminar cuenta.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión.';
                errDiv.classList.remove('hidden');
            }
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ContaFit Agenda') }} | Dashboard</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .card-panel {
            background-color: #111827;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-bg {
            background-color: rgba(0, 0, 0, 0.8);
        }
        .modal-box {
            background-color: #111827;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="font-sans text-slate-100 bg-[#0b0f17] min-h-screen">

    <!-- BARRA SUPERIOR (RF-01, RF-02, RF-03) -->
    <nav class="w-full bg-[#0f172a] border-b border-white/10 px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-sky-400 flex items-center justify-center font-bold text-slate-950 text-lg">C</div>
            <span class="font-bold text-lg text-white tracking-tight">ContaFit Agenda Web</span>
        </div>
        
        <div class="flex items-center gap-3">
            <div id="authHeader" class="flex items-center gap-3"></div>
        </div>
    </nav>

    <!-- DASHBOARD PRINCIPAL -->
    <div class="p-4 lg:p-6">
        <div class="mx-auto flex min-h-[calc(100vh-6rem)] max-w-[1600px] flex-col gap-5 xl:flex-row">
            
            <!-- SIDEBAR IZQUIERDO: DASHBOARD DE TAREAS Y RECORDATORIOS DEL DÍA (RF-08) -->
            <aside class="w-full xl:w-[380px] flex flex-col gap-5 rounded-3xl card-panel p-5 shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-400">DASHBOARD DIARIO (RF-08)</p>
                        <h1 class="mt-1 text-2xl font-bold text-white">Tareas del Día</h1>
                    </div>
                    <div id="todayDayBadge" class="grid h-11 w-11 place-items-center rounded-2xl bg-indigo-600 font-bold text-white shadow-md">
                        {{ date('d') }}
                    </div>
                </div>

                <button onclick="openCreateModal()" class="w-full flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 py-3 text-sm font-semibold text-white transition shadow-md">
                    <span>+</span> Crear Evento / Tarea
                </button>

                <!-- LISTA CHECKLIST DE TAREAS Y RECORDATORIOS DE HOY (RF-08) -->
                <section class="flex-1 overflow-y-auto max-h-[580px] space-y-3">
                    <div class="flex items-center justify-between text-sm border-b border-white/10 pb-2">
                        <h2 class="font-semibold text-white">Pendientes de hoy</h2>
                        <div class="flex items-center gap-2">
                            <label class="text-[10px] text-slate-400 flex items-center gap-1 cursor-pointer">
                                <input type="checkbox" id="showCompletedToggle" onchange="fetchTodayTasks()" class="rounded border-white/20 text-indigo-600 focus:ring-0">
                                Ver completadas
                            </label>
                            <span id="taskCountBadge" class="rounded-full bg-indigo-500/20 text-indigo-300 px-2 py-0.5 text-[11px] font-semibold">0 pendientes</span>
                        </div>
                    </div>

                    <div id="todayTaskList" class="space-y-2">
                        <div class="p-4 text-center text-slate-400 text-xs">Cargando tareas del día...</div>
                    </div>
                </section>
            </aside>

            <!-- CALENDARIO PRINCIPAL Y FILTROS (RF-04, RF-05, RF-06, RF-07) -->
            <main class="flex-1 flex flex-col gap-4 rounded-3xl card-panel p-5 shadow-xl min-w-0">
                
                <!-- CONTROLES Y BARRA DE FILTROS EN TIEMPO REAL (RF-07) -->
                <header class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between border-b border-white/10 pb-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-indigo-400">Módulo 2: Calendario y Eventos</p>
                        <div class="mt-1 flex items-center gap-3">
                            <button onclick="navigateMonth(-1)" class="h-9 w-9 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold transition flex items-center justify-center">‹</button>
                            <h2 id="calendarTitle" class="text-2xl font-bold text-white min-w-[220px] text-center">Mes {{ date('Y') }}</h2>
                            <button onclick="navigateMonth(1)" class="h-9 w-9 rounded-xl bg-white/5 hover:bg-white/10 text-white font-bold transition flex items-center justify-center">›</button>
                        </div>
                    </div>

                    <!-- FILTROS Y BÚSQUEDA EN TIEMPO REAL (RF-07) -->
                    <div class="flex flex-wrap items-center gap-3">
                        <input type="text" id="searchInput" oninput="applyFilters()" placeholder="🔎 Buscar evento o nota..." class="rounded-xl border border-white/10 bg-[#111827] px-3 py-2 text-xs text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 min-w-[200px]">

                        <select id="typeFilter" onchange="applyFilters()" class="rounded-xl border border-white/10 bg-[#1e293b] px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Todas las Categorías</option>
                            <option value="tarea">📝 Tarea</option>
                            <option value="recordatorio">⏰ Recordatorio</option>
                            <option value="fecha_importante">📌 Fecha Importante</option>
                        </select>

                        <select id="statusFilter" onchange="applyFilters()" class="rounded-xl border border-white/10 bg-[#1e293b] px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500">
                            <option value="">Todos los Estados</option>
                            <option value="pendiente">⏳ Pendiente</option>
                            <option value="en_progreso">🚀 En Progreso</option>
                            <option value="completada">✅ Completada</option>
                        </select>
                    </div>
                </header>

                <!-- GRID DEL CALENDARIO MENSUAL -->
                <section class="flex-1 flex flex-col">
                    <div class="grid grid-cols-7 gap-2 pb-2 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                    </div>

                    <div id="calendarGrid" class="grid grid-cols-7 gap-2 flex-1 overflow-y-auto max-h-[580px]">
                        <!-- Renderizado JS -->
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- MODAL DE DETALLE DE DÍA Y GESTIÓN DE EVENTOS (RF-05, RF-06) -->
    <div id="dayViewModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-xl rounded-3xl modal-box p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <div>
                    <h3 id="dayViewTitle" class="text-base font-bold text-white">📅 Eventos del Día</h3>
                    <p class="text-xs text-slate-400">Ver, editar o agregar eventos para esta fecha</p>
                </div>
                <button onclick="closeDayViewModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <div id="dayViewContent" class="flex-1 overflow-y-auto space-y-3 pr-1">
                <!-- Carga dinámica JS de eventos del día -->
            </div>

            <div class="border-t border-white/10 pt-3 flex justify-end gap-3">
                <button onclick="closeDayViewModal()" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-xs text-slate-300">Cerrar</button>
                <button id="addEventOnDayBtn" onclick="openCreateModalFromDayView()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold text-white transition">+ Añadir Evento en esta Fecha</button>
            </div>
        </div>
    </div>

    <!-- MODAL CONFIRMACIÓN BORRADO DE EVENTO RECURRENTE (RF-06) -->
    <div id="deleteRecurringModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-bold text-amber-400">Opciones de Eliminación de Evento Recurrente (RF-06)</h3>
                <button onclick="closeDeleteRecurringModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-300">Este evento es recurrente. ¿Cómo deseas proceder con la eliminación?</p>

            <div class="space-y-2 pt-2">
                <button onclick="confirmDeleteRecurringInstance()" class="w-full py-3 px-4 rounded-xl border border-amber-500/30 bg-amber-500/10 hover:bg-amber-500/20 text-amber-200 text-xs font-semibold text-left transition flex items-center justify-between">
                    <span>1. Eliminar SOLO la ocurrencia de esta fecha</span>
                    <span>→</span>
                </button>
                <button onclick="confirmDeleteRecurringSeries()" class="w-full py-3 px-4 rounded-xl border border-red-500/30 bg-red-500/10 hover:bg-red-500/20 text-red-200 text-xs font-semibold text-left transition flex items-center justify-between">
                    <span>2. Eliminar TODA la serie y eventos futuros</span>
                    <span>→</span>
                </button>
            </div>

            <div class="pt-2 flex justify-end">
                <button onclick="closeDeleteRecurringModal()" class="px-4 py-2 rounded-xl border border-white/10 bg-white/5 text-xs text-slate-300">Cancelar</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE AUTENTICACIÓN OBLIGATORIO AL INICIAR (LOGIN / REGISTRO) (RF-01, RF-02) -->
    <div id="authModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex border-b border-white/10 mb-4">
                <button id="tabLoginBtn" onclick="switchAuthTab('login')" class="flex-1 py-3 text-center text-sm font-semibold text-indigo-400 border-b-2 border-indigo-500">Iniciar Sesión (RF-02)</button>
                <button id="tabRegisterBtn" onclick="switchAuthTab('register')" class="flex-1 py-3 text-center text-sm font-semibold text-slate-400">Registrarse (RF-01)</button>
            </div>

            <!-- LOGIN FORM -->
            <form id="loginForm" onsubmit="handleLogin(event)" class="space-y-4">
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Correo Electrónico</label>
                    <input type="email" id="loginEmail" required placeholder="tuemail@ejemplo.com" class="w-full rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Contraseña</label>
                    <input type="password" id="loginPassword" required placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 p-3 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div id="loginError" class="text-xs text-red-400 hidden"></div>
                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-semibold text-white text-xs transition">Ingresar</button>
            </form>

            <!-- REGISTER FORM -->
            <form id="registerForm" onsubmit="handleRegister(event)" class="space-y-3 hidden">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Primer Nombre *</label>
                        <input type="text" id="regFirstName" required placeholder="Juan" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Segundo Nombre</label>
                        <input type="text" id="regMiddleName" placeholder="Carlos" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Primer Apellido *</label>
                        <input type="text" id="regLastName" required placeholder="Pérez" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Segundo Apellido</label>
                        <input type="text" id="regSecondLastName" placeholder="Gómez" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Correo Electrónico *</label>
                    <input type="email" id="regEmail" required placeholder="tuemail@ejemplo.com" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Contraseña Segura *</label>
                    <input type="password" id="regPassword" required placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Confirmar Contraseña *</label>
                    <input type="password" id="regPasswordConfirm" required placeholder="••••••••" class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                </div>
                <div id="regError" class="text-xs text-red-400 hidden"></div>
                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-semibold text-white text-xs transition">Crear Cuenta</button>
            </form>
        </div>
    </div>

    <!-- MODAL CREAR / EDITAR EVENTO (RF-04, RF-05, RF-06) -->
    <div id="createModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-lg rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 id="eventFormModalTitle" class="text-base font-bold text-white">Configurar Evento (RF-04, RF-05)</h3>
                <button onclick="closeCreateModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <form onsubmit="handleSaveEvent(event)" class="space-y-3">
                <input type="hidden" id="editingEventId" value="">

                <div>
                    <label class="block text-xs text-slate-300 mb-1">Título *</label>
                    <input type="text" id="evtTitle" required placeholder="ej. Pagar servicios o Reunión" class="w-full rounded-xl border border-white/10 bg-white/5 p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Descripción / Nota</label>
                    <textarea id="evtDescription" rows="2" placeholder="Detalles adicionales..." class="w-full rounded-xl border border-white/10 bg-white/5 p-2.5 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Tipo de Evento *</label>
                        <select id="evtType" class="w-full rounded-xl border border-white/10 bg-[#1e293b] p-2.5 text-xs text-white focus:outline-none">
                            <option value="tarea">📝 Tarea</option>
                            <option value="recordatorio">⏰ Recordatorio</option>
                            <option value="fecha_importante">📌 Fecha Importante</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Color Identificativo</label>
                        <input type="color" id="evtColor" value="#3B82F6" class="w-full h-9 rounded-xl border border-white/10 bg-white/5 p-1 cursor-pointer">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Hora Inicio *</label>
                        <input type="datetime-local" id="evtStartAt" required class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">Hora Fin *</label>
                        <input type="datetime-local" id="evtEndAt" required class="w-full rounded-xl border border-white/10 bg-white/5 p-2 text-xs text-white focus:outline-none">
                    </div>
                </div>

                <!-- EVENTOS RECURRENTES (RF-06) -->
                <div class="border-t border-white/10 pt-3">
                    <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                        <input type="checkbox" id="evtIsRecurring" onchange="toggleRecurrenceOptions()" class="rounded border-white/10 text-indigo-600 focus:ring-0">
                        Eventos Recurrentes (RF-06)
                    </label>
                    
                    <div id="recurrenceOptions" class="mt-2 hidden">
                        <label class="block text-xs text-slate-300 mb-1">Regla de Repetición</label>
                        <select id="evtRecurrenceFreq" class="w-full rounded-xl border border-white/10 bg-[#1e293b] p-2 text-xs text-white">
                            <option value="diaria">Diaria (Todos los días)</option>
                            <option value="semanal">Semanal (Mismo día de la semana)</option>
                            <option value="mensual">Mensual (Mismo día del mes)</option>
                            <option value="anual">Anual (Misma fecha cada año)</option>
                        </select>
                    </div>
                </div>

                <div id="createError" class="text-xs text-red-400 hidden"></div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeCreateModal()" class="flex-1 py-2.5 rounded-xl border border-white/10 bg-white/5 text-slate-300 text-xs">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 font-semibold text-white text-xs transition">Guardar Evento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL BORRADO DE CUENTA (RF-03) -->
    <div id="deleteAccountModal" class="fixed inset-0 z-50 flex items-center justify-center modal-bg p-4 hidden">
        <div class="w-full max-w-md rounded-3xl modal-box p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between border-b border-white/10 pb-3">
                <h3 class="text-base font-bold text-red-400">Solicitar Borrado de Cuenta (RF-03)</h3>
                <button onclick="closeDeleteAccountModal()" class="text-slate-400 hover:text-white text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-300">Esta acción eliminará de forma permanente (hard delete) tu usuario, eventos y registros asociados.</p>

            <form onsubmit="handleDeleteAccount(event)" class="space-y-3">
                <div>
                    <label class="block text-xs text-slate-300 mb-1">Ingresa tu contraseña para confirmar *</label>
                    <input type="password" id="deletePasswordInput" required placeholder="••••••••" class="w-full rounded-xl border border-red-500/30 bg-black/50 p-2.5 text-xs text-white focus:outline-none">
                </div>
                <div id="deleteAccountError" class="text-xs text-red-400 hidden"></div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteAccountModal()" class="flex-1 py-2.5 rounded-xl border border-white/10 bg-white/5 text-slate-300 text-xs">Cancelar</button>
                    <button type="submit" class="flex-1 py-2.5 rounded-xl bg-red-600 hover:bg-red-500 font-semibold text-white text-xs transition">Confirmar Baja</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT CORREGIDO -->
    <script>
        let token = null;
        let user = null;
        let currentEvents = [];
        let currentDate = new Date();
        let selectedDayDateStr = null;
        let pendingDeleteEventId = null;

        document.addEventListener('DOMContentLoaded', () => {
            localStorage.clear();
            checkAuth();
        });

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

        function checkAuth() {
            const header = document.getElementById('authHeader');
            const dayBadge = document.getElementById('todayDayBadge');
            if (dayBadge) dayBadge.innerText = new Date().getDate();

            if (!token || !user) {
                document.getElementById('authModal').classList.remove('hidden');
                header.innerHTML = `<button onclick="openAuthModal()" class="px-3 py-1.5 rounded-xl bg-indigo-600 text-xs font-semibold text-white hover:bg-indigo-500">Ingresar / Registro</button>`;
            } else {
                document.getElementById('authModal').classList.add('hidden');
                header.innerHTML = `
                    <span class="text-xs font-semibold text-slate-200">👋 <strong>${user.name}</strong></span>
                    <button onclick="openDeleteAccountModal()" class="px-2.5 py-1 rounded-xl border border-red-500/30 bg-red-500/10 text-[11px] text-red-300 hover:bg-red-500/20 transition">Borrar Cuenta (RF-03)</button>
                    <button onclick="handleLogout()" class="px-2.5 py-1 rounded-xl border border-white/10 bg-white/5 text-[11px] text-slate-300 hover:bg-white/10 transition">Salir</button>
                `;
                loadDashboardData();
            }
        }

        function switchAuthTab(tab) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const tabLoginBtn = document.getElementById('tabLoginBtn');
            const tabRegisterBtn = document.getElementById('tabRegisterBtn');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                tabLoginBtn.className = 'flex-1 py-3 text-center text-sm font-semibold text-indigo-400 border-b-2 border-indigo-500';
                tabRegisterBtn.className = 'flex-1 py-3 text-center text-sm font-semibold text-slate-400';
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                tabRegisterBtn.className = 'flex-1 py-3 text-center text-sm font-semibold text-indigo-400 border-b-2 border-indigo-500';
                tabLoginBtn.className = 'flex-1 py-3 text-center text-sm font-semibold text-slate-400';
            }
        }

        function openAuthModal() { document.getElementById('authModal').classList.remove('hidden'); }

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
                    token = data.data.auth_token;
                    user = data.data.user;
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user_data', JSON.stringify(user));
                    checkAuth();
                } else {
                    errDiv.innerText = data.message || 'Credenciales inválidas.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión.';
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
                    token = data.data.auth_token;
                    user = data.data.user;
                    localStorage.setItem('auth_token', token);
                    localStorage.setItem('user_data', JSON.stringify(user));
                    checkAuth();
                } else {
                    const msg = data.data?.errors ? Object.values(data.data.errors).flat().join('<br>') : (data.message || 'Error al registrar.');
                    errDiv.innerHTML = msg;
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión.';
                errDiv.classList.remove('hidden');
            }
        }

        function handleLogout() {
            localStorage.clear();
            token = null; user = null;
            currentEvents = [];
            document.getElementById('todayTaskList').innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">Por favor inicia sesión.</div>`;
            document.getElementById('calendarGrid').innerHTML = '';
            checkAuth();
        }

        async function loadDashboardData() {
            await fetchTodayTasks();
            await applyFilters();
        }

        // RF-08: DASHBOARD CHECKLIST CON OPCIÓN DE VER Y DESMARCAR COMPLETADAS
        async function fetchTodayTasks() {
            const list = document.getElementById('todayTaskList');
            const badge = document.getElementById('taskCountBadge');
            const showCompleted = document.getElementById('showCompletedToggle')?.checked || false;
            if (!token) return;

            const localTodayStr = getLocalYYYYMMDD();

            try {
                const res = await fetch(`/api/dashboard/today?date=${localTodayStr}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    let tasks = data.data.events || [];
                    
                    if (!showCompleted) {
                        tasks = tasks.filter(t => t.status !== 'completada');
                    }

                    badge.innerText = `${tasks.length} tareas`;

                    if (tasks.length === 0) {
                        list.innerHTML = `<div class="p-4 text-center text-slate-400 text-xs">🎉 No tienes tareas pendientes para hoy.</div>`;
                        return;
                    }

                    list.innerHTML = tasks.map(t => {
                        const timeStr = new Date(t.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const isCompleted = t.status === 'completada';

                        return `
                            <div class="rounded-xl border border-white/10 bg-[#1e293b] p-3 space-y-2 ${isCompleted ? 'opacity-60 line-through' : ''}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" ${isCompleted ? 'checked' : ''} onchange="changeTaskStatus(${t.id}, this.checked ? 'completada' : 'pendiente')" class="h-4 w-4 rounded border-white/20 text-indigo-600 focus:ring-0 cursor-pointer">
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400">${timeStr} - ${t.type.toUpperCase()}</p>
                                            <h3 class="text-xs font-semibold text-white">${t.title}</h3>
                                        </div>
                                    </div>
                                    <span class="h-2.5 w-2.5 rounded-full shrink-0" style="background-color: ${t.color}"></span>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-300 pt-1.5 border-t border-white/5">
                                    <span class="text-[10px] font-medium text-indigo-300">Estado: <strong>${t.status}</strong></span>
                                    <div class="flex gap-2">
                                        ${isCompleted ? 
                                            `<button onclick="changeTaskStatus(${t.id}, 'pendiente')" class="text-amber-400 hover:text-amber-300 text-[10px] font-semibold">↩️ Desmarcar</button>` : 
                                            `<button onclick="changeTaskStatus(${t.id}, 'en_progreso')" class="text-indigo-400 hover:text-indigo-300 text-[10px] font-semibold">🚀 En Progreso</button>`
                                        }
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            } catch (err) { console.error(err); }
        }

        // RF-07: FILTROS Y BÚSQUEDA EN TIEMPO REAL
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
                const res = await fetch(`/api/events?${params.toString()}`, {
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    currentEvents = data.data.events || [];
                    renderCalendar();
                }
            } catch (err) { console.error(err); }
        }

        async function navigateMonth(direction) {
            currentDate.setMonth(currentDate.getMonth() + direction);
            renderCalendar();
        }

        function eventMatchesDate(e, dateStr) {
            const eventStart = e.start_at.slice(0, 10);
            if (eventStart === dateStr) return true;
            if (!e.is_recurring) return false;
            if (dateStr < eventStart) return false;

            const evalDate = new Date(dateStr + 'T00:00:00');
            const startDate = new Date(eventStart + 'T00:00:00');

            switch (e.recurrence_frequency) {
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

                cellsHTML += `
                    <div onclick="openDayViewModal('${dateKey}')" class="relative flex min-h-[90px] flex-col overflow-hidden rounded-xl border border-white/10 p-2 text-white cursor-pointer hover:border-indigo-500/80 transition ${isToday ? 'ring-2 ring-indigo-500 bg-indigo-950/40' : 'bg-white/5'}">
                        <div class="flex items-start justify-between">
                            <span class="text-xs font-semibold text-slate-300">${day}</span>
                            ${isToday ? `<span class="rounded bg-indigo-500 px-1 py-0.2 text-[8px] font-bold text-slate-950">HOY</span>` : ''}
                        </div>

                        <div class="mt-1 space-y-1 overflow-y-auto max-h-[60px]">
                            ${dayEvents.map(e => `
                                <div class="rounded px-1 py-0.5 text-[9px] font-semibold text-white truncate flex items-center justify-between" style="background-color: ${e.color}" title="${e.title}">
                                    <span>${e.is_recurring ? '🔄 ' : ''}${e.title}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }

            grid.innerHTML = cellsHTML;
        }

        // MODAL VER EVENTOS DEL DÍA (RF-05, RF-06)
        function openDayViewModal(dateStr) {
            selectedDayDateStr = dateStr;
            document.getElementById('dayViewTitle').innerText = `📅 Eventos del Día (${dateStr})`;
            
            const container = document.getElementById('dayViewContent');
            const dayEvents = currentEvents.filter(e => eventMatchesDate(e, dateStr));

            let html = '';

            if (dayEvents.length === 0) {
                html += `<div class="p-6 text-center text-slate-400 text-xs">No hay eventos ni tareas registradas para esta fecha.</div>`;
            } else {
                html += `<div class="space-y-2">
                    <h4 class="text-xs font-bold text-indigo-400 uppercase tracking-wider">Eventos Registrados</h4>
                    ${dayEvents.map(e => `
                        <div class="rounded-xl border border-white/10 bg-[#1e293b] p-3 space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="text-[10px] font-bold text-indigo-300 uppercase">${e.type} ${e.is_recurring ? '(Recurrente 🔄)' : ''}</span>
                                    <h4 class="text-xs font-bold text-white">${e.title}</h4>
                                    <p class="text-[11px] text-slate-300 mt-0.5">${e.description || 'Sin descripción'}</p>
                                    <p class="text-[10px] text-slate-400 mt-1">Horario: ${e.start_at.slice(11, 16)} ${e.end_at ? '- ' + e.end_at.slice(11, 16) : ''}</p>
                                </div>
                                <span class="h-3 w-3 rounded-full shrink-0" style="background-color: ${e.color}"></span>
                            </div>

                            <div class="flex items-center justify-between text-xs border-t border-white/5 pt-2">
                                <span class="text-[10px] text-slate-400">Estado: <strong>${e.status}</strong></span>
                                <div class="flex gap-2">
                                    <button onclick="openEditModal(${e.id})" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[10px] font-semibold">✏️ Editar</button>
                                    <button onclick="promptDeleteEvent(${e.id})" class="px-2.5 py-1 rounded-lg bg-red-600 hover:bg-red-500 text-white text-[10px] font-semibold">🗑️ Eliminar</button>
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

        // PREGUNTA SI DESEA ELIMINAR SOLO ESTA OCURRENCIA O TODA LA SERIE (RF-06)
        function promptDeleteEvent(id) {
            const event = currentEvents.find(e => e.id === id);
            if (!event) return;

            if (event.is_recurring || event.recurrence_parent_id) {
                pendingDeleteEventId = id;
                document.getElementById('deleteRecurringModal').classList.remove('hidden');
            } else {
                if (confirm('¿Eliminar este evento?')) {
                    executeDeleteEvent(id, false);
                }
            }
        }

        function closeDeleteRecurringModal() {
            document.getElementById('deleteRecurringModal').classList.add('hidden');
            pendingDeleteEventId = null;
        }

        // ELIMINA SOLO ESTA INSTANCIA (RF-06)
        async function confirmDeleteRecurringInstance() {
            if (!pendingDeleteEventId) return;
            const id = pendingDeleteEventId;
            closeDeleteRecurringModal();
            
            try {
                const res = await fetch(`/api/events/${id}/instance`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    closeDayViewModal();
                    await fetchTodayTasks();
                    await applyFilters();
                }
            } catch (err) { console.error(err); }
        }

        // ELIMINA TODA LA SERIE COMPLETA
        async function confirmDeleteRecurringSeries() {
            if (!pendingDeleteEventId) return;
            const id = pendingDeleteEventId;
            closeDeleteRecurringModal();
            await executeDeleteEvent(id, true);
        }

        async function executeDeleteEvent(id) {
            try {
                const res = await fetch(`/api/events/${id}`, {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    closeDayViewModal();
                    await fetchTodayTasks();
                    await applyFilters();
                }
            } catch (err) { console.error(err); }
        }

        // EDITAR EVENTO (RF-05)
        function openEditModal(eventId) {
            closeDayViewModal();
            const event = currentEvents.find(e => e.id === eventId);
            if (!event) return;

            document.getElementById('editingEventId').value = event.id;
            document.getElementById('eventFormModalTitle').innerText = '✏️ Editar Evento';
            document.getElementById('evtTitle').value = event.title || '';
            document.getElementById('evtDescription').value = event.description || '';
            document.getElementById('evtType').value = event.type || 'tarea';
            document.getElementById('evtColor').value = event.color || '#3B82F6';
            
            document.getElementById('evtStartAt').value = event.start_at ? event.start_at.replace(' ', 'T').slice(0, 16) : getLocalDateTimeString(0);
            document.getElementById('evtEndAt').value = event.end_at ? event.end_at.replace(' ', 'T').slice(0, 16) : getLocalDateTimeString(1);
            
            document.getElementById('evtIsRecurring').checked = !!event.is_recurring;
            document.getElementById('evtRecurrenceFreq').value = event.recurrence_frequency || 'diaria';
            toggleRecurrenceOptions();

            document.getElementById('createModal').classList.remove('hidden');
        }

        function openCreateModal(customDate = null) {
            document.getElementById('editingEventId').value = '';
            document.getElementById('eventFormModalTitle').innerText = 'Configurar Evento (RF-04, RF-05)';
            document.getElementById('evtTitle').value = '';
            document.getElementById('evtDescription').value = '';
            document.getElementById('evtType').value = 'tarea';
            document.getElementById('evtColor').value = '#3B82F6';
            document.getElementById('evtIsRecurring').checked = false;
            document.getElementById('evtRecurrenceFreq').value = 'diaria';
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
                const res = await fetch(`/api/events/${id}/status`, {
                    method: 'PATCH',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ status: status })
                });
                const data = await res.json();
                if (data.success) {
                    await fetchTodayTasks();
                    await applyFilters();
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

            const payload = {
                title: document.getElementById('evtTitle').value,
                description: document.getElementById('evtDescription').value,
                type: document.getElementById('evtType').value,
                color: document.getElementById('evtColor').value,
                start_at: document.getElementById('evtStartAt').value.replace('T', ' ') + ':00',
                end_at: document.getElementById('evtEndAt').value ? document.getElementById('evtEndAt').value.replace('T', ' ') + ':00' : null,
                is_recurring: isRecurring,
            };

            if (isRecurring) payload.recurrence_frequency = document.getElementById('evtRecurrenceFreq').value;

            const url = editingId ? `/api/events/${editingId}` : '/api/events';
            const method = editingId ? 'PATCH' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    closeCreateModal();
                    await fetchTodayTasks();
                    await applyFilters();
                } else {
                    errDiv.innerText = data.message || 'Error al guardar el evento.';
                    errDiv.classList.remove('hidden');
                }
            } catch (err) {
                errDiv.innerText = 'Error de conexión.';
                errDiv.classList.remove('hidden');
            }
        }

        // RF-03: SOLICITUD DE BORRADO DE CUENTA
        function openDeleteAccountModal() { document.getElementById('deleteAccountModal').classList.remove('hidden'); }
        function closeDeleteAccountModal() { document.getElementById('deleteAccountModal').classList.add('hidden'); }

        async function handleDeleteAccount(e) {
            e.preventDefault();
            const pass = document.getElementById('deletePasswordInput').value;
            const errDiv = document.getElementById('deleteAccountError');
            errDiv.classList.add('hidden');

            try {
                const res = await fetch('/api/account', {
                    method: 'DELETE',
                    headers: { 'Authorization': `Bearer ${token}`, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ password: pass })
                });
                const data = await res.json();
                if (data.success) {
                    alert('Cuenta eliminada de forma permanente.');
                    handleLogout();
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

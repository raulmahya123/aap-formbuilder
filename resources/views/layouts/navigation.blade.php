@php
    $user = auth()->user();
    $isSuper = $user?->isSuperAdmin() ?? false;
    $navItemClass = function (bool $isActive) {
        return $isActive ? 'ui-nav-item is-active' : 'ui-nav-item';
    };
    $subItemClass = function (bool $isActive) {
        return $isActive
            ? 'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-semibold bg-[#1646A0] text-white transition'
            : 'flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition';
    };

    // Auto open dropdown if current URL matches child routes
    $isShePerfActive = request()->is('*/safety*') || request()->is('*/performance/*') || request()->is('*/manhours*') || request()->is('*/targets*');
    $isSapActive = request()->is('*/sap*');
    $isRiskActive = request()->is('*/risk*') || request()->is('*/hipo*') || request()->is('*/ccm-reports*');
    $isPicaActive = request()->is('*/pica*');
    $isDocActive = request()->is('*/documents*');
    $isOpsActive = request()->is('*/overview*') || request()->is('*/incidents*') || request()->is('*/inspections*') || request()->is('*/training*') || request()->is('*/environment*') || request()->is('*/sites*') || request()->is('*/reports*') || request()->is('*/notifications*');
    $isMasterActive = request()->routeIs('admin.departments.*') || request()->routeIs('admin.sites.*') || request()->routeIs('admin.companies.*') || request()->routeIs('admin.groups.*') || request()->routeIs('admin.indicators.*') || request()->routeIs('admin.forms.*') || request()->routeIs('user.forms.*');
    $isK3lhActive = request()->routeIs('admin.daily.*') || request()->routeIs('admin.qa.*') || request()->routeIs('admin.wellness.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.ccm.*');
@endphp

<aside id="app-sidebar" aria-label="Navigasi utama" x-show="isDesktop || sidebarOpen" x-cloak
  :class="{ 'translate-x-0': sidebarOpen, 'ui-sidebar--collapsed': sidebarCollapsed && isDesktop }"
  :inert="!isDesktop && !sidebarOpen"
  class="ui-sidebar fixed inset-y-0 left-0 z-50 w-64 -translate-x-full bg-white border-r border-[#D4D4D8] transition-transform duration-200 lg:static lg:translate-x-0 lg:w-auto lg:block overflow-y-auto">
  <button type="button" class="ui-drawer-close ui-shell-toggle lg:hidden" @click="sidebarOpen=false; $nextTick(() => document.getElementById('navigation-toggle').focus())" aria-label="Tutup navigasi"><x-ui.icon name="x" /></button>
  <nav aria-label="Menu aplikasi">
  <div class="flex items-center justify-center border-b border-[#D4D4D8] bg-white px-4 py-5">
    <a href="{{ route('dashboard') }}" class="flex items-center justify-center">
      <img
        src="{{ asset('assets/images/logomandala.png') }}"
        alt="Mandala Management System Andalan"
        class="h-auto transition-all"
        :class="sidebarCollapsed ? 'w-10' : 'w-36'"
      />
    </a>
  </div>
  <div class="mt-4 grid gap-3">
    {{-- ================= ADMIN ================= --}}
    @if($isSuper || ($user && $user->isAdmin()))

    {{-- 1. BERANDA / DASHBOARD --}}
    <div>
      <div class="grid gap-1">
        @if(Route::has('dashboard'))
        @if($user?->canUseFeatureRoute('dashboard'))
          <a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}">
            <x-ui-icon name="dashboard"/>
            <span class="ui-nav-label font-semibold">Dashboard Utama</span>
          </a>
        @endif
        @endif
      </div>
    </div>

    {{-- 2. OPERASIONAL K3LH (DROPDOWN) --}}
    <div x-data="{ openK3lh: {{ $isK3lhActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openK3lh = !openK3lh" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openK3lh }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="shield"/>
          <span class="ui-nav-label">Operasional K3LH</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openK3lh ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openK3lh" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if(Route::has('admin.daily.index'))
        @if($user?->canUseFeatureRoute('admin.daily.index'))
          <a title="Input Data Operasional" href="{{ route('admin.daily.index') }}" class="{{ $subItemClass(request()->routeIs('admin.daily.*')) }}">Input Data Operasional</a>
        @endif
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
          <a title="HIPO & Nearmiss" href="{{ route('admin.qa.index') }}" class="{{ $subItemClass(request()->routeIs('admin.qa.*')) }}">HIPO & Nearmiss</a>
        @endif
        @endif

        @if(Route::has('admin.wellness.index'))
        @if($user?->canUseFeatureRoute('admin.wellness.index'))
          <a title="Health Assesment" href="{{ route('admin.wellness.index') }}" class="{{ $subItemClass(request()->routeIs('admin.wellness.*')) }}">Health Assesment</a>
        @endif
        @endif

        @if(Route::has('admin.reports.index'))
        @if($user?->canUseFeatureRoute('admin.reports.index'))
          <a title="Dashboard KPI & Statistik" href="{{ route('admin.reports.index') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.index')) }}">Dashboard KPI & Statistik</a>
        @endif
        @endif

        @if(Route::has('admin.reports.annual'))
        @if($user?->canUseFeatureRoute('admin.reports.annual'))
          <a title="Laporan Tahunan" href="{{ route('admin.reports.annual') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.annual')) }}">Laporan Tahunan</a>
        @endif
        @endif

        @if(Route::has('admin.reports.quarterly'))
        @if($user?->canUseFeatureRoute('admin.reports.quarterly'))
          <a title="Laporan Triwulan" href="{{ route('admin.reports.quarterly') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.quarterly')) }}">Laporan Triwulan</a>
        @endif
        @endif

        @if(Route::has('admin.reports.aggregate'))
        @if($user?->canUseFeatureRoute('admin.reports.aggregate'))
          <a title="Laporan Agregat" href="{{ route('admin.reports.aggregate') }}" class="{{ $subItemClass(request()->routeIs('admin.reports.aggregate')) }}">Laporan Agregat</a>
        @endif
        @endif

        @if(Route::has('admin.daily.summary'))
        @if($user?->canUseFeatureRoute('admin.daily.summary'))
          <a title="Matriks Input" href="{{ route('admin.daily.summary') }}" class="{{ $subItemClass(request()->routeIs('admin.daily.summary')) }}">Matriks Input</a>
        @endif
        @endif

        @if(Route::has('admin.ccm.index'))
        @if($user?->canUseFeatureRoute('admin.ccm.index'))
          <a title="Laporan CCM" href="{{ route('admin.ccm.index') }}" class="{{ $subItemClass(request()->routeIs('admin.ccm.*')) }}">Laporan CCM</a>
        @endif
        @endif
      </div>
    </div>

    @if(Route::has('hse.dashboard.overview'))
    @if($user?->canUseFeatureRoute('hse.dashboard.overview'))

    {{-- SECTION DIVIDER HEADER --}}
    <div class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
      SHE Management Modules
    </div>

    {{-- 3. SHE PERFORMANCE (DROPDOWN) --}}
    <div x-data="{ openShePerf: {{ $isShePerfActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openShePerf = !openShePerf" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openShePerf }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="chart"/>
          <span class="ui-nav-label">SHE Performance</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openShePerf ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openShePerf" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.safety') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.safety')) }}">Performance Dashboard</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'fatality') }}" class="{{ $subItemClass(request()->is('*/performance/fatality')) }}">Fatality Monitoring</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'lti') }}" class="{{ $subItemClass(request()->is('*/performance/lti')) }}">LTI Monitoring</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'non-lti') }}" class="{{ $subItemClass(request()->is('*/performance/non-lti')) }}">Injury Non-LTI</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'property-damage') }}" class="{{ $subItemClass(request()->is('*/performance/property-damage')) }}">Property Damage</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'pdfr') }}" class="{{ $subItemClass(request()->is('*/performance/pdfr')) }}">PDFR Monitoring</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'pd-cost') }}" class="{{ $subItemClass(request()->is('*/performance/pd-cost')) }}">Property Damage Cost</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'environmental') }}" class="{{ $subItemClass(request()->is('*/performance/environmental')) }}">Environmental Accident</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'pak') }}" class="{{ $subItemClass(request()->is('*/performance/pak')) }}">PAK Monitoring</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'kaptk') }}" class="{{ $subItemClass(request()->is('*/performance/kaptk')) }}">KAPTK Monitoring</a>
        <a href="{{ route('hse.dashboard.performance.indicator', 'near-miss') }}" class="{{ $subItemClass(request()->is('*/performance/near-miss')) }}">Near Miss</a>
        <a href="{{ route('hse.dashboard.manhours.index') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.manhours.*')) }}">Manhours Management</a>
        <a href="{{ route('hse.dashboard.targets.index') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.targets.*')) }}">KPI Target Management</a>
      </div>
    </div>

    {{-- 4. SAFETY ACCOUNTABILITY PROGRAM / SAP (DROPDOWN) --}}
    <div x-data="{ openSapGroup: {{ $isSapActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openSapGroup = !openSapGroup" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openSapGroup }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="users"/>
          <span class="ui-nav-label">Safety Accountability (SAP)</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openSapGroup ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openSapGroup" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.sap.index') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.sap.index')) }}">SAP Dashboard</a>
        <a href="{{ route('hse.sap.inspeksi.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.sap.inspeksi.*')) }}">SAP Inspeksi SHE/HSE (9 Form)</a>
        <a href="{{ route('hse.sap.pto.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.sap.pto.*')) }}">PTO (Planned Task Observation)</a>
        <a href="{{ route('hse.sap.hazard.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.sap.hazard.*')) }}">Hazard Report (SHE-FM-007)</a>
        <a href="{{ route('hse.dashboard.sap.achievement', 'inspection') }}" class="{{ $subItemClass(request()->is('*/sap/achievement/inspection')) }}">Inspection Achievement</a>
        <a href="{{ route('hse.dashboard.sap.achievement', 'pto') }}" class="{{ $subItemClass(request()->is('*/sap/achievement/pto')) }}">PTO Achievement</a>
        <a href="{{ route('hse.dashboard.sap.achievement', 'hazard') }}" class="{{ $subItemClass(request()->is('*/sap/achievement/hazard')) }}">Hazard Report Achievement</a>
        <a href="{{ route('hse.dashboard.sap.employee') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.sap.employee')) }}">Employee SAP Performance</a>
        <a href="{{ route('hse.dashboard.sap.department') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.sap.department')) }}">Department SAP Performance</a>
        <a href="{{ route('hse.dashboard.sap.targets.index') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.sap.targets.*')) }}">SAP Target Management</a>
      </div>
    </div>

    {{-- 5. RISK REGISTER (DROPDOWN) --}}
    <div x-data="{ openRiskReg: {{ $isRiskActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openRiskReg = !openRiskReg" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openRiskReg }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="warning"/>
          <span class="ui-nav-label">Risk Register</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openRiskReg ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openRiskReg" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.risk.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.risk.dashboard')) }}">Risk Dashboard</a>
        <a href="{{ route('hse.dashboard.risk.list') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.risk.list')) }}">Risk Register List</a>
        <a href="{{ route('hse.dashboard.risk.high_activities') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.risk.high_activities')) }}">High Risk Activities</a>
        <a href="{{ route('hse.dashboard.risk') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.risk')) }}">Risk Assessment Analytics</a>
        @if(Route::has('admin.hipo.index'))
          <a href="{{ route('admin.hipo.index') }}" class="{{ $subItemClass(request()->routeIs('admin.hipo.*')) }}">HIPO Monitoring</a>
        @endif
        @if(Route::has('ccm-reports.index'))
          <a href="{{ route('ccm-reports.index') }}" class="{{ $subItemClass(request()->routeIs('ccm-reports.*')) }}">Critical Control Management</a>
        @endif
        <a href="{{ route('hse.dashboard.risk.controls') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.risk.controls')) }}">Risk Control Monitoring</a>
      </div>
    </div>

    {{-- 6. PICA MANAGEMENT (DROPDOWN) --}}
    <div x-data="{ openPicaGroup: {{ $isPicaActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openPicaGroup = !openPicaGroup" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openPicaGroup }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="check-check"/>
          <span class="ui-nav-label">PICA Management</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openPicaGroup ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openPicaGroup" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.pica.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.pica.dashboard')) }}">PICA Dashboard</a>
        <a href="{{ route('hse.dashboard.pica.list') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.pica.list') && !request()->route('source')) }}">All PICA</a>
        <a href="{{ route('hse.dashboard.pica.list', 'audit') }}" class="{{ $subItemClass(request()->is('*/pica/list/audit')) }}">PICA Audit</a>
        <a href="{{ route('hse.dashboard.pica.list', 'investigation') }}" class="{{ $subItemClass(request()->is('*/pica/list/investigation')) }}">PICA Investigation</a>
        <a href="{{ route('hse.dashboard.pica.list', 'inspection') }}" class="{{ $subItemClass(request()->is('*/pica/list/inspection')) }}">PICA Inspection</a>
        <a href="{{ route('hse.dashboard.pica.list', 'pto') }}" class="{{ $subItemClass(request()->is('*/pica/list/pto')) }}">PICA PTO</a>
        <a href="{{ route('hse.dashboard.pica.list', 'hazard') }}" class="{{ $subItemClass(request()->is('*/pica/list/hazard')) }}">PICA Hazard Report</a>
        <a href="{{ route('hse.dashboard.pica.list', 'verification') }}" class="{{ $subItemClass(request()->is('*/pica/list/verification')) }}">PICA Verification</a>
        <a href="{{ route('hse.dashboard.pica.list', 'overdue') }}" class="{{ $subItemClass(request()->is('*/pica/list/overdue')) }}">
          <span class="text-rose-600 font-bold">Overdue PICA</span>
        </a>
      </div>
    </div>

    {{-- 7. DOCUMENT MANAGEMENT (DROPDOWN) --}}
    <div x-data="{ openDocGroup: {{ $isDocActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openDocGroup = !openDocGroup" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openDocGroup }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="file"/>
          <span class="ui-nav-label">Document Repository</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openDocGroup ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openDocGroup" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.documents.dashboard') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.documents.dashboard')) }}">Document Dashboard</a>
        <a href="{{ route('hse.dashboard.documents.index', 'policy') }}" class="{{ $subItemClass(request()->is('*/documents/list/policy')) }}">Policy (Kebijakan)</a>
        <a href="{{ route('hse.dashboard.documents.index', 'sop') }}" class="{{ $subItemClass(request()->is('*/documents/list/sop')) }}">SOP Prosedur</a>
        <a href="{{ route('hse.dashboard.documents.index', 'ik') }}" class="{{ $subItemClass(request()->is('*/documents/list/ik')) }}">Instruksi Kerja (IK)</a>
        <a href="{{ route('hse.dashboard.documents.index', 'form') }}" class="{{ $subItemClass(request()->is('*/documents/list/form')) }}">Form Documents</a>
        <a href="{{ route('hse.dashboard.documents.index', 'approval') }}" class="{{ $subItemClass(request()->is('*/documents/list/approval')) }}">Document Approval</a>
        <a href="{{ route('hse.dashboard.documents.index', 'expiring') }}" class="{{ $subItemClass(request()->is('*/documents/list/expiring')) }}">
          <span class="text-amber-600 font-bold">Expiring Review Due</span>
        </a>
      </div>
    </div>

    {{-- 8. SHE OPERATIONAL & ANALYTICS (DROPDOWN) --}}
    <div x-data="{ openHseOps: {{ $isOpsActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openHseOps = !openHseOps" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openHseOps }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="activity"/>
          <span class="ui-nav-label">Analytics & Reports</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openHseOps ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openHseOps" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        <a href="{{ route('hse.dashboard.overview') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.overview')) }}">SHE Overview</a>
        <a href="{{ route('hse.dashboard.incidents') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.incidents')) }}">Incident & Nearmiss</a>
        <a href="{{ route('hse.dashboard.inspections') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.inspections')) }}">Inspection Module</a>
        <a href="{{ route('hse.dashboard.training') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.training')) }}">Training Matrix</a>
        <a href="{{ route('hse.dashboard.environment') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.environment')) }}">Environment & Waste</a>
        <a href="{{ route('hse.dashboard.sites') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.sites')) }}">Site Performance</a>
        <a href="{{ route('hse.dashboard.reports') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.reports')) }}">
          <span class="text-[#1646A0] font-bold">SHE Report Center</span>
        </a>
        <a href="{{ route('hse.dashboard.notifications.index') }}" class="{{ $subItemClass(request()->routeIs('hse.dashboard.notifications.*')) }}">Notification Center</a>
      </div>
    </div>
    @endif
    @endif

    {{-- SECTION DIVIDER: MASTER DATA --}}
    <div class="px-3 pt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
      Master Data & Formulir
    </div>

    {{-- 9. DOKUMEN & MASTER DATA (DROPDOWN) --}}
    <div x-data="{ openMaster: {{ $isMasterActive ? 'true' : 'false' }} }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openMaster = !openMaster" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100" :class="{ 'bg-slate-50 border-slate-200': openMaster }">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="mandala"/>
          <span class="ui-nav-label">Master Data & Forms</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openMaster ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openMaster" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if(Route::has('admin.forms.index'))
        @if($user?->canUseFeatureRoute('admin.forms.index'))
          <a href="{{ route('admin.forms.index') }}" class="{{ $subItemClass(request()->routeIs('admin.forms.*')) }}">Dokumen Mandala</a>
        @endif
        @endif

        @if(Route::has('admin.departments.index'))
        @if($user?->canUseFeatureRoute('admin.departments.index'))
          <a href="{{ route('admin.departments.index') }}" class="{{ $subItemClass(request()->routeIs('admin.departments.*')) }}">Department</a>
        @endif
        @endif

        @if(Route::has('admin.sites.index'))
        @if($user?->canUseFeatureRoute('admin.sites.index'))
          <a href="{{ route('admin.sites.index') }}" class="{{ $subItemClass(request()->routeIs('admin.sites.*')) }}">Master Site</a>
        @endif
        @endif

        @if(Route::has('admin.companies.index'))
        @if($user?->canUseFeatureRoute('admin.companies.index'))
          <a href="{{ route('admin.companies.index') }}" class="{{ $subItemClass(request()->routeIs('admin.companies.*')) }}">Master Perusahaan</a>
        @endif
        @endif

        @if(Route::has('admin.groups.index'))
        @if($user?->canUseFeatureRoute('admin.groups.index'))
          <a href="{{ route('admin.groups.index') }}" class="{{ $subItemClass(request()->routeIs('admin.groups.*')) }}">Kelompok Indikator</a>
        @endif
        @endif

        @if(Route::has('admin.indicators.index'))
        @if($user?->canUseFeatureRoute('admin.indicators.index'))
          <a href="{{ route('admin.indicators.index') }}" class="{{ $subItemClass(request()->routeIs('admin.indicators.*')) }}">Indikator</a>
        @endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
          <a href="{{ route('user.forms.index') }}" class="{{ $subItemClass(request()->routeIs('user.forms.*')) }}">Formulir Publik</a>
        @endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
          <a href="{{ route('user.daily_notes.index') }}" class="{{ $subItemClass(request()->routeIs('user.daily_notes.*')) }}">Catatan Harian</a>
        @endif
        @endif
      </div>
    </div>

    {{-- 10. ADMINISTRASI SISTEM --}}
    @if($user?->role === 'super_admin')
    <div x-data="{ openAdmin: false }" class="rounded-xl border border-transparent transition">
      <button type="button" @click="openAdmin = !openAdmin" class="ui-nav-item w-full justify-between cursor-pointer font-semibold text-slate-800 hover:bg-slate-100">
        <div class="flex items-center gap-3 min-w-0">
          <x-ui-icon name="lock"/>
          <span class="ui-nav-label">Administrasi</span>
        </div>
        <svg class="h-4 w-4 text-slate-400 transition-transform duration-200" :class="openAdmin ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div x-show="sidebarCollapsed || openAdmin" x-collapse x-cloak class="mt-1 pl-7 pr-2 grid gap-1 border-l-2 border-slate-200 ml-5 py-1">
        @if($isSuper && Route::has('admin.users.active.index'))
        @if($user?->canUseFeatureRoute('admin.users.active.index'))
          <a href="{{ route('admin.users.active.index') }}" class="{{ $subItemClass(request()->routeIs('admin.users.active.*') || request()->routeIs('admin.site_access.*')) }}">Kelola Pengguna & Akses</a>
        @endif
        @endif
      </div>
    </div>
    @endif

    {{-- ================= NON ADMIN ================= --}}
    @else
    <div x-data="{ openMenu: true }">
      <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-2">Menu Utama</div>
      <div class="grid gap-1">
        @if($user?->canUseFeatureRoute('dashboard'))
          <a href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
          <a href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/><span class="ui-nav-label">HIPO & Nearmiss</span></a>
        @endif
        @endif


        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
          <a href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Formulir Publik</span></a>
        @endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
          <a href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
        @endif
        @endif
      </div>
    </div>
    @endif
  </div>
</nav>
</aside>

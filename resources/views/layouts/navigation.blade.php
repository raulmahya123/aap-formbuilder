@php
    $user = auth()->user();
    $isSuper = $user?->isSuperAdmin() ?? false;
    $navItemClass = function (bool $isActive) {
        return $isActive ? 'ui-nav-item is-active' : 'ui-nav-item';
    };
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
  <div class="mt-4 grid gap-4">
    {{-- ================= ADMIN ================= --}}
    @if($isSuper || ($user && $user->isAdmin()))

    <div>
      <div class="grid gap-1">
        @if(Route::has('dashboard'))
        @if($user?->canUseFeatureRoute('dashboard'))
<a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
@endif
        @endif
      </div>
    </div>

    <div>
      <div class="ui-nav-section px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]">K3LH / HSE</div>
      <div class="mt-2 grid gap-1">
        @if(Route::has('admin.daily.index'))
        @if($user?->canUseFeatureRoute('admin.daily.index'))
<a title="Input Data Operasional" aria-label="Input Data Operasional" href="{{ route('admin.daily.index') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.*')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Input Data Operasional</span></a>
@endif
        @endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="HIPO & Nearmiss" aria-label="HIPO & Nearmiss" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/><span class="ui-nav-label">HIPO & Nearmiss</span></a>
@endif
        @endif

        @if(Route::has('admin.wellness.index'))
        @if($user?->canUseFeatureRoute('admin.wellness.index'))
<a title="Health Assesment Checklist" aria-label="Health Assesment Checklist" href="{{ route('admin.wellness.index') }}" class="{{ $navItemClass(request()->routeIs('admin.wellness.*')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Health Assesment</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.index'))
        @if($user?->canUseFeatureRoute('admin.reports.index'))
<a title="Dashboard KPI & Statistik" aria-label="Dashboard KPI & Statistik" href="{{ route('admin.reports.index') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.index')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Dashboard KPI & Statistik</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.annual'))
        @if($user?->canUseFeatureRoute('admin.reports.annual'))
<a title="Laporan Tahunan" aria-label="Laporan Tahunan" href="{{ route('admin.reports.annual') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.annual')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Tahunan</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.quarterly'))
        @if($user?->canUseFeatureRoute('admin.reports.quarterly'))
<a title="Laporan Triwulan" aria-label="Laporan Triwulan" href="{{ route('admin.reports.quarterly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.quarterly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Triwulan</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.aggregate'))
        @if($user?->canUseFeatureRoute('admin.reports.aggregate'))
<a title="Laporan Agregat" aria-label="Laporan Agregat" href="{{ route('admin.reports.aggregate') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.aggregate')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan Agregat</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif

        @if(Route::has('admin.daily.summary'))
        @if($user?->canUseFeatureRoute('admin.daily.summary'))
<a title="Matriks Input" aria-label="Matriks Input" href="{{ route('admin.daily.summary') }}" class="{{ $navItemClass(request()->routeIs('admin.daily.summary')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Matriks Input</span></a>
@endif
        @endif

        @if(Route::has('admin.ccm.index'))
        @if($user?->canUseFeatureRoute('admin.ccm.index'))
<a title="Laporan CCM" aria-label="Laporan CCM" href="{{ route('admin.ccm.index') }}" class="{{ $navItemClass(request()->routeIs('admin.ccm.*')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Laporan CCM</span></a>
@endif
        @endif
      </div>
    </div>

    @if(Route::has('hse.dashboard.overview'))
    @if($user?->canUseFeatureRoute('hse.dashboard.overview'))
    <div>
      <div x-data="{openShePerf:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openShePerf" @click="openShePerf=!openShePerf">
          SHE Performance
          <span aria-hidden="true" x-text="openShePerf ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openShePerf" x-transition>
          <a title="Performance Dashboard" aria-label="Performance Dashboard" href="{{ route('hse.dashboard.safety') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.safety')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Performance Dashboard</span></a>
          <a title="Fatality Monitoring" aria-label="Fatality Monitoring" href="{{ route('hse.dashboard.performance.indicator', 'fatality') }}" class="{{ $navItemClass(request()->is('*/performance/fatality')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Fatality Monitoring</span></a>
          <a title="LTI Monitoring" aria-label="LTI Monitoring" href="{{ route('hse.dashboard.performance.indicator', 'lti') }}" class="{{ $navItemClass(request()->is('*/performance/lti')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">LTI Monitoring</span></a>
          <a title="Injury Non-LTI" aria-label="Injury Non-LTI" href="{{ route('hse.dashboard.performance.indicator', 'non-lti') }}" class="{{ $navItemClass(request()->is('*/performance/non-lti')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Injury Non-LTI</span></a>
          <a title="Property Damage" aria-label="Property Damage" href="{{ route('hse.dashboard.performance.indicator', 'property-damage') }}" class="{{ $navItemClass(request()->is('*/performance/property-damage')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Property Damage</span></a>
          <a title="PDFR Monitoring" aria-label="PDFR Monitoring" href="{{ route('hse.dashboard.performance.indicator', 'pdfr') }}" class="{{ $navItemClass(request()->is('*/performance/pdfr')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">PDFR Monitoring</span></a>
          <a title="Property Damage Cost" aria-label="Property Damage Cost" href="{{ route('hse.dashboard.performance.indicator', 'pd-cost') }}" class="{{ $navItemClass(request()->is('*/performance/pd-cost')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Property Damage Cost</span></a>
          <a title="Environmental Accident" aria-label="Environmental Accident" href="{{ route('hse.dashboard.performance.indicator', 'environmental') }}" class="{{ $navItemClass(request()->is('*/performance/environmental')) }}"><x-ui-icon name="location"/><span class="ui-nav-label">Environmental Accident</span></a>
          <a title="PAK Monitoring" aria-label="PAK Monitoring" href="{{ route('hse.dashboard.performance.indicator', 'pak') }}" class="{{ $navItemClass(request()->is('*/performance/pak')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">PAK Monitoring</span></a>
          <a title="KAPTK Monitoring" aria-label="KAPTK Monitoring" href="{{ route('hse.dashboard.performance.indicator', 'kaptk') }}" class="{{ $navItemClass(request()->is('*/performance/kaptk')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">KAPTK Monitoring</span></a>
          <a title="Near Miss" aria-label="Near Miss" href="{{ route('hse.dashboard.performance.indicator', 'near-miss') }}" class="{{ $navItemClass(request()->is('*/performance/near-miss')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Near Miss</span></a>
          <a title="Manhours Management" aria-label="Manhours Management" href="{{ route('hse.dashboard.manhours.index') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.manhours.*')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Manhours Management</span></a>
          <a title="KPI Target Management" aria-label="KPI Target Management" href="{{ route('hse.dashboard.targets.index') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.targets.*')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">KPI Target Management</span></a>
        </div>
      </div>
    </div>

    <div>
      <div x-data="{openSapGroup:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openSapGroup" @click="openSapGroup=!openSapGroup">
          Safety Accountability Program (SAP)
          <span aria-hidden="true" x-text="openSapGroup ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openSapGroup" x-transition>
          <a title="SAP Dashboard" aria-label="SAP Dashboard" href="{{ route('hse.dashboard.sap.index') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.sap.index')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">SAP Dashboard</span></a>
          <a title="Inspection Achievement" aria-label="Inspection Achievement" href="{{ route('hse.dashboard.sap.achievement', 'inspection') }}" class="{{ $navItemClass(request()->is('*/sap/achievement/inspection')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Inspection Achievement</span></a>
          <a title="PTO Achievement" aria-label="PTO Achievement" href="{{ route('hse.dashboard.sap.achievement', 'pto') }}" class="{{ $navItemClass(request()->is('*/sap/achievement/pto')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">PTO Achievement</span></a>
          <a title="Hazard Report Achievement" aria-label="Hazard Report Achievement" href="{{ route('hse.dashboard.sap.achievement', 'hazard') }}" class="{{ $navItemClass(request()->is('*/sap/achievement/hazard')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Hazard Report Achievement</span></a>
          <a title="Employee SAP Performance" aria-label="Employee SAP Performance" href="{{ route('hse.dashboard.sap.employee') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.sap.employee')) }}"><x-ui-icon name="users"/><span class="ui-nav-label">Employee SAP Performance</span></a>
          <a title="Department SAP Performance" aria-label="Department SAP Performance" href="{{ route('hse.dashboard.sap.department') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.sap.department')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">Department SAP Performance</span></a>
          <a title="SAP Target Management" aria-label="SAP Target Management" href="{{ route('hse.dashboard.sap.targets.index') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.sap.targets.*')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">SAP Target Management</span></a>
        </div>
      </div>
    </div>

    <div>
      <div x-data="{openRiskReg:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openRiskReg" @click="openRiskReg=!openRiskReg">
          Risk Register
          <span aria-hidden="true" x-text="openRiskReg ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openRiskReg" x-transition>
          <a title="Risk Dashboard" aria-label="Risk Dashboard" href="{{ route('hse.dashboard.risk.dashboard') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk.dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Risk Dashboard</span></a>
          <a title="Risk Register List" aria-label="Risk Register List" href="{{ route('hse.dashboard.risk.list') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk.list')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Risk Register List</span></a>
          <a title="High Risk Activities" aria-label="High Risk Activities" href="{{ route('hse.dashboard.risk.high_activities') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk.high_activities')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">High Risk Activities</span></a>
          <a title="Risk Assessment" aria-label="Risk Assessment" href="{{ route('hse.dashboard.risk') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Risk Assessment</span></a>
          @if(Route::has('admin.hipo.index'))
          <a title="HIPO Monitoring" aria-label="HIPO Monitoring" href="{{ route('admin.hipo.index') }}" class="{{ $navItemClass(request()->routeIs('admin.hipo.*')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">HIPO Monitoring</span></a>
          @endif
          @if(Route::has('ccm-reports.index'))
          <a title="Critical Control Management" aria-label="Critical Control Management" href="{{ route('ccm-reports.index') }}" class="{{ $navItemClass(request()->routeIs('ccm-reports.*')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">Critical Control Management</span></a>
          @endif
          <a title="Risk Control Monitoring" aria-label="Risk Control Monitoring" href="{{ route('hse.dashboard.risk.controls') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk.controls')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Risk Control Monitoring</span></a>
        </div>
      </div>
    </div>

    <div>
      <div x-data="{openPicaGroup:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openPicaGroup" @click="openPicaGroup=!openPicaGroup">
          PICA Management
          <span aria-hidden="true" x-text="openPicaGroup ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openPicaGroup" x-transition>
          <a title="PICA Dashboard" aria-label="PICA Dashboard" href="{{ route('hse.dashboard.pica.dashboard') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.pica.dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">PICA Dashboard</span></a>
          <a title="All PICA" aria-label="All PICA" href="{{ route('hse.dashboard.pica.list') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.pica.list') && !request()->route('source')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">All PICA</span></a>
          <a title="PICA Audit" aria-label="PICA Audit" href="{{ route('hse.dashboard.pica.list', 'audit') }}" class="{{ $navItemClass(request()->is('*/pica/list/audit')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">PICA Audit</span></a>
          <a title="PICA Investigation" aria-label="PICA Investigation" href="{{ route('hse.dashboard.pica.list', 'investigation') }}" class="{{ $navItemClass(request()->is('*/pica/list/investigation')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">PICA Investigation</span></a>
          <a title="PICA Inspection" aria-label="PICA Inspection" href="{{ route('hse.dashboard.pica.list', 'inspection') }}" class="{{ $navItemClass(request()->is('*/pica/list/inspection')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">PICA Inspection</span></a>
          <a title="PICA PTO" aria-label="PICA PTO" href="{{ route('hse.dashboard.pica.list', 'pto') }}" class="{{ $navItemClass(request()->is('*/pica/list/pto')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">PICA PTO</span></a>
          <a title="PICA Hazard Report" aria-label="PICA Hazard Report" href="{{ route('hse.dashboard.pica.list', 'hazard') }}" class="{{ $navItemClass(request()->is('*/pica/list/hazard')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">PICA Hazard Report</span></a>
          <a title="PICA Verification" aria-label="PICA Verification" href="{{ route('hse.dashboard.pica.list', 'verification') }}" class="{{ $navItemClass(request()->is('*/pica/list/verification')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">PICA Verification</span></a>
          <a title="Overdue PICA" aria-label="Overdue PICA" href="{{ route('hse.dashboard.pica.list', 'overdue') }}" class="{{ $navItemClass(request()->is('*/pica/list/overdue')) }}"><x-ui-icon name="report"/><span class="ui-nav-label text-rose-600 font-semibold">Overdue PICA</span></a>
        </div>
      </div>
    </div>

    <div>
      <div x-data="{openDocGroup:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openDocGroup" @click="openDocGroup=!openDocGroup">
          Document Management
          <span aria-hidden="true" x-text="openDocGroup ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openDocGroup" x-transition>
          <a title="Document Dashboard" aria-label="Document Dashboard" href="{{ route('hse.dashboard.documents.dashboard') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.documents.dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Document Dashboard</span></a>
          <a title="Policy Repository" aria-label="Policy Repository" href="{{ route('hse.dashboard.documents.index', 'policy') }}" class="{{ $navItemClass(request()->is('*/documents/list/policy')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Policy</span></a>
          <a title="Standard Operating Procedures" aria-label="Standard Operating Procedures" href="{{ route('hse.dashboard.documents.index', 'sop') }}" class="{{ $navItemClass(request()->is('*/documents/list/sop')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">SOP</span></a>
          <a title="Instruksi Kerja" aria-label="Instruksi Kerja" href="{{ route('hse.dashboard.documents.index', 'ik') }}" class="{{ $navItemClass(request()->is('*/documents/list/ik')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Instruksi Kerja (IK)</span></a>
          <a title="Form Documents" aria-label="Form Documents" href="{{ route('hse.dashboard.documents.index', 'form') }}" class="{{ $navItemClass(request()->is('*/documents/list/form')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Form Documents</span></a>
          <a title="Document Approval" aria-label="Document Approval" href="{{ route('hse.dashboard.documents.index', 'approval') }}" class="{{ $navItemClass(request()->is('*/documents/list/approval')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Document Approval</span></a>
          <a title="Expiring Documents" aria-label="Expiring Documents" href="{{ route('hse.dashboard.documents.index', 'expiring') }}" class="{{ $navItemClass(request()->is('*/documents/list/expiring')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label text-amber-600 font-semibold">Expiring Documents</span></a>
        </div>
      </div>
    </div>

    <div>
      <div x-data="{openHseOps:true}">
        <button type="button" class="ui-nav-section flex w-full cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openHseOps" @click="openHseOps=!openHseOps">
          SHE Operational & Analytics
          <span aria-hidden="true" x-text="openHseOps ? '−' : '+'"></span>
        </button>
        <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openHseOps" x-transition>
          <a title="Overview" aria-label="Overview" href="{{ route('hse.dashboard.overview') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.overview')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Overview</span></a>
          <a title="Incident & Nearmiss" aria-label="Incident & Nearmiss" href="{{ route('hse.dashboard.incidents') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.incidents')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Incident & Nearmiss</span></a>
          <a title="Inspection" aria-label="Inspection" href="{{ route('hse.dashboard.inspections') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.inspections')) }}"><x-ui-icon name="input"/><span class="ui-nav-label">Inspection</span></a>
          <a title="Risk & Compliance" aria-label="Risk & Compliance" href="{{ route('hse.dashboard.risk') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.risk')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Risk & Compliance</span></a>
          <a title="PICA Management" aria-label="PICA Management" href="{{ route('hse.dashboard.actions') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.actions')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">PICA Management</span></a>
          <a title="Site Performance" aria-label="Site Performance" href="{{ route('hse.dashboard.sites') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.sites')) }}"><x-ui-icon name="location"/><span class="ui-nav-label">Site Performance</span></a>
          <a title="Reports" aria-label="Reports" href="{{ route('hse.dashboard.reports') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.reports')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Reports</span></a>
          <a title="Notifications" aria-label="Notifications" href="{{ route('hse.dashboard.notifications.index') }}" class="{{ $navItemClass(request()->routeIs('hse.dashboard.notifications.*')) }}"><x-ui-icon name="chat"/><span class="ui-nav-label">Notifications</span></a>
        </div>
      </div>
    </div>
    @endif
    @endif

    <div>
      <div class="ui-nav-section px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]">Dokumen & Master Data</div>
      <div class="mt-2 grid gap-1">
        @if(Route::has('admin.documents.index'))
        @if($user?->canUseFeatureRoute('admin.documents.index'))
<a title="Dokumen Mandala" aria-label="Dokumen Mandala" href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Dokumen Mandala</span></a>
@endif
        @endif

        @if(Route::has('admin.departments.index'))
        @if($user?->canUseFeatureRoute('admin.departments.index'))
<a title="Department" aria-label="Department" href="{{ route('admin.departments.index') }}" class="{{ $navItemClass(request()->routeIs('admin.departments.*')) }}"><x-ui-icon name="folder"/><span class="ui-nav-label">Department</span></a>
@endif
        @endif

        @if(Route::has('admin.sites.index'))
        @if($user?->canUseFeatureRoute('admin.sites.index'))
<a title="Master Site" aria-label="Master Site" href="{{ route('admin.sites.index') }}" class="{{ $navItemClass(request()->routeIs('admin.sites.*')) }}"><x-ui-icon name="location"/><span class="ui-nav-label">Master Site</span></a>
@endif
        @endif

        @if(Route::has('admin.companies.index'))
        @if($user?->canUseFeatureRoute('admin.companies.index'))
<a title="Master Perusahaan" aria-label="Master Perusahaan" href="{{ route('admin.companies.index') }}" class="{{ $navItemClass(request()->routeIs('admin.companies.*')) }}"><x-ui-icon name="location"/><span class="ui-nav-label">Master Perusahaan</span></a>
@endif
        @endif

        @if(Route::has('admin.groups.index'))
        @if($user?->canUseFeatureRoute('admin.groups.index'))
<a title="Kelompok Indikator" aria-label="Kelompok Indikator" href="{{ route('admin.groups.index') }}" class="{{ $navItemClass(request()->routeIs('admin.groups.*')) }}"><x-ui-icon name="groups"/><span class="ui-nav-label">Kelompok Indikator</span></a>
@endif
        @endif

        @if(Route::has('admin.indicators.index'))
        @if($user?->canUseFeatureRoute('admin.indicators.index'))
<a title="Indikator" aria-label="Indikator" href="{{ route('admin.indicators.index') }}" class="{{ $navItemClass(request()->routeIs('admin.indicators.*')) }}"><x-ui-icon name="chart"/><span class="ui-nav-label">Indikator</span></a>
@endif
        @endif

        @if(Route::has('admin.forms.index'))
        @if($user?->canUseFeatureRoute('admin.forms.index'))
<a title="Form Builder" aria-label="Form Builder" href="{{ route('admin.forms.index') }}" class="{{ $navItemClass(request()->routeIs('admin.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Form Builder</span></a>
@endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
<a title="Formulir Publik" aria-label="Formulir Publik" href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Formulir Publik</span></a>
@endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
<a title="Catatan Harian" aria-label="Catatan Harian" href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif
      </div>
    </div>

    @if($user?->role === 'super_admin')
    <div x-data="{openAdministration:true}">
      <button type="button" class="ui-nav-section flex cursor-pointer justify-between px-3 text-[11px] font-semibold normal-case tracking-normal text-[#52525B]" :aria-expanded="openAdministration" @click="openAdministration=!openAdministration">
        Administrasi
        <span aria-hidden="true" x-text="openAdministration ? '−' : '+'"></span>
      </button>
      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openAdministration" x-transition>
        @if($isSuper && Route::has('admin.users.active.index'))
        @if($user?->canUseFeatureRoute('admin.users.active.index'))
<a title="Kelola Pengguna & Akses" aria-label="Kelola Pengguna & Akses" href="{{ route('admin.users.active.index') }}" class="{{ $navItemClass(request()->routeIs('admin.users.active.*') || request()->routeIs('admin.site_access.*')) }}"><x-ui-icon name="users"/><span class="ui-nav-label">Kelola Pengguna & Akses</span></a>
@endif
        @endif
      </div>
    </div>
    @endif

    {{-- ================= NON ADMIN ================= --}}
    @else

    <div x-data="{openMenu:true}">
      <button type="button" class="ui-nav-section px-3 text-xs normal-case tracking-normalr text-coal-500 flex justify-between cursor-pointer" :aria-expanded="openMenu" @click="openMenu=!openMenu">
        Menu
        <span aria-hidden="true" x-text="openMenu ? '−' : '+'"></span>
      </button>

      <div class="mt-2 grid gap-1" x-show="sidebarCollapsed || openMenu" x-transition>
        @if($user?->canUseFeatureRoute('dashboard'))
<a title="Dashboard" aria-label="Dashboard" href="{{ route('dashboard') }}" class="{{ $navItemClass(request()->routeIs('dashboard')) }}"><x-ui-icon name="dashboard"/><span class="ui-nav-label">Dashboard</span></a>
@endif

        @if(Route::has('admin.qa.index'))
        @if($user?->canUseFeatureRoute('admin.qa.index'))
<a title="HIPO & Nearmiss" aria-label="HIPO & Nearmiss" href="{{ route('admin.qa.index') }}" class="{{ $navItemClass(request()->routeIs('admin.qa.*')) }}"><x-ui-icon name="chat"/><span class="ui-nav-label">HIPO & Nearmiss</span></a>
@endif
        @endif

        @if(Route::has('admin.documents.index'))
        @if($user?->canUseFeatureRoute('admin.documents.index'))
<a title="Dokumen Mandala" aria-label="Dokumen Mandala" href="{{ route('admin.documents.index') }}" class="{{ $navItemClass(request()->routeIs('admin.documents.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Dokumen Mandala</span></a>
@endif
        @endif

        @if(Route::has('user.forms.index'))
        @if($user?->canUseFeatureRoute('user.forms.index'))
<a title="Formulir Publik" aria-label="Formulir Publik" href="{{ route('user.forms.index') }}" class="{{ $navItemClass(request()->routeIs('user.forms.*')) }}"><x-ui-icon name="mandala"/><span class="ui-nav-label">Formulir Publik</span></a>
@endif
        @endif

        @if(Route::has('user.daily_notes.index'))
        @if($user?->canUseFeatureRoute('user.daily_notes.index'))
<a title="Catatan Harian" aria-label="Catatan Harian" href="{{ route('user.daily_notes.index') }}" class="{{ $navItemClass(request()->routeIs('user.daily_notes.*')) }}"><x-ui-icon name="note"/><span class="ui-nav-label">Catatan Harian</span></a>
@endif
        @endif

        @if(Route::has('admin.reports.monthly'))
        @if($user?->canUseFeatureRoute('admin.reports.monthly'))
<a title="Rekap Bulanan" aria-label="Rekap Bulanan" href="{{ route('admin.reports.monthly') }}" class="{{ $navItemClass(request()->routeIs('admin.reports.monthly')) }}"><x-ui-icon name="report"/><span class="ui-nav-label">Rekap Bulanan</span></a>
@endif
        @endif
      </div>
    </div>
    @endif
  </div>
</nav>
</aside>

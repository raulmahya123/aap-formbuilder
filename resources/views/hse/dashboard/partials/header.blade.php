@props(['title', 'subtitle', 'filters', 'options', 'export' => null, 'create' => null, 'panel' => null, 'showDashboardFilters' => true])
<x-ui.page-header>
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">{{ $title }}</h1>
            <p class="ui-page-description">{{ $subtitle }}</p>
        </div>
        <div class="ui-page-actions">
            @if($create)
                <x-ui.link variant="primary" icon="plus" :href="route($create['route'])">{{ $create['label'] }}</x-ui.link>
            @endif
            @if($export)
                <a class="ui-button" href="{{ route('hse.dashboard.reports.export', ['report' => $export] + request()->query()) }}">
                    <x-ui.lucide name="download" /> Export CSV
                </a>
            @endif
            <a class="ui-button" href="{{ route('hse.dashboard.reports', request()->query()) }}">
                <x-ui.lucide name="file-text" /> Reports
            </a>
        </div>
    @if($showDashboardFilters)
        <x-slot:filters>
            <div class="ui-filter-summary">
                <span class="ui-filter-summary__title"><x-ui.lucide name="filter" /> Filter dashboard</span>
                <span>{{ $filters['label'] }} · {{ $filters['from']->format('d M Y') }} – {{ $filters['to']->format('d M Y') }}</span>
            </div>
            <x-hse.filter-bar :filters="$filters" :options="$options" :panel="$panel" />
        </x-slot:filters>
    @endif
</x-ui.page-header>

@props(['title', 'subtitle', 'filters', 'options', 'export' => null])
<header class="ui-page-header">
    <div class="ui-page-header__row">
        <div class="ui-page-header__copy">
            <h1 class="ui-page-title">{{ $title }}</h1>
            <p class="ui-page-description">{{ $subtitle }}</p>
        </div>
        <div class="ui-page-actions">
            <span class="ui-status ui-status--info">{{ $filters['label'] }}</span>
            <span class="text-xs text-[#64748B]">{{ $filters['from']->format('d M Y') }} – {{ $filters['to']->format('d M Y') }}</span>
            @if($export)
                <a class="ui-button" href="{{ route('hse.dashboard.reports.export', ['report' => $export] + request()->query()) }}">
                    <x-ui.lucide name="download" /> Export CSV
                </a>
            @endif
            <a class="ui-button" href="{{ route('hse.dashboard.reports', request()->query()) }}">
                <x-ui.lucide name="file-text" /> Reports
            </a>
        </div>
    </div>
    <div class="ui-page-header__filters">
        <x-hse.filter-bar :filters="$filters" :options="$options" />
    </div>
</header>

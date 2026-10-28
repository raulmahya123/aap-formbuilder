<header {{ $attributes->class(['ui-page-header']) }}>
    @isset($breadcrumbs)
        <x-ui.breadcrumbs>{{ $breadcrumbs }}</x-ui.breadcrumbs>
    @else
        @hasSection('breadcrumbs')
            <x-ui.breadcrumbs>@yield('breadcrumbs')</x-ui.breadcrumbs>
        @endif
    @endisset
    <div class="ui-page-header__row">{{ $slot }}</div>
</header>
@isset($filters)
    <x-ui.filter-panel>{{ $filters }}</x-ui.filter-panel>
@endisset

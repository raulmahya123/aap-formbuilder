@props(['items', 'depth' => 0])
@foreach($items as $item)
    @if(isset($item['children']))
        <div class="ui-navigation-group" x-data="{ expanded: @js($item['active']) }">
            <button type="button" @click="if (sidebarCollapsed) { toggleSidebarCollapse(); expanded = true; } else { expanded = !expanded; }" :aria-expanded="expanded" title="{{ $item['label'] }}" aria-label="{{ $item['label'] }}" class="{{ $depth === 0 ? 'ui-nav-item' : 'ui-navigation-subgroup' }}">
                @if(isset($item['icon']))<x-ui.icon :name="$item['icon']" />@endif
                <span class="ui-nav-label">{{ $item['label'] }}</span>
                <x-ui.lucide name="chevron-right" class="ui-navigation-chevron" x-bind:class="expanded ? 'rotate-90' : ''" />
            </button>
            <div x-show="expanded && !sidebarCollapsed" x-cloak class="ui-navigation-children">
                <x-ui.navigation-tree :items="$item['children']" :depth="$depth + 1" />
            </div>
        </div>
    @else
        <a href="{{ route($item['route'], $item['params']) }}" @if($item['active']) aria-current="page" @endif class="ui-navigation-link {{ $item['active'] ? 'is-active' : '' }}">{{ $item['label'] }}</a>
    @endif
@endforeach

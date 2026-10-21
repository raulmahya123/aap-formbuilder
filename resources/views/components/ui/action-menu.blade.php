@if(preg_match('/<(?:a|button)\b/', (string) $slot))
    @php($menuId = 'row-actions-'.\Illuminate\Support\Str::uuid())
    <x-ui.icon-button action="more" :popovertarget="$menuId" aria-haspopup="menu" aria-expanded="false" />
    <div id="{{ $menuId }}" class="ui-action-menu" popover role="menu" aria-label="Aksi lainnya">{{ $slot }}</div>
@endif

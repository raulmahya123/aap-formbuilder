@props(['action' => null])
<form method="GET" action="{{ $action ?? url()->current() }}" {{ $attributes->class(['ui-table-toolbar'])->merge(['x-data' => '']) }} data-listing-filters>
    @isset($actions)
        <div class="ui-filter-fields">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endisset
    @isset($actions)
        <div class="ui-filter-actions">{{ $actions }}</div>
    @endisset
    @foreach(request()->only(['site_id','company_id','department_id','doc_type','sort','dir']) as $key => $value)
        @if(is_scalar($value) && !preg_match('/name=[\"\x27]'.preg_quote($key, '/').'[\"\x27]/', (string) $slot))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach
    @if(request('per_page') && !preg_match('/name=[\"\x27](per_page|perPage|per)[\"\x27]/', (string) $slot))<input type="hidden" name="per_page" value="{{ \App\Support\Listing::perPage() }}">@endif
</form>

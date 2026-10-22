@props(['colspan' => 1, 'title' => null, 'message' => null, 'icon' => 'search', 'action' => null, 'actionLabel' => null, 'actionHref' => null, 'canCreate' => false, 'kind' => null])
@php
    $stateKind = $kind ?? (request()->filled('q') || request()->filled('search') ? 'search' : (session('active_site_id') || collect(request()->except(['page','per_page','sort','direction','q','search']))->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty() ? 'filter' : 'empty'));
@endphp
<tr class="ui-table-empty-row">
  <td colspan="{{ $colspan }}">
    <x-ui.empty-state :kind="$stateKind" :title="$title" :message="$message" :action-href="$actionHref" :action-label="$actionLabel ?? 'Tambah data'" :can-create="$canCreate">{{ $slot }}</x-ui.empty-state>
  </td>
</tr>

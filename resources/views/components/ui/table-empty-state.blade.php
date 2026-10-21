@props(['colspan' => 1, 'title' => 'Belum ada data', 'message' => null, 'icon' => 'search', 'action' => null, 'actionLabel' => null, 'actionHref' => null])
<tr>
  <td colspan="{{ $colspan }}">
    <div class="ui-table-empty-state">
      <div class="ui-table-empty-state__icon"><x-ui.lucide :name="$icon" /></div>
      <div class="ui-table-empty-state__title">{{ $title }}</div>
      @if($message)
        <div class="ui-table-empty-state__message">{{ $message }}</div>
      @endif
      @if($actionHref)
        <x-ui.link variant="primary" href="{{ $actionHref }}" class="mt-2">{{ $actionLabel ?? 'Tambah data' }}</x-ui.link>
      @endif
      {{ $slot }}
    </div>
  </td>
</tr>

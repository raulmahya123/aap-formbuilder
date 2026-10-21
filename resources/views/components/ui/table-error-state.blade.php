@props(['colspan' => 1, 'title' => 'Terjadi kesalahan', 'message' => 'Gagal memuat data. Silakan coba lagi.'])
<tr>
  <td colspan="{{ $colspan }}">
    <div class="ui-table-error-state">
      <div class="ui-table-error-state__icon"><x-ui.lucide name="alert-triangle" /></div>
      <div class="ui-table-error-state__title">{{ $title }}</div>
      <div class="ui-table-error-state__message">{{ $message }}</div>
      {{ $slot }}
    </div>
  </td>
</tr>

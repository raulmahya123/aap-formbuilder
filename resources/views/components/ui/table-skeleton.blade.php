@props(['cols' => 5, 'rows' => 5])
@for($r = 0; $r < $rows; $r++)
<tr class="ui-table-skeleton" aria-busy="true" aria-label="Memuat baris data">
  @for($c = 0; $c < $cols; $c++)
    <td>
      <div class="ui-skeleton-bar" style="width: {{ $c === 0 ? 60 : 80 }}%" aria-hidden="true"></div>
    </td>
  @endfor
</tr>
@endfor

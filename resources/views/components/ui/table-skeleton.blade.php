@props(['cols' => 5, 'rows' => 5])
@for($r = 0; $r < $rows; $r++)
<tr class="ui-table-skeleton">
  @for($c = 0; $c < $cols; $c++)
    <td>
      <div class="ui-skeleton-bar" style="width: {{ rand(40, 90) }}%"></div>
    </td>
  @endfor
</tr>
@endfor

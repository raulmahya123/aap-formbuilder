@props(['colspan' => 1, 'kind' => 'api', 'retryUrl' => null])
<tr>
  <td colspan="{{ $colspan }}">
    <x-ui.error-state :kind="$kind" :retry-url="$retryUrl ?? url()->full()">{{ $slot }}</x-ui.error-state>
  </td>
</tr>

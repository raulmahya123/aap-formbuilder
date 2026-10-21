@props(['action' => null])
<div {{ $attributes->class(['ui-data-table']) }}>
  @if(isset($toolbar))
    <form method="GET" action="{{ $action ?? url()->current() }}" x-data x-ref="dtForm">
      <div class="ui-table-toolbar">
        <div class="ui-table-toolbar__left">{{ $toolbar }}</div>
        @if(isset($toolbarRight))
          <div class="ui-table-toolbar__right">{{ $toolbarRight }}</div>
        @endif
      </div>
    </form>
  @endif

  {{ $slot }}

  @if(isset($pagination))
    {{ $pagination }}
  @endif
</div>

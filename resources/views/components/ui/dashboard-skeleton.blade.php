<x-ui.page-skeleton {{ $attributes }}><div class="ui-skeleton-dashboard">@for($i = 0; $i < 4; $i++)<x-ui.card-skeleton />@endfor</div><x-ui.detail-skeleton :rows="6" /></x-ui.page-skeleton>

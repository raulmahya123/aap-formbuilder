@props(['paginator', 'perPageOptions' => [10, 25, 50, 100], 'perPageParam' => 'per_page'])
@php
  $currentPage = $paginator->currentPage();
  $lastPage = $paginator->lastPage();
  $perPage = $paginator->perPage();
  $first = $paginator->firstItem() ?? 0;
  $last = $paginator->lastItem() ?? 0;
  $total = $paginator->total();
  $pageName = $paginator->getPageName();
  $perPageOptions = collect($perPageOptions)->push($perPage)->unique()->sort()->values();

  // Build page window: always show first, last, current±1, with ellipses
  $pages = collect();
  $pages->push(1);
  for ($p = max(2, $currentPage - 1); $p <= min($lastPage - 1, $currentPage + 1); $p++) {
    $pages->push($p);
  }
  if ($lastPage > 1) $pages->push($lastPage);
  $pages = $pages->unique()->sort()->values();

  // Helper to build URL preserving query params
  $buildUrl = function ($page, $pp = null) use ($paginator, $perPageParam, $perPage, $pageName) {
    $url = $paginator->url($page);
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $params);
    $params = array_replace(request()->except([$pageName, $perPageParam, 'per', 'perPage']), $params);
    unset($params['per'], $params['perPage']);
    $params[$pageName] = $page;
    $params[$perPageParam] = $pp ?? $perPage;
    return strtok($url, '?#') . '?' . http_build_query($params) . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
  };
@endphp
<div {{ $attributes->class(['ui-table-pagination']) }}>
  <div class="ui-table-pagination__info">
    @if($total === 0)
      Tidak ada data
    @else
      Menampilkan <strong>{{ $first }}–{{ $last }}</strong> dari <strong>{{ number_format($total) }}</strong> data
    @endif
  </div>
  <div class="ui-table-pagination__controls">
    <div class="ui-table-pagination__per-page">
      <label>Baris per halaman:
      <select aria-label="Baris per halaman" onchange="window.location.href=this.value">
        @foreach($perPageOptions as $opt)
          <option value="{{ $buildUrl(1, $opt) }}" @selected($perPage == $opt)>{{ $opt }}</option>
        @endforeach
      </select>
      </label>
    </div>
    <nav class="ui-table-pagination__pages" aria-label="Pagination">
      {{-- Previous --}}
      <a
        href="{{ $paginator->previousPageUrl() ? $buildUrl($currentPage - 1) : '#' }}"
        class="ui-table-pagination__btn"
        @if(!$paginator->previousPageUrl()) aria-disabled="true" tabindex="-1" @endif
        aria-label="Halaman sebelumnya"
      ><x-ui.lucide name="chevron-left" /></a>

      {{-- Page numbers --}}
      @foreach($pages as $idx => $page)
        @if($idx > 0 && $page - $pages[$idx - 1] > 1)
          <span class="ui-table-pagination__ellipsis" aria-hidden="true">…</span>
        @endif
        <a
          href="{{ $buildUrl($page) }}"
          class="ui-table-pagination__btn {{ $page === $currentPage ? 'is-active' : '' }}"
          @if($page === $currentPage) aria-current="page" @endif
        >{{ $page }}</a>
      @endforeach

      {{-- Next --}}
      <a
        href="{{ $paginator->nextPageUrl() ? $buildUrl($currentPage + 1) : '#' }}"
        class="ui-table-pagination__btn"
        @if(!$paginator->nextPageUrl()) aria-disabled="true" tabindex="-1" @endif
        aria-label="Halaman berikutnya"
      ><x-ui.lucide name="chevron-right" /></a>
    </nav>
  </div>
</div>

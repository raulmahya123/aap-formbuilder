{{-- resources/views/admin/documents/show.blade.php --}}
@extends('layouts.app')

@section('title', $document->doc_no.' — '.$document->title)

@section('content')
@php
  // ===== Snapshot layout dari template (disimpan di documents) =====
  $L = $document->layout_config ?? [];
  $pageW = (int) data_get($L, 'page.width', 794);     // px
  $pageH = (int) data_get($L, 'page.height', 1123);   // px
  $mTop  = (int) data_get($L, 'margins.top', 40);
  $mRight= (int) data_get($L, 'margins.right', 35);
  $mBot  = (int) data_get($L, 'margins.bottom', 40);
  $mLeft = (int) data_get($L, 'margins.left', 35);
  $fontPt= (int) data_get($L, 'font.size', 12);

  $hdr = $document->header_config ?? [];
  $ftr = $document->footer_config ?? [];
  $sig = $document->signature_config ?? [];
  $sections = collect($document->sections ?? [])->map(fn($s)=>is_array($s)?$s:[])->values();

  // ===== Hitung total halaman (autoFlow sederhana sesuai editor) =====
  $contentBottom = $pageH - $mBot;
  $pages = 1;
  foreach ($sections as $s) {
    $pg = max(1, (int)($s['page'] ?? 1));
    $top = is_numeric($s['top'] ?? null) ? (int)$s['top'] : ($mTop + 60);
    $height = is_numeric($s['height'] ?? null) ? (int)$s['height'] : 120;
    $auto = !empty($s['autoFlow']) && empty($s['repeatEachPage']);
    if ($auto && ($top + $height) > $contentBottom) $pg += 1;
    $pages = max($pages, $pg);
  }

  // ===== Status & badge =====
  $status = $document->controlled_status ?? 'controlled';
  $statusBadge = [
    'controlled'   => 'bg-green-50 text-green-700 border border-green-200',
    'uncontrolled' => 'bg-yellow-50 text-yellow-900 border border-yellow-200',
    'obsolete'     => 'bg-red-50 text-red-800 border border-red-200',
  ][$status] ?? 'bg-gray-50 text-gray-800 border border-gray-200';

  // ===== Kumpulkan blocks per halaman (header/footer repeat + sections) =====
  $blocksByPage = [];
  for ($p=1; $p<=$pages; $p++) $blocksByPage[$p]=[];

  // header/footer default posisinya sama kayak editor
  $blocksByPage[1][] = [
    'type'=>'header','page'=>1,'repeat'=>true,
    'top'=>max(8, $mTop-28),'left'=>$mLeft,
    'width'=>$pageW - ($mLeft+$mRight),'height'=>36,
  ];
  $blocksByPage[1][] = [
    'type'=>'footer','page'=>1,'repeat'=>true,
    'top'=>$pageH - ($mBot + 28),'left'=>$mLeft,
    'width'=>$pageW - ($mLeft+$mRight),'height'=>28,
  ];

  // sections → blocks absolut
  foreach ($sections as $s) {
    $basePg = max(1,(int)($s['page'] ?? 1));
    $pagesTo = !empty($s['repeatEachPage']) ? range(1,$pages) : [$basePg];

    foreach ($pagesTo as $pg) {
      $top = is_numeric($s['top'] ?? null) ? (int)$s['top'] : ($mTop + 60);
      $left= is_numeric($s['left']?? null) ? (int)$s['left']: $mLeft;
      $width=is_numeric($s['width']??null) ? (int)$s['width']: ($pageW-$mLeft-$mRight);
      $height=is_numeric($s['height']??null)? (int)$s['height']: 120;

      // autoFlow sederhana: kalau nabrak bottom, pindah halaman & reset top ke margin
      if (empty($s['repeatEachPage']) && !empty($s['autoFlow']) && ($top+$height) > $contentBottom) {
        $pg = min($pages, $pg+1);
        $top = $mTop;
      }

      $blocksByPage[$pg][] = [
        'type' => ($s['type'] ?? 'text')==='table' ? 'table' : 'html',
        'page' => $pg, 'repeat'=>false,
        'top'=>$top,'left'=>$left,'width'=>$width,'height'=>$height,
        'payload'=>$s
      ];
    }
  }
@endphp

<div class="max-w-[{{ $pageW+32 }}px] mx-auto p-6 space-y-5">

  {{-- Top bar / meta ringkas --}}
  <div class="flex items-start justify-between gap-4 print:hidden">
    <div>
      <h1 class="text-xl font-semibold text-[#1D1C1A]">{{ $document->doc_no }} — {{ $document->title }}</h1>
      <div class="mt-1 text-xs text-gray-600">
        Rev {{ $document->revision_no ?? 0 }}
        @if($document->effective_date) • Eff.Date {{ $document->effective_date->format('d M Y') }} @endif
        @if($document->class) • Class {{ $document->class }} @endif
        @if($document->dept_code) • Dept: {{ $document->dept_code }} @endif
        @if($document->doc_type) • Type: {{ $document->doc_type }} @endif
        @if($document->project_code) • Project: {{ $document->project_code }} @endif
        @if($document->department?->name) • Owner: {{ $document->department->name }} @endif
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-2 py-1 rounded-lg text-xs capitalize {{ $statusBadge }}">{{ $status }}</span>
      @can('update',$document)
        <a href="{{ route('admin.documents.edit',$document) }}" class="px-3 py-2 rounded-xl border">Edit</a>
      @endcan
      <a href="{{ route('admin.documents.index') }}" class="px-3 py-2 rounded-xl border">← Kembali</a>
      <button onclick="window.print()" class="px-3 py-2 rounded-xl border">Print</button>
    </div>
  </div>

  {{-- ===== Render halaman per canvas (px 1:1 dengan editor) ===== --}}
  @for($p=1;$p<=$pages;$p++)
    <div class="relative bg-white border rounded-2xl shadow-sm overflow-hidden mb-6 page"
         style="width:{{ $pageW }}px;height:{{ $pageH }}px;">

      {{-- Watermark utk uncontrolled/obsolete --}}
      @if(in_array($status,['uncontrolled','obsolete']))
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-10">
          <div class="text-7xl font-black tracking-widest rotate-[-25deg] select-none">{{ strtoupper($status) }}</div>
        </div>
      @endif

      {{-- Garis margin (tipis biar tau batas konten) --}}
      <div class="absolute pointer-events-none"
           style="top:{{ $mTop }}px;left:{{ $mLeft }}px;width:{{ $pageW-$mLeft-$mRight }}px;height:{{ $pageH-$mTop-$mBot }}px;outline:1px dashed rgba(0,0,0,.06)"></div>

      {{-- Render blocks halaman ini + blok repeat dari halaman 1 --}}
      @foreach(
        collect($blocksByPage[$p])
          ->merge($p>1 ? collect($blocksByPage[1])->where('repeat',true)->map(fn($b)=>array_merge($b,['page'=>$p])) : [])
          as $b
      )
        {{-- HEADER --}}
        @if($b['type']==='header')
          <div class="absblock flex items-center justify-between bg-white/95"
               style="top:{{ $b['top'] }}px;left:{{ $b['left'] }}px;width:{{ $b['width'] }}px;height:{{ $b['height'] }}px;font-size:12px;">
            <div class="flex items-center gap-2 overflow-hidden {{ data_get($hdr,'logo.position')==='right' ? 'order-2' : '' }}">
              @if(data_get($hdr,'logo.url'))
                <img src="{{ data_get($hdr,'logo.url') }}" class="h-6 w-auto object-contain">
              @endif
              <div class="truncate font-medium">{{ data_get($hdr,'title.text',$document->title) }}</div>
            </div>
            <div class="text-xs text-gray-600 {{ data_get($hdr,'logo.position')==='right' ? 'order-1' : '' }}">
              Doc.No: {{ $document->doc_no ?? '-' }}
            </div>
          </div>
        @endif

        {{-- FOOTER --}}
        @if($b['type']==='footer')
          <div class="absblock flex items-center justify-between bg-white/95"
               style="top:{{ $b['top'] }}px;left:{{ $b['left'] }}px;width:{{ $b['width'] }}px;height:{{ $b['height'] }}px;font-size:11px;">
            <div class="truncate">{{ $ftr['text'] ?? '' }}</div>
            @if(($ftr['show_page_number'] ?? true))
              <div class="text-xs text-gray-600">Halaman {{ $p }} / {{ $pages }}</div>
            @endif
          </div>
        @endif

        {{-- SECTION: HTML/TEXT --}}
        @if($b['type']==='html')
          <div class="absblock prose prose-sm max-w-none overflow-auto"
               style="top:{{ $b['top'] }}px;left:{{ $b['left'] }}px;width:{{ $b['width'] }}px;height:{{ $b['height'] }}px;font-size:{{ $fontPt }}pt;">
            @if(($b['payload']['label'] ?? '')!=='')
              <h3 class="!mt-0">{{ $b['payload']['label'] }}</h3>
            @endif
            {!! $b['payload']['html'] ?? '' !!}
          </div>
        @endif

        {{-- SECTION: TABLE --}}
        @if($b['type']==='table')
          @php
            $s = $b['payload']; $rows=max(1,(int)($s['rows']??0)); $cols=max(1,(int)($s['cols']??0)); $cells=$s['cells']??[];
          @endphp
          <div class="absblock overflow-auto"
               style="top:{{ $b['top'] }}px;left:{{ $b['left'] }}px;width:{{ $b['width'] }}px;height:{{ $b['height'] }}px;">
            @if(($s['label'] ?? '')!=='')
              <div class="font-semibold mb-1">{{ $s['label'] }}</div>
            @endif
            <table class="w-full border-collapse text-sm">
              @for($r=0;$r<$rows;$r++)
                <tr>
                  @for($c=0;$c<$cols;$c++)
                    @php $idx=$r*$cols+$c; @endphp
                    <td class="border px-2 py-1 align-top">{{ $cells[$idx] ?? '' }}</td>
                  @endfor
                </tr>
              @endfor
            </table>
          </div>
        @endif
      @endforeach
    </div>
  @endfor

  {{-- QR / Barcode (opsional, tampilkan di bawah canvas biar rapi) --}}
  @if($document->qr_image_path || $document->barcode_image_path)
    <div class="print:hidden">
      <div class="mt-2 flex gap-6 items-end">
        @if($document->qr_image_path)
          <div><img src="{{ $document->qr_image_path }}" class="h-24"><div class="text-xs text-gray-500">QR</div></div>
        @endif
        @if($document->barcode_image_path)
          <div><img src="{{ $document->barcode_image_path }}" class="h-16"><div class="text-xs text-gray-500">Barcode</div></div>
        @endif
      </div>
    </div>
  @endif

  <p class="text-[11px] text-gray-500 print:hidden">
    Preview mengikuti <b>layout_config</b> & koordinat section (hasil editor). Yang kamu lihat = yang dicetak.
  </p>
</div>

{{-- Styles lokal untuk absolute blocks & printing --}}
<style>
  .absblock{position:absolute}
  @media print{
    body{ -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page{border:0;border-radius:0;box-shadow:none}
    @page{ size:auto; margin:0 } /* margin sudah kita atur di layout_config */
  }
</style>
@endsection

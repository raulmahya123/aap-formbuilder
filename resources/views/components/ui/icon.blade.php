@props(['name', 'class' => 'h-5 w-5'])
@php
  $paths = [
    'dashboard' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13h7V3H3v10Zm11 8h7V11h-7v10ZM3 21h7v-4H3v4Zm11-10h7V3h-7v8Z"/>',
    'wellness' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21c5-3 8-7.5 8-12a4 4 0 0 0-8-1.5A4 4 0 0 0 4 9c0 4.5 3 9 8 12Zm0-12v8m0-4 4-3m-4 1-3-2"/>',
    'department' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5l8-3 8 3v16M4 9h16M8 21v-4h8v4M8 13h.01M12 13h.01M16 13h.01"/>',
    'mandala' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 3h9l3 3v15H6V3Zm9 0v4h4M9 12h6m-6 4h6"/>',
    'chat' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a3 3 0 0 1 3-3h10a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H9l-5 4v-4a3 3 0 0 1-1-2V5Zm4 4h.01M12 9h.01M16 9h.01"/>',
    'contract' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 3h8l3 3v15H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 0v4h3M8 12h7M8 16h5"/>',
    'users' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m9-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm5-7a4 4 0 0 1 0 7m2 10v-2a4 4 0 0 0-3-3.87"/>',
    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 17H3L12 3Zm0 6v4m0 4h.01"/>',
    'company' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V5h9v16M14 9h5v12M8 8h2m-2 4h2m-2 4h2m9-3h.01m-3 0h.01m3 4h.01m-3 0h.01"/>',
    'groups' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5h6v6H4V5Zm10 0h6v6h-6V5ZM4 15h6v6H4v-6Zm10 0h6v6h-6v-6ZM10 8h4m-7 3v4m10-4v4m-7 3h4"/>',
    'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M7 16v-4m4 4V8m4 8v-6m4 6V4"/>',
    'note' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 3h14v18H5V3Zm3 4h8M8 11h8m-8 4h5"/>',
    'report' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14M4 19h16M8 15v-3m4 3V8m4 7v-5"/>',
    'access' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm0 0v6m0-18V3m9 9h-3M6 12H3m15.36 6.36-2.12-2.12M7.76 7.76 5.64 5.64m12.72 0-2.12 2.12M7.76 16.24l-2.12 2.12"/>',
    'shield' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3 20 6v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6l8-3Zm-3 9 2 2 4-4"/>',
    'location' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.3"/>',
    'input' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4h14v16H5V4Zm3 4h8M8 12h5m-5 4h8"/>',
  ];
@endphp
<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
  {!! $paths[$name] ?? $paths['dashboard'] !!}
</svg>

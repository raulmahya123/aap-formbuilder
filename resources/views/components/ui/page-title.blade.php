<h1 {{ $attributes->class(['ui-heading', 'ui-page-title'])->merge(['title' => trim(strip_tags($slot))]) }}>{{ $slot }}</h1>

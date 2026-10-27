<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class StandardPaginationTest extends TestCase
{
    public function test_links_preserve_filters_custom_page_name_and_paginator_path(): void
    {
        $this->app['request'] = \Illuminate\Http\Request::create('/other?site_id=7&search=alat&per=25');
        $paginator = new LengthAwarePaginator(range(1, 10), 80, 10, 2, [
            'path'=>'https://example.test/reports', 'pageName'=>'reports_page', 'fragment'=>'results',
        ]);
        $paginator->appends(['status'=>'open']);
        $html = Blade::render('<x-ui.table-pagination :paginator="$paginator" />', compact('paginator'));
        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $next = $xpath->query('//a[@aria-label="Halaman berikutnya"]')->item(0)->getAttribute('href');
        $this->assertStringStartsWith('https://example.test/reports?', $next);
        parse_str(parse_url($next, PHP_URL_QUERY), $query);
        $this->assertSame(['site_id'=>'7','search'=>'alat','status'=>'open','reports_page'=>'3','per_page'=>'10'], $query);
        $this->assertSame('results', parse_url($next, PHP_URL_FRAGMENT));
        $option = $xpath->query('//select/option')->item(1)->getAttribute('value');
        parse_str(parse_url($option, PHP_URL_QUERY), $query);
        $this->assertSame('1', $query['reports_page']);
        $this->assertSame('25', $query['per_page']);
    }

    public function test_empty_pagination_and_single_page_have_disabled_navigation(): void
    {
        foreach ([0, 1] as $total) {
            $paginator = new LengthAwarePaginator($total ? [1] : [], $total, 10, 1);
            $html = Blade::render('<x-ui.table-pagination :paginator="$paginator" />', compact('paginator'));
            $document = new \DOMDocument();
            @$document->loadHTML($html);
            $xpath = new \DOMXPath($document);
            $this->assertSame(2, $xpath->query('//a[@aria-disabled="true" and @tabindex="-1"]')->length);
            $this->assertSame(4, $xpath->query('//select/option')->length);
            if (!$total) $this->assertStringContainsString('Tidak ada data', $html);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Support\SiteSelection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SiteSelectionTest extends TestCase
{
    private function selectionRequest(array $query = [], bool $allowed = true): Request
    {
        $request = Request::create('/admin/dashboard', 'GET', $query);
        $request->setLaravelSession(app('session.store'));
        $request->session()->put('active_site_id', 7);
        $request->setUserResolver(fn () => new class($allowed) {
            public function __construct(private bool $allowed) {}
            public function hasSiteAccess(int $id): bool { return $this->allowed; }
        });
        return $request;
    }

    public function test_global_selection_is_used_when_local_filter_is_absent(): void
    {
        $this->assertSame(7, SiteSelection::id($this->selectionRequest()));
    }

    public function test_local_filter_and_explicit_all_override_global_selection(): void
    {
        $this->assertSame(9, SiteSelection::id($this->selectionRequest(['site_id' => 9])));
        $this->assertNull(SiteSelection::id($this->selectionRequest(['site_id' => ''])));
    }

    public function test_site_without_permission_is_rejected(): void
    {
        $this->expectException(HttpException::class);
        SiteSelection::id($this->selectionRequest(['site_id'=>7], false));
    }

    public function test_revoked_session_site_is_cleared(): void
    {
        $request = $this->selectionRequest([], false);
        $this->assertNull(SiteSelection::id($request));
        $this->assertFalse($request->session()->has('active_site_id'));
    }
}

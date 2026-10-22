<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WellnessResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WellnessController extends Controller
{
    public function index(Request $request): View
    {
        $siteId = \App\Support\SiteSelection::id($request);
        $allowed = $request->user()->allowedSiteIds();
        $sites = \App\Models\Site::query()
            ->when(is_array($allowed), fn ($q) => $q->whereIn('id', $allowed ?: [-1]))
            ->when($siteId, fn ($q) => $q->where('id', $siteId))->get(['id', 'code', 'name']);
        $labels = $sites->flatMap(fn ($site) => [$site->name, $site->code])->filter()->unique()->values()->all();
        $base = WellnessResponse::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('nik', 'like', '%'.$request->string('q').'%')))
            ->when($siteId || is_array($allowed), fn ($q) => $q->whereIn('site', $labels ?: ['__none__']))
            ->when($request->filled('site'), fn ($query) => $query->where('site', 'like', '%' . (string) $request->string('site') . '%'))
            ->when($request->filled('work_readiness'), fn ($query) => $query->where('work_readiness', (string) $request->string('work_readiness')));
        $responses = (clone $base)
            ->latest()
            ->paginate(\App\Support\Listing::perPage())->withQueryString();

        $stats = [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'not_ready' => (clone $base)->whereIn('work_readiness', ['tidak', 'off_day'])->count(),
            'complaints' => (clone $base)->whereIn('health_complaint', ['ya', 'ringan', 'berat'])->count(),
        ];

        return view('admin.wellness.index', compact('responses', 'stats'));
    }
}

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
        $responses = WellnessResponse::query()
            ->when($request->filled('site'), fn ($query) => $query->where('site', 'like', '%' . (string) $request->string('site') . '%'))
            ->when($request->filled('work_readiness'), fn ($query) => $query->where('work_readiness', (string) $request->string('work_readiness')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $base = WellnessResponse::query();
        $stats = [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'not_ready' => (clone $base)->whereIn('work_readiness', ['tidak', 'off_day'])->count(),
            'complaints' => (clone $base)->whereIn('health_complaint', ['ringan', 'berat'])->count(),
        ];

        return view('admin.wellness.index', compact('responses', 'stats'));
    }
}

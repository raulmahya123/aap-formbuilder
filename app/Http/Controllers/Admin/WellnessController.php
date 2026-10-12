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
            ->when($request->filled('mood'), fn ($query) => $query->where('mood', (string) $request->string('mood')))
            ->when($request->filled('department'), fn ($query) => $query->where('department', 'like', '%' . (string) $request->string('department') . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $base = WellnessResponse::query();
        $stats = [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('created_at', today())->count(),
            'good_mood' => (clone $base)->where('mood', 'baik')->count(),
            'high_stress' => (clone $base)->where('stress_level', '>=', 4)->count(),
        ];

        return view('admin.wellness.index', compact('responses', 'stats'));
    }
}

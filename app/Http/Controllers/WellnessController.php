<?php

namespace App\Http\Controllers;

use App\Models\WellnessResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WellnessController extends Controller
{
    public function create(): View
    {
        return view('wellness.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'department' => ['nullable', 'string', 'max:120'],
            'mood' => ['required', 'in:baik,cukup,kurang'],
            'stress_level' => ['required', 'integer', 'between:1,5'],
            'sleep_hours' => ['nullable', 'numeric', 'between:0,24'],
            'physical_activity' => ['nullable', 'in:tidak_ada,ringan,sedang,aktif'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        WellnessResponse::create($validated);

        return redirect()->route('wellness.create')->with('success', 'Terima kasih, respons wellness Anda sudah tersimpan.');
    }
}

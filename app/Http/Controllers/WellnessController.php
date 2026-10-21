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
            'nik' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:120'],
            'site' => ['required', 'string', 'max:120'],
            'shift' => ['required', 'in:pagi,malam'],
            'sleep_before_shift' => ['required', 'in:lebih_6_jam,6_jam,kurang_6_jam'],
            'health_complaint' => ['required', 'in:tidak_ada,ringan,berat'],
            'drowsy_medication' => ['required', 'in:ya,tidak'],
            'work_readiness' => ['required', 'in:ya,tidak,off_day'],
        ]);

        WellnessResponse::create($validated);

        return redirect()->route('wellness.create')->with('success', 'Terima kasih, respons wellness Anda sudah tersimpan.');
    }
}

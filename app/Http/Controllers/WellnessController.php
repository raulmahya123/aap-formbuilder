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
            'sleep_quality' => ['required', 'in:baik,cukup,buruk'],
            'sleep_quality_reason' => ['exclude_unless:sleep_quality,buruk', 'required', 'string', 'max:2000'],
            'heavy_drowsiness' => ['required', 'in:ya,tidak'],
            'health_complaint' => ['required', 'in:tidak,ya'],
            'health_complaint_details' => ['exclude_unless:health_complaint,ya', 'required', 'string', 'max:2000'],
            'drowsy_medication' => ['required', 'in:ya,tidak'],
            'work_readiness' => ['required', 'in:ya,tidak,off_day'],
        ], [
            'sleep_quality.required' => 'Pilih kualitas tidur Anda.',
            'health_complaint_details.required' => 'Jelaskan keluhan atau gejala yang Anda rasakan.',
            'sleep_quality_reason.required' => 'Sertakan alasan kualitas tidur buruk.',
            'heavy_drowsiness.required' => 'Pilih apakah Anda mengantuk berat atau sulit berkonsentrasi.',
        ]);

        WellnessResponse::create($validated);

        return redirect()->route('wellness.create')->with('success', 'Terima kasih, Health Assesment Checklist Anda sudah tersimpan.');
    }
}

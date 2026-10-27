<?php

namespace App\Http\Controllers;

use App\Models\WellnessResponse;
use App\Models\Department;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WellnessController extends Controller
{
    public function create(): View
    {
        return view('wellness.create', [
            'departments'=>Department::orderBy('name')->get(['id', 'name']),
            'sites'=>Site::orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'nik' => ['required', 'string', 'max:50'],
            'position' => ['required', 'string', 'max:120'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'site_id' => ['required', 'integer', 'exists:sites,id'],
            'shift' => ['required', 'in:pagi,malam'],
            'sleep_before_shift' => ['required', 'in:lebih_6_jam,6_jam,kurang_6_jam'],
            'sleep_quality' => ['required', 'in:baik,cukup,buruk'],
            'sleep_quality_reason' => ['exclude_unless:sleep_quality,buruk', 'required', 'string', 'max:2000'],
            'heavy_drowsiness' => ['required', 'in:ya,tidak'],
            'health_complaint' => ['required', 'in:tidak,ya'],
            'health_complaint_details' => ['exclude_unless:health_complaint,ya', 'required', 'string', 'max:2000'],
            'drowsy_medication' => ['required', 'in:ya,tidak'],
            'drowsy_medication_details' => ['exclude_unless:drowsy_medication,ya', 'required', 'string', 'max:2000'],
            'work_readiness' => ['required', 'in:ya,tidak,off_day'],
        ], [
            'sleep_quality.required' => 'Pilih kualitas tidur Anda.',
            'department_id.required' => 'Pilih departemen Anda.',
            'department_id.exists' => 'Departemen yang dipilih tidak tersedia. Pilih kembali.',
            'site_id.required' => 'Pilih site Anda.',
            'site_id.exists' => 'Site yang dipilih tidak tersedia. Pilih kembali.',
            'drowsy_medication_details.required' => 'Tuliskan nama obat yang sedang Anda konsumsi.',
            'health_complaint_details.required' => 'Jelaskan keluhan atau gejala yang Anda rasakan.',
            'sleep_quality_reason.required' => 'Sertakan alasan kualitas tidur buruk.',
            'heavy_drowsiness.required' => 'Pilih apakah Anda mengantuk berat atau sulit berkonsentrasi.',
        ]);

        $department = Department::findOrFail($validated['department_id']);
        $site = Site::findOrFail($validated['site_id']);
        // Preserve the existing site-code representation for legacy reports and filters.
        $validated['department'] = $department->name;
        $validated['site'] = $site->code ?: $site->name;
        WellnessResponse::create($validated);

        return redirect()->route('wellness.create')->with('success', 'Terima kasih, Health Assesment Checklist Anda sudah tersimpan.');
    }
}

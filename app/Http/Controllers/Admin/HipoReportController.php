<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HipoReportController extends Controller
{
    public function index()
    {
        return view('admin.hipo.index', [
            'reports' => HipoReport::latest()->get()
        ]);
    }

    // ✅ CREATE FORM
    public function create()
    {
        return view('admin.hipo.create');
    }

    // ✅ STORE DATA
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_time' => 'required|date',
            'jobsite' => 'required|string',
            'jenis_hipo' => 'required|in:HIPO,Nearmiss',
            'category' => 'required|string',
            'risk_level' => 'required|in:Low,Medium,High,Extreme',
            'kta' => 'required|string',
            'tta' => 'required|string',
            'potential_consequence' => 'required|string',
            'stop_work' => 'nullable|boolean',

            // PIC default kosong saat create
            'pic' => 'nullable|string',
            'pic_engineering' => 'nullable|string',
            'pic_administrative' => 'nullable|string',
            'pic_work_practice' => 'nullable|string',
            'pic_ppe' => 'nullable|string',

            'admin_note' => 'nullable|string',

            // Evidence
            'evidence_engineering' => 'nullable|image|max:2048',
            'evidence_administrative' => 'nullable|image|max:2048',
            'evidence_work_practice' => 'nullable|image|max:2048',
            'evidence_ppe' => 'nullable|image|max:2048',
        ]);

        // Default status saat create
        $validated['status'] = 'Open';

        // Upload evidence jika ada
        foreach (['engineering','administrative','work_practice','ppe'] as $key) {
            if ($request->hasFile("evidence_$key")) {
                $validated["evidence_$key"] =
                    $request->file("evidence_$key")
                            ->store("hipo/$key", 'public');
            }
        }

        HipoReport::create($validated);

        return redirect()
            ->route('admin.hipo.index')
            ->with('success', 'Data HIPO berhasil ditambahkan');
    }

    public function show(HipoReport $hipo)
    {
        return view('admin.hipo.show', compact('hipo'));
    }

    public function update(Request $request, HipoReport $hipo)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,On Progress,Closed,Rejected',
            'pic' => 'required|string',
            'pic_engineering' => 'required|string',
            'pic_administrative' => 'required|string',
            'pic_work_practice' => 'required|string',
            'pic_ppe' => 'required|string',
            'admin_note' => 'nullable|string',
            'evidence_engineering' => 'nullable|image|max:2048',
            'evidence_administrative' => 'nullable|image|max:2048',
            'evidence_work_practice' => 'nullable|image|max:2048',
            'evidence_ppe' => 'nullable|image|max:2048',
        ]);

        foreach (['engineering','administrative','work_practice','ppe'] as $key) {
            if ($request->hasFile("evidence_$key")) {

                if ($hipo->{"evidence_$key"}) {
                    Storage::disk('public')->delete($hipo->{"evidence_$key"});
                }

                $validated["evidence_$key"] =
                    $request->file("evidence_$key")
                            ->store("hipo/$key", 'public');
            }
        }

        $hipo->update($validated);

        return back()->with('success', 'Data HIPO berhasil diperbarui');
    }

    public function destroy(HipoReport $hipo)
    {
        foreach (['engineering','administrative','work_practice','ppe'] as $key) {
            if ($hipo->{"evidence_$key"}) {
                Storage::disk('public')->delete($hipo->{"evidence_$key"});
            }
        }

        $hipo->delete();

        return back()->with('success', 'Data HIPO berhasil dihapus');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HipoReportController extends Controller
{
    // ==============================
    // INDEX
    // ==============================
    public function index()
    {
        $reports = HipoReport::latest()->get();

        return view('admin.hipo.index', compact('reports'));
    }

    // ==============================
    // CREATE
    // ==============================
    public function create()
    {
        return view('admin.hipo.create');
    }

    // ==============================
    // STORE
    // ==============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_time' => 'required|date',
            'jobsite' => 'required|string|max:255',
            'shift' => 'required|string|max:100',
            'source' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'risk_level' => 'required|in:Low,Medium,High,Extreme',

            'kta' => 'required|string',
            'tta' => 'required|string',
            'description' => 'required|string',
            'potential_consequence' => 'required|string',

            'stop_work' => 'nullable|boolean',

            'control_engineering' => 'nullable|string',
            'control_administrative' => 'nullable|string',
            'control_work_practice' => 'nullable|string',
            'control_ppe' => 'nullable|string',

            'pic_engineering' => 'nullable|string|max:255',
            'pic_administrative' => 'nullable|string|max:255',
            'pic_work_practice' => 'nullable|string|max:255',
            'pic_ppe' => 'nullable|string|max:255',

            'evidence_engineering' => 'nullable|image|max:2048',
            'evidence_administrative' => 'nullable|image|max:2048',
            'evidence_work_practice' => 'nullable|image|max:2048',
            'evidence_ppe' => 'nullable|image|max:2048',
        ]);

        // ================= SYSTEM FIELDS =================
        $validated['status'] = 'Open';
        $validated['user_id'] = auth()->id();
        $validated['reporter_name'] = auth()->user()->name;
        $validated['stop_work'] = $request->boolean('stop_work');
        $validated['site_id'] = session('active_site_id');

        // ================= HANDLE FILE UPLOAD =================
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {

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

    // ==============================
    // SHOW
    // ==============================
    public function show(HipoReport $hipo)
    {
        return view('admin.hipo.show', compact('hipo'));
    }

    // ==============================
    // UPDATE
    // ==============================
    public function update(Request $request, HipoReport $hipo)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,On Progress,Closed,Rejected',
            'description' => 'required|string',

            'control_engineering' => 'nullable|string',
            'control_administrative' => 'nullable|string',
            'control_work_practice' => 'nullable|string',
            'control_ppe' => 'nullable|string',

            'pic_engineering' => 'nullable|string|max:255',
            'pic_administrative' => 'nullable|string|max:255',
            'pic_work_practice' => 'nullable|string|max:255',
            'pic_ppe' => 'nullable|string|max:255',

            'admin_note' => 'nullable|string',

            'evidence_engineering' => 'nullable|image|max:2048',
            'evidence_administrative' => 'nullable|image|max:2048',
            'evidence_work_practice' => 'nullable|image|max:2048',
            'evidence_ppe' => 'nullable|image|max:2048',
        ]);

        // HANDLE FILE UPDATE
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {

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

    // ==============================
    // DELETE
    // ==============================
    public function destroy(HipoReport $hipo)
    {
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {

            if ($hipo->{"evidence_$key"}) {
                Storage::disk('public')->delete($hipo->{"evidence_$key"});
            }
        }

        $hipo->delete();

        return back()->with('success', 'Data HIPO berhasil dihapus');
    }
}

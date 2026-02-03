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

    public function show(HipoReport $hipo)
    {
        return view('admin.hipo.show', compact('hipo'));
    }

    public function update(Request $request, HipoReport $hipo)
    {
        $validated = $request->validate([
            'status' => 'required|in:Open,On Progress,Closed,Rejected',

            // PIC Utama
            'pic' => 'required|string',

            // PIC per kontrol
            'pic_engineering' => 'required|string',
            'pic_administrative' => 'required|string',
            'pic_work_practice' => 'required|string',
            'pic_ppe' => 'required|string',

            // Admin note (opsional)
            'admin_note' => 'nullable|string',

            // Evidence (opsional saat update)
            'evidence_engineering' => 'nullable|image|max:2048',
            'evidence_administrative' => 'nullable|image|max:2048',
            'evidence_work_practice' => 'nullable|image|max:2048',
            'evidence_ppe' => 'nullable|image|max:2048',
        ]);

        // Handle evidence update (jika diganti)
        foreach (['engineering','administrative','work_practice','ppe'] as $key) {
            if ($request->hasFile("evidence_$key")) {

                // hapus file lama
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
        // hapus semua evidence
        foreach (['engineering','administrative','work_practice','ppe'] as $key) {
            if ($hipo->{"evidence_$key"}) {
                Storage::disk('public')->delete($hipo->{"evidence_$key"});
            }
        }

        $hipo->delete();

        return back()->with('success', 'Data HIPO berhasil dihapus');
    }
}

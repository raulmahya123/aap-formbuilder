<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use Illuminate\Http\Request;

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
        $hipo->update([
            'status' => $request->status,
            'pic' => $request->pic,
        ]);

        return back()->with('success', 'Status HIPO diperbarui');
    }

    public function destroy(HipoReport $hipo)
    {
        $hipo->delete();
        return back()->with('success', 'Data HIPO dihapus');
    }
}

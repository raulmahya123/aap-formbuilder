<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HipoReportController extends Controller
{
    public function index()
    {
        return view('user.hipo.index', [
            'reports' => HipoReport::where('user_id', Auth::id())->latest()->get()
        ]);
    }

    public function create()
    {
        return view('user.hipo.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jobsite' => 'required|string',
            'reporter_name' => 'required|string',
            'report_time' => 'required|date',
            'shift' => 'required',
            'source' => 'required',
            'category' => 'required',
            'description' => 'required',
            'potential_consequence' => 'required',
            'risk_level' => 'nullable|string',
            'stop_work' => 'required|boolean',
            'control_engineering' => 'nullable|string',
            'control_administrative' => 'nullable|string',
            'control_work_practice' => 'nullable|string',
            'control_ppe' => 'nullable|string',
            'pic' => 'nullable|string',
            'evidence_file' => 'nullable|file|mimes:jpg,png,pdf|max:5120',
        ]);

        if ($request->hasFile('evidence_file')) {
            $data['evidence_file'] = $request->file('evidence_file')
                ->store('hipo-evidence', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['status'] = 'Open';

        HipoReport::create($data);

        return redirect()->route('user.hipo.index')
            ->with('success', 'Laporan HIPO berhasil dikirim');
    }
}

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
            'reports' => HipoReport::where('user_id', Auth::id())
                ->latest()
                ->get()
        ]);
    }

    public function create()
    {
        return view('user.hipo.create');
    }

    public function store(Request $request)
    {
        // VALIDASI SESUAI FORM BARU
        $data = $request->validate([
            'jobsite' => 'required|string',
            'reporter_name' => 'required|string',
            'pic' => 'required|string',

            'report_time' => 'required|date',
            'shift' => 'required|string',
            'source' => 'required|string',
            'category' => 'required|string',
            'risk_level' => 'required|string',

            'description' => 'required|string',
            'potential_consequence' => 'required|string',
            'stop_work' => 'required|boolean',

            // 4 KONTROL RISIKO (WAJIB)
            'control_engineering' => 'required|string',
            'control_administrative' => 'required|string',
            'control_work_practice' => 'required|string',
            'control_ppe' => 'required|string',

            // PIC PER KONTROL (WAJIB)
            'pic_engineering' => 'required|string',
            'pic_administrative' => 'required|string',
            'pic_work_practice' => 'required|string',
            'pic_ppe' => 'required|string',

            // EVIDENCE PER KONTROL (WAJIB)
            'evidence_engineering' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'evidence_administrative' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'evidence_work_practice' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'evidence_ppe' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // SIMPAN FILE EVIDENCE
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {
            $data["evidence_$key"] = $request
                ->file("evidence_$key")
                ->store("hipo/$key", 'public');
        }

        // DATA TAMBAHAN SYSTEM
        $data['user_id'] = Auth::id();
        $data['status'] = 'Open';

        HipoReport::create($data);

        return redirect()
            ->route('user.hipo.index')
            ->with('success', 'Laporan HIPO berhasil dikirim');
    }
}

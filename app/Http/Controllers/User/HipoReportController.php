<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
        $sites = $this->siteOptions();

        return view('user.hipo.create', compact('sites'));
    }

    public function store(Request $request)
    {
        // VALIDASI SESUAI FORM BARU
        $data = $request->validate([
            'site_id' => 'required|integer|exists:sites,id',
            'pic' => 'required|string',

            'report_time' => 'required|date',
            'shift' => 'required|string',
            'source' => 'required|string',
            'category' => 'required|string',
            'risk_level' => 'required|string',

            'description' => 'required|string',
            'potential_consequence' => 'required|string',
            'stop_work' => 'nullable|boolean',

            // 4 KONTROL RISIKO (WAJIB)
            'control_engineering' => 'required|string',
            'control_administrative' => 'required|string',
            'control_work_practice' => 'required|string',
            'control_ppe' => 'required|string',

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
        $data['reporter_name'] = Auth::user()->name;
        $data['stop_work'] = $request->boolean('stop_work');
        $data['jobsite'] = $this->jobsiteLabel(Site::with('company:id,code,name')->findOrFail($data['site_id']));
        if (Schema::hasColumn('hipo_reports', 'jenis_hipo')) {
            $data['jenis_hipo'] = $data['category'] === 'Nearmiss' ? 'Nearmiss' : 'HIPO';
        }

        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {
            $data["pic_$key"] = $data['pic'];
        }
        unset($data['pic']);

        HipoReport::create($data);

        return redirect()
            ->route('user.hipo.index')
            ->with('success', 'Laporan HIPO berhasil dikirim');
    }

    private function siteOptions()
    {
        return Site::with('company:id,code,name')
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'company_id'])
            ->map(function (Site $site) {
                return [
                    'id' => $site->id,
                    'label' => $this->jobsiteLabel($site),
                ];
            })
            ->values();
    }

    private function jobsiteLabel(Site $site): string
    {
        $company = $site->company?->code ?: $site->company?->name;
        $siteName = $site->code ?: $site->name;

        return $company ? "{$company}-{$siteName}" : $siteName;
    }
}

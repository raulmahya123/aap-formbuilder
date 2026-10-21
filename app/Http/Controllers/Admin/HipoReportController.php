<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HipoReport;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;

class HipoReportController extends Controller
{
    // ==============================
    // INDEX
    // ==============================
    public function index(Request $request)
    {
        $allowed = auth()->user()->allowedSiteIds();
        $siteId = \App\Support\SiteSelection::id($request);
        $reports = HipoReport::when(is_array($allowed), fn ($q) => $q->whereIn('site_id', $allowed ?: [-1]))
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w->where('jobsite', 'like', '%'.$request->string('search').'%')->orWhere('reporter_name', 'like', '%'.$request->string('search').'%')->orWhere('description', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->filled('risk_level'), fn ($q) => $q->where('risk_level', $request->string('risk_level')->toString()))
            ->latest()->paginate(\App\Support\Listing::perPage())->withQueryString();

        return view('admin.hipo.index', compact('reports'));
    }

    // ==============================
    // CREATE
    // ==============================
    public function create()
    {
        $sites = $this->siteOptions();

        return view('admin.hipo.create', compact('sites'));
    }

    // ==============================
    // STORE
    // ==============================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_time' => 'required|date',
            'site_id' => 'required|integer|exists:sites,id',
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

        Gate::authorize('site-access', (int) $validated['site_id']);

        // ================= SYSTEM FIELDS =================
        $validated['status'] = 'Open';
        $validated['user_id'] = auth()->id();
        $validated['reporter_name'] = auth()->user()->name;
        $validated['stop_work'] = $request->boolean('stop_work');
        $validated['jobsite'] = $this->jobsiteLabel(Site::with('company:id,code,name')->findOrFail($validated['site_id']));
        if (Schema::hasColumn('hipo_reports', 'jenis_hipo')) {
            $validated['jenis_hipo'] = $validated['category'] === 'Nearmiss' ? 'Nearmiss' : 'HIPO';
        }

        // ================= HANDLE FILE UPLOAD =================
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {

            if ($request->hasFile("evidence_$key")) {

                $validated["evidence_$key"] =
                    $request->file("evidence_$key")
                        ->store("hipo/$key", 'mandala_uploads');
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
        Gate::authorize('site-access', (int) $hipo->site_id);
        return view('admin.hipo.show', compact('hipo'));
    }

    // ==============================
    // UPDATE
    // ==============================
    public function update(Request $request, HipoReport $hipo)
    {
        Gate::authorize('site-access', (int) $hipo->site_id);
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
                    Storage::disk('mandala_uploads')->delete($hipo->{"evidence_$key"});
                }

                $validated["evidence_$key"] =
                    $request->file("evidence_$key")
                        ->store("hipo/$key", 'mandala_uploads');
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
        Gate::authorize('site-access', (int) $hipo->site_id);
        foreach (['engineering', 'administrative', 'work_practice', 'ppe'] as $key) {

            if ($hipo->{"evidence_$key"}) {
                Storage::disk('mandala_uploads')->delete($hipo->{"evidence_$key"});
            }
        }

        $hipo->delete();

        return back()->with('success', 'Data HIPO berhasil dihapus');
    }

    private function siteOptions()
    {
        $query = Site::with('company:id,code,name')
            ->orderBy('name')
            ->when(is_array(auth()->user()->allowedSiteIds()), function ($q) {
                $q->whereIn('id', auth()->user()->allowedSiteIds() ?: [-1]);
            });

        return $query->get(['id', 'code', 'name', 'company_id'])
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

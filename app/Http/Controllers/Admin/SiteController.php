<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Site, Company};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    /**
     * List + filter (q, company_id)
     */
    public function index(Request $request)
    {
        $q          = trim((string) $request->get('q', ''));
        $companyId  = $request->integer('company_id'); // null kalau ga ada
        $perPage    = (int) $request->get('per', 15);

        $sites = Site::query()
            ->with(['company:id,code,name']) // eager load
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($companyId, fn ($qq) => $qq->where('company_id', $companyId))
            ->orderBy('code')
            ->paginate(max(1, $perPage))
            ->appends($request->only('q', 'company_id', 'per'));

        // dropdown filter perusahaan
        $companies = Company::orderBy('code')->get(['id','code','name']);

        return view('admin.sites.index', compact('sites', 'q', 'companies', 'companyId', 'perPage'));
    }

    /**
     * Show form create
     */
    public function create()
    {
        $site = new Site();

        // untuk select perusahaan di form
        $companies = Company::orderBy('code')->get(['id','code','name']);

        return view('admin.sites.form', compact('site', 'companies'));
    }

    /**
     * Store site baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['required','string','max:20','unique:sites,code'],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'company_id'  => ['nullable','integer','exists:companies,id'], // jadikan 'required' kalau wajib
        ]);

        $site = Site::create($data);

        return redirect()
            ->route('admin.sites.index')
            ->with('success', "Site {$site->code} berhasil dibuat.");
    }

    /**
     * Show form edit
     */
    public function edit(Site $site)
    {
        $companies = Company::orderBy('code')->get(['id','code','name']);
        return view('admin.sites.form', compact('site', 'companies'));
    }

    /**
     * Update site
     */
    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'code'        => [
                'required','string','max:20',
                Rule::unique('sites','code')->ignore($site->id),
            ],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'company_id'  => ['nullable','integer','exists:companies,id'], // jadikan 'required' kalau wajib
        ]);

        $site->update($data);

        return redirect()
            ->route('admin.sites.index')
            ->with('success', "Site {$site->code} diperbarui.");
    }

    /**
     * Hapus site
     */
    public function destroy(Site $site)
    {
        $site->delete();
        return back()->with('success', "Site {$site->code} dihapus.");
    }

    /**
     * Ganti active site (di session)
     */
    public function switch(Request $request)
    {
        $request->validate([
            'site_id' => ['nullable','integer','exists:sites,id'],
        ]);

        if (!auth()->user()->isSuperAdmin() && !\Gate::allows('is-admin')) {
            if ($request->filled('site_id')
                && method_exists(auth()->user(),'hasSite')
                && !auth()->user()->hasSite((int)$request->site_id)) {
                return back()->with('error','Kamu tidak punya akses ke site tersebut.');
            }
        }

        session(['active_site_id' => $request->site_id ?: null]);
        return back()->with('ok','Active site updated.');
    }
}

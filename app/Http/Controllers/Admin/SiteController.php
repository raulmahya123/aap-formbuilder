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

        $allowedSiteIds = auth()->user()->allowedSiteIds();
        $sites = Site::query()
            ->with(['company:id,code,name']) // eager load
            ->when(is_array($allowedSiteIds), fn ($qq) => $qq->whereIn('id', $allowedSiteIds ?: [-1]))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('code', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($companyId, fn ($qq) => $qq->where('company_id', $companyId))
            ->orderBy('code')
            ->paginate(\App\Support\Listing::perPage())->withQueryString()
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
        abort_unless(auth()->user()->isSuperAdmin(), 403);
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
        abort_unless(auth()->user()->isSuperAdmin(), 403);
        $data = $request->validate([
            'code'        => ['required','string','max:20','unique:sites,code'],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'company_id'  => ['nullable','integer','exists:companies,id'], // jadikan 'required' kalau wajib
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
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
        abort_unless(auth()->user()->hasSiteAccess((int) $site->id), 403);
        $companies = Company::orderBy('code')->get(['id','code','name']);
        return view('admin.sites.form', compact('site', 'companies'));
    }

    /**
     * Update site
     */
    public function update(Request $request, Site $site)
    {
        abort_unless(auth()->user()->hasSiteAccess((int) $site->id), 403);
        $data = $request->validate([
            'code'        => [
                'required','string','max:20',
                Rule::unique('sites','code')->ignore($site->id),
            ],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'],
            'company_id'  => ['nullable','integer','exists:companies,id'], // jadikan 'required' kalau wajib
            'latitude'    => ['nullable','numeric','between:-90,90'],
            'longitude'   => ['nullable','numeric','between:-180,180'],
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
        abort_unless(auth()->user()->hasSiteAccess((int) $site->id), 403);
        $site->delete();
        return back()->with('success', "Site {$site->code} dihapus.");
    }

    /**
     * Ganti active site (di session)
     */
    public function switch(Request $request)
    {
        $request->validateWithBag('siteSelection', [
            'site_id' => ['nullable','integer','exists:sites,id'],
        ]);

        if ($request->filled('site_id') && !auth()->user()->hasSiteAccess((int) $request->site_id)) {
            return back()->with('error','Kamu tidak punya akses ke site tersebut.');
        }

        session(['active_site_id' => $request->filled('site_id') ? (int) $request->site_id : null]);
        // Remove stale local site filters and page numbers when the global selection changes.
        $previous = url()->previous();
        $parts = parse_url($previous);
        $current = parse_url($request->getSchemeAndHttpHost());
        if (($parts['host'] ?? null) !== ($current['host'] ?? null) || ($parts['port'] ?? null) !== ($current['port'] ?? null)) {
            return redirect()->route('dashboard')->with('ok', 'Site aktif diperbarui.');
        }
        parse_str($parts['query'] ?? '', $query);
        unset($query['site_id'], $query['site'], $query['page']);
        $target = '/'.ltrim($parts['path'] ?? '/', '/').($query ? '?'.http_build_query($query) : '');
        return redirect($target)->with('ok', 'Site aktif diperbarui.');
    }
}

<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SiteRequest;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $sites = Site::query()
            ->when($q, fn($qq) => $qq->where(fn($w) =>
                $w->where('name', 'like', "%$q%")
                  ->orWhere('code', 'like', "%$q%")
            ))
            ->orderBy('code')
            ->paginate(15)
            ->appends(['q' => $q]);

        return view('admin.sites.index', compact('sites', 'q'));
    }

    public function create()
    {
        $site = new Site();
        return view('admin.sites.form', compact('site'));
    }

    public function store(SiteRequest $request)
    {
        $site = Site::create($request->validated());
        return redirect()->route('admin.sites.index')
            ->with('success', "Site {$site->code} berhasil dibuat.");
    }

    public function edit(Site $site)
    {
        return view('admin.sites.form', compact('site'));
    }

    public function update(SiteRequest $request, Site $site)
    {
        $site->update($request->validated());
        return redirect()->route('admin.sites.index')
            ->with('success', "Site {$site->code} diperbarui.");
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return back()->with('success', "Site {$site->code} dihapus.");
    }

    /**
     * Switch active site (untuk sidebar switcher).
     */
    public function switch(Request $request)
    {
        $request->validate([
            'site_id' => ['nullable','integer','exists:sites,id'],
        ]);

        // Validasi tambahan: jika bukan admin, cek apakah user punya akses ke site tsb
        if (!auth()->user()->isSuperAdmin() && !\Gate::allows('is-admin')) {
            if ($request->filled('site_id') && method_exists(auth()->user(),'hasSite')
                && !auth()->user()->hasSite((int)$request->site_id)) {
                return back()->with('error','Kamu tidak punya akses ke site tersebut.');
            }
        }

        session(['active_site_id' => $request->site_id ?: null]);

        return back()->with('ok','Active site updated.');
    }
}

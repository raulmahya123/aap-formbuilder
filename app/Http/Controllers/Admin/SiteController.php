<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));
        $sites = Site::query()
            ->when($q, fn($qq) => $qq->where(fn($w) =>
                $w->where('name', 'like', "%$q%")
                  ->orWhere('code', 'like', "%$q%")
                  ->orWhere('description', 'like', "%$q%")
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => ['required','string','max:20','unique:sites,code'],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'], // kalau kolom TEXT, tak perlu max
        ]);

        $site = Site::create($data);

        return redirect()->route('admin.sites.index')
            ->with('success', "Site {$site->code} berhasil dibuat.");
    }

    public function edit(Site $site)
    {
        return view('admin.sites.form', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'code'        => [
                'required','string','max:20',
                Rule::unique('sites','code')->ignore($site->id),
            ],
            'name'        => ['required','string','max:150'],
            'description' => ['nullable','string'],
        ]);

        $site->update($data);

        return redirect()->route('admin.sites.index')
            ->with('success', "Site {$site->code} diperbarui.");
    }

    public function destroy(Site $site)
    {
        $site->delete();
        return back()->with('success', "Site {$site->code} dihapus.");
    }

    public function switch(Request $request)
    {
        $request->validate([
            'site_id' => ['nullable','integer','exists:sites,id'],
            // jika pakai UUID: ['nullable','uuid','exists:sites,uuid'],
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

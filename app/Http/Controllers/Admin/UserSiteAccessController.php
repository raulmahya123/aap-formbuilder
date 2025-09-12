<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSiteAccess;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserSiteAccessController extends Controller
{
    // Tampilkan daftar akses + form tambah
    public function index(Request $r)
    {
        // pastikan ada Policy ability: manageSiteAccess
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $q = UserSiteAccess::with(['user:id,name,email', 'site:id,name,code'])
            ->orderBy('id'); // perapihan

        if ($r->filled('site_id')) {
            $q->where('site_id', (int) $r->site_id);
        }
        if ($r->filled('user_id')) {
            $q->where('user_id', (int) $r->user_id);
        }

        return view('admin.site-access.index', [
            'accesses' => $q->paginate(20)->withQueryString(),
            'users'    => User::orderBy('name')->get(['id','name','email']),
            'sites'    => Site::orderBy('code')->get(['id','name','code']),
        ]);
    }

    // Tambah akses (assign)
    public function store(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'user_id' => ['required','exists:users,id'],
            'site_id' => [
                'required','exists:sites,id',
                Rule::unique('user_site_access')->where(fn ($q) =>
                    $q->where('user_id', $r->user_id)
                      ->where('site_id', $r->site_id)
                ),
            ],
        ]);

        UserSiteAccess::create($data);

        return back()->with('ok', 'Akses site berhasil ditambahkan.');
    }

    // Hapus akses (revoke)
    public function destroy(UserSiteAccess $userSiteAccess)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $userSiteAccess->delete();

        return back()->with('ok', 'Akses site berhasil dihapus.');
    }

    // (Opsional) Tambah massal: 1 user ke banyak site
    public function bulkAttachSites(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'user_id'    => ['required','exists:users,id'],
            'site_ids'   => ['required','array'],
            'site_ids.*' => ['integer','exists:sites,id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        // kalau relasi pivot bernama 'sites' (belongsToMany)
        if (method_exists($user, 'sites')) {
            $user->sites()->syncWithoutDetaching($data['site_ids']);
        } else {
            // fallback via model pivot
            foreach ($data['site_ids'] as $sid) {
                UserSiteAccess::firstOrCreate(['user_id' => $user->id, 'site_id' => $sid]);
            }
        }

        return back()->with('ok', 'Akses site berhasil ditambahkan (bulk).');
    }

    // Alias agar sesuai route admin.site_access.bulk
    public function bulk(Request $r)
    {
        return $this->bulkAttachSites($r);
    }

    // Cabut massal: 1 user dari banyak site
    public function bulkDetachSites(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'user_id'    => ['required','exists:users,id'],
            'site_ids'   => ['required','array'],
            'site_ids.*' => ['integer','exists:sites,id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        if (method_exists($user, 'sites')) {
            $user->sites()->detach($data['site_ids']);
        } else {
            UserSiteAccess::where('user_id', $user->id)
                ->whereIn('site_id', $data['site_ids'])
                ->delete();
        }

        return back()->with('ok', 'Akses site berhasil dicabut (bulk).');
    }

    // Hapus banyak akses berdasarkan ID pivot (checkbox pada tabel)
    public function destroySelected(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'ids'   => ['required','array'],
            'ids.*' => ['integer','exists:user_site_access,id'],
        ]);

        UserSiteAccess::whereIn('id', $data['ids'])->delete();

        return back()->with('ok', 'Akses terpilih berhasil dihapus.');
    }
}

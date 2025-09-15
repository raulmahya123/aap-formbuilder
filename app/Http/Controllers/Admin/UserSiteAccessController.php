<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSiteAccess;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;

class UserSiteAccessController extends Controller
{
    /**
     * Tampilkan daftar akses + filter + data untuk dropdown.
     * Query:
     * - site_id (opsional untuk filter & prefilling form)
     * - email   (opsional untuk filter & prefilling form)
     */
    public function index(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $siteId = $r->integer('site_id') ?: null;
        $email  = trim((string) $r->get('email', ''));

        $q = UserSiteAccess::query()
            ->with(['user:id,name,email', 'site:id,name,code'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            // karena email dipilih dari dropdown, pakai kecocokan eksak saja
            ->when($email !== '', function ($qq) use ($email) {
                $qq->whereHas('user', function ($u) use ($email) {
                    $u->where('email', '=', $email);
                });
            })
            ->orderBy('id');

        return view('admin.site-access.index', [
            'accesses' => $q->paginate(20)->withQueryString(),
            'sites'    => Site::orderBy('code')->get(['id','name','code']),
            'emails'   => User::orderBy('email')->pluck('email'),
            'siteId'   => $siteId,
            'email'    => $email,
        ]);
    }

    /**
     * Tambahkan akses (site_id, email).
     * Body:
     * - site_id: required|exists:sites,id
     * - email:   required|exists:users,email
     */
    public function store(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'site_id' => ['required','integer','exists:sites,id'],
            'email'   => ['required','email','exists:users,email'],
        ]);

        // Ambil user_id dari email
        $userId = (int) User::where('email', $data['email'])->value('id');

        try {
            // Idempotent: tidak membuat duplikat bila sudah ada
            $access = UserSiteAccess::firstOrCreate(
                ['user_id' => $userId, 'site_id' => (int) $data['site_id']]
            );

            $wasCreated = $access->wasRecentlyCreated;

            return back()->with(
                $wasCreated ? 'ok' : 'info',
                $wasCreated ? 'Akses site ditambahkan.' : 'Data akses sudah ada.'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // fallback jika keburu tabrakan race condition
            $sqlState  = (string) ($e->errorInfo[0] ?? '');
            $driverErr = (int)    ($e->errorInfo[1] ?? 0);

            // MySQL duplicate: 1062, PostgreSQL unique_violation: 23505
            if ($driverErr === 1062 || $sqlState === '23505') {
                return back()->with('info', 'Data akses sudah ada.');
            }

            throw $e;
        }
    }

    /**
     * Hapus akses berdasarkan ID pivot (route model binding).
     * Route: DELETE admin.site_access.destroy
     */
    public function destroy(UserSiteAccess $userSiteAccess)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $userSiteAccess->delete();

        return back()->with('ok', 'Akses site dihapus.');
    }

    /**
     * (Opsional) Cabut akses berdasarkan pasangan (site_id, email).
     * Body/Query:
     * - site_id: required|exists:sites,id
     * - email:   required|exists:users,email
     */
    public function revokeByPair(Request $r)
    {
        $this->authorize('manageSiteAccess', UserSiteAccess::class);

        $data = $r->validate([
            'site_id' => ['required','integer','exists:sites,id'],
            'email'   => ['required','email','exists:users,email'],
        ]);

        $userId = (int) User::where('email', $data['email'])->value('id');

        $deleted = UserSiteAccess::where('site_id', (int) $data['site_id'])
            ->where('user_id', $userId)
            ->delete();

        return back()->with(
            $deleted ? 'ok' : 'info',
            $deleted ? 'Akses site dicabut.' : 'Tidak ada data untuk dicabut.'
        );
    }
}

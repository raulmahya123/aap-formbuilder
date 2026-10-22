<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSiteAccess;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Models\Department;
use App\Models\DepartmentUserRole;

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

        $siteId = \App\Support\SiteSelection::id($r);
        $email  = trim((string) $r->get('email', ''));
        $search = trim((string) $r->get('q', ''));

        $q = UserSiteAccess::query()
            ->with(['user:id,name,email,role,is_active', 'user.departments:id,name', 'site:id,name,code'])
            ->when($search !== '', fn ($query) => $query->whereHas('user', fn ($users) =>
                $users->where(fn ($fields) => $fields->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%'))
            ))
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            // karena email dipilih dari dropdown, pakai kecocokan eksak saja
            ->when($email !== '', function ($qq) use ($email) {
                $qq->whereHas('user', function ($u) use ($email) {
                    $u->where('email', '=', $email);
                });
            })
            ->orderBy('id');

        return view('admin.site-access.index', [
            'accesses' => $q->paginate(\App\Support\Listing::perPage())->withQueryString(),
            'sites'    => Site::orderBy('code')->get(['id','name','code']),
            'emails'   => User::orderBy('email')->pluck('email'),
            'users'    => User::orderBy('name')->get(['id', 'name', 'email', 'role']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
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
            'role' => ['nullable', 'in:user,super_admin'],
            'department_id' => ['nullable', 'required_with:dept_role', 'integer', 'exists:departments,id'],
            'dept_role' => ['nullable', 'required_with:department_id', 'in:member,dept_admin'],
        ]);

        // Ambil user_id dari email
        $userId = (int) User::where('email', $data['email'])->value('id');

        try {
            // Idempotent: tidak membuat duplikat bila sudah ada
            $access = DB::transaction(function () use ($data, $userId, $r) {
                $user = User::whereKey($userId)->lockForUpdate()->firstOrFail();
                if (!empty($data['role']) && $data['role'] !== $user->role) {
                    if ($user->id === $r->user()->id && $data['role'] !== 'super_admin') {
                        throw ValidationException::withMessages(['role' => 'Role akun Anda sendiri tidak dapat diturunkan dari halaman ini.']);
                    }
                    $user->update(['role' => $data['role']]);
                }
                if (!empty($data['department_id']) && !empty($data['dept_role'])) {
                    DepartmentUserRole::updateOrCreate(
                        ['user_id' => $userId, 'department_id' => $data['department_id']],
                        ['dept_role' => $data['dept_role']]
                    );
                }
                return UserSiteAccess::firstOrCreate(['user_id' => $userId, 'site_id' => (int) $data['site_id']]);
            });

            $wasCreated = $access->wasRecentlyCreated;

            if (!empty($data['role']) || !empty($data['dept_role'])) {
                return back()->with('success', 'Penugasan site dan role berhasil disimpan.');
            }
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

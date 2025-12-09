<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IndicatorDaily;
use App\Models\Indicator;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class IndicatorDailyController extends Controller
{
    /**
     * Helper: daftar site_id yang boleh diakses user saat ini.
     * - Admin/Super Admin: null (artinya semua)
     * - User biasa: array of site ids dari relasi pivot `sites()`
     */
    private function allowedSiteIds(?\App\Models\User $user): ?array
    {
        if (!$user) return [];

        // Jika punya helper isAdmin() yang mencakup super_admin
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return null; // semua site
        }

        // Ambil dari relasi pivot
        if (method_exists($user, 'sites')) {
            return $user->sites()->pluck('sites.id')->all();
        }

        return []; // kalau relasi tidak ada, berarti tidak punya akses
    }

    /**
     * User melihat rekap harian.
     * - Admin: semua site
     * - User: hanya site yang dia punya akses
     */
    public function index(Request $request)
    {
        $user           = $request->user();
        $allowedSiteIds = $this->allowedSiteIds($user);

        Log::info('DAILY INDEX: listing data', [
            'user_id'         => optional($user)->id,
            'allowed_site_ids'=> $allowedSiteIds,
            'filters'         => $request->only(['site_id', 'indicator_id', 'date']),
            'url'             => $request->fullUrl(),
            'ip'              => $request->ip(),
        ]);

        $query = IndicatorDaily::query()
            ->with([
                'site:id,name,code',
                'indicator:id,name,code,unit',
            ])
            // Batasi site untuk user biasa
            ->when(is_array($allowedSiteIds), fn($q) =>
                $q->whereIn('site_id', $allowedSiteIds)
            )
            // Filter tambahan dari UI
            ->when($request->filled('site_id'), fn($q) =>
                $q->where('site_id', (int) $request->site_id)
            )
            ->when($request->filled('indicator_id'), fn($q) =>
                $q->where('indicator_id', (int) $request->indicator_id)
            )
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('date', $request->date)
            )
            ->orderByDesc('date')
            ->orderBy('indicator_id');

        // Daftar site untuk dropdown:
        // - Admin: semua site
        // - User: hanya site yang dia punya akses
        $sitesQ = Site::query()
            ->orderBy('code')
            ->select(['id', 'name', 'code']);

        if (is_array($allowedSiteIds)) {
            $sitesQ->whereIn('id', $allowedSiteIds ?: [-1]); // -1 agar hasil kosong jika tidak ada akses
        }

        $rows = $query->paginate(20)->withQueryString();

        Log::info('DAILY INDEX: result summary', [
            'user_id'      => optional($user)->id,
            'rows_count'   => $rows->total(),
            'current_page' => $rows->currentPage(),
        ]);

        return view('user.daily.index', [
            'rows'       => $rows,
            'sites'      => $sitesQ->get(),
            'indicators' => Indicator::orderBy('order_index')
                ->orderBy('id')
                ->get(['id', 'name', 'code', 'unit']),
            'filters'    => $request->only(['site_id', 'indicator_id', 'date']),
        ]);
    }

    /**
     * Simpan input baru / update existing untuk site yang user punya akses.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'      => ['required', 'exists:sites,id'],
            'indicator_id' => ['required', 'exists:indicators,id'],
            'date'         => ['required', 'date'],
            'value'        => ['required', 'numeric'],
            'note'         => ['nullable', 'string'],
        ]);

        Log::info('DAILY STORE: incoming request', [
            'user_id' => optional($request->user())->id,
            'payload' => $data,
            'raw'     => $request->all(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
        ]);

        // Otorisasi per-site:
        // Pakai ability daily.manage (kalau ada), fallback ke site-access.
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', (int) $data['site_id']);
        } else {
            Gate::authorize('site-access',  (int) $data['site_id']);
        }

        $row = IndicatorDaily::updateOrCreate(
            [
                'site_id'      => $data['site_id'],
                'indicator_id' => $data['indicator_id'],
                'date'         => $data['date'],
            ],
            [
                'value' => $data['value'],
                'note'  => $data['note'] ?? null,
            ]
        );

        Log::info('DAILY STORE: row saved/updated', [
            'row_id'        => $row->id,
            'site_id'       => $row->site_id,
            'indicator_id'  => $row->indicator_id,
            'date'          => $row->date,
            'value'         => $row->value,
            'note'          => $row->note,
        ]);

        return redirect()
            ->route('daily.index') // <— sesuai route di web.php
            ->with('ok', "Data harian untuk {$row->indicator->name} tersimpan.");
    }

    /**
     * Update data existing.
     * - Semua user: bisa ubah value + note
     * - HANYA super_admin: boleh ubah indicator_id
     */
    public function update(Request $request, IndicatorDaily $daily)
    {
        $user = $request->user();

        Log::info('DAILY UPDATE: called', [
            'user_id' => optional($user)->id,
            'daily_id'=> $daily->id,
            'before'  => $daily->toArray(),
            'raw'     => $request->all(),
            'url'     => $request->fullUrl(),
            'ip'      => $request->ip(),
        ]);

        // Otorisasi per-site (admin lolos; user harus punya akses ke site terkait)
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', (int) $daily->site_id);
        } else {
            Gate::authorize('site-access',  (int) $daily->site_id);
        }

        // Rules default (untuk semua user)
        $rules = [
            'value' => ['required', 'numeric'],
            'note'  => ['nullable', 'string'],
        ];

        // HANYA super_admin yang boleh ganti indicator_id
        // Sesuaikan cek ini dengan method/role yang kamu pakai di User model
        $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();

        if ($isSuperAdmin) {
            $rules['indicator_id'] = ['required', 'exists:indicators,id'];
        }

        $data = $request->validate($rules);

        // Kalau BUKAN super_admin, pastikan indicator_id TIDAK ikut di-update
        if (!$isSuperAdmin) {
            unset($data['indicator_id']);
        }

        Log::info('DAILY UPDATE: payload after validation', [
            'daily_id'      => $daily->id,
            'is_super_admin'=> $isSuperAdmin,
            'update_data'   => $data,
        ]);

        $daily->update($data);
        $daily->refresh();

        Log::info('DAILY UPDATE: after update', [
            'daily_id' => $daily->id,
            'after'    => $daily->toArray(),
        ]);

        return redirect()
            ->route('daily.index') // <— sesuai route di web.php
            ->with('ok', "Data harian diperbarui.");
    }

    /**
     * Hapus data harian.
     */
    public function destroy(IndicatorDaily $daily, Request $request)
    {
        $user = $request->user();

        Log::info('DAILY DESTROY: called', [
            'user_id'  => optional($user)->id,
            'daily_id' => $daily->id,
            'row'      => $daily->toArray(),
            'url'      => $request->fullUrl(),
            'ip'       => $request->ip(),
        ]);

        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', (int) $daily->site_id);
        } else {
            Gate::authorize('site-access',  (int) $daily->site_id);
        }

        $daily->delete();

        Log::info('DAILY DESTROY: deleted', [
            'user_id'  => optional($user)->id,
            'daily_id' => $daily->id,
        ]);

        return redirect()
            ->route('daily.index') // <— sesuai route di web.php
            ->with('ok', "Data harian dihapus.");
    }
}

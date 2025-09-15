<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IndicatorDaily;
use App\Models\Indicator;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        $sitesQ = Site::query()->orderBy('code')->select(['id','name','code']);
        if (is_array($allowedSiteIds)) {
            $sitesQ->whereIn('id', $allowedSiteIds ?: [-1]); // -1 agar hasil kosong jika tidak ada akses
        }

        return view('user.daily.index', [
            'rows'       => $query->paginate(20)->withQueryString(),
            'sites'      => $sitesQ->get(),
            'indicators' => Indicator::orderBy('order_index')->orderBy('id')->get(['id','name','code','unit']),
            'filters'    => $request->only(['site_id','indicator_id','date']),
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

        return redirect()
            ->route('user.daily.index')
            ->with('ok', "Data harian untuk {$row->indicator->name} tersimpan.");
    }

    /**
     * Update data existing.
     */
    public function update(Request $request, IndicatorDaily $daily)
    {
        // Otorisasi per-site (admin lolos; user harus punya akses ke site terkait)
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', (int) $daily->site_id);
        } else {
            Gate::authorize('site-access',  (int) $daily->site_id);
        }

        $data = $request->validate([
            'value' => ['required', 'numeric'],
            'note'  => ['nullable', 'string'],
        ]);

        $daily->update($data);

        return redirect()
            ->route('user.daily.index')
            ->with('ok', "Data harian diperbarui.");
    }

    /**
     * Hapus data harian.
     */
    public function destroy(IndicatorDaily $daily)
    {
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', (int) $daily->site_id);
        } else {
            Gate::authorize('site-access',  (int) $daily->site_id);
        }

        $daily->delete();

        return redirect()
            ->route('user.daily.index')
            ->with('ok', "Data harian dihapus.");
    }
}

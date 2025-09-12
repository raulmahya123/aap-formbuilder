<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\IndicatorDaily;
use App\Models\Indicator;
use App\Models\Site;
use Illuminate\Http\Request;

class IndicatorDailyController extends Controller
{
    /**
     * Semua user bisa melihat rekap harian lintas site (read-only).
     * Filtering tetap disediakan.
     */
    public function index(Request $request)
    {
        $query = IndicatorDaily::query()
            ->with([
                'site:id,name,code',
                'indicator:id,name,code,unit',
            ])
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

        return view('user.daily.index', [
            'rows'       => $query->paginate(20)->withQueryString(),
            'sites'      => Site::orderBy('code')->get(['id','name','code']),
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

        // Policy: hanya boleh create di site yg user punya akses
        $this->authorize('createForSite', [IndicatorDaily::class, (int) $data['site_id']]);

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
        $this->authorize('update', $daily);

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
     * Hapus data harian (hanya admin / sesuai policy).
     */
    public function destroy(IndicatorDaily $daily)
    {
        $this->authorize('delete', $daily);

        $daily->delete();

        return redirect()
            ->route('user.daily.index')
            ->with('ok', "Data harian dihapus.");
    }
}

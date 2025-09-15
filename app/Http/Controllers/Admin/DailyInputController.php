<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyRequest;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DailyInputController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // opsional: kalau mau benar2 admin-only untuk halaman ini, aktifkan baris di bawah
        // $this->middleware('can:is-admin')->only(['index','create']);
    }

    /** helper: daftar site_id yang boleh untuk user (null = semua) */
    // app/Http/Controllers/Admin/DailyInputController.php

    /** helper: daftar site_id yang boleh untuk user
     *  - Super Admin : null  (semua site)
     *  - Admin/User  : array (hanya site yang di-assign)
     */
    private function allowedSiteIds($user): ?array
    {
        if (!$user) return [];

        // HANYA super admin yang dapat semua site
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return null; // semua site
        }

        // Admin biasa & user: harus punya akses (pivot user_site_access)
        if (method_exists($user, 'sites')) {
            return $user->sites()->pluck('sites.id')->all();
        }

        return []; // tidak ada akses
    }


    public function create(Request $r)
    {
        $allowed = $this->allowedSiteIds($r->user());

        $sitesQ = Site::query()->orderBy('code');
        if (is_array($allowed)) {
            // jika tidak ada akses sama sekali, kosongkan daftar sites
            $sitesQ->whereIn('id', $allowed ?: [-1]);
        }
        $sites = $sitesQ->get(['id', 'code', 'name']);

        $date  = $r->input('date', now()->toDateString());

        $groups = IndicatorGroup::with(['indicators' => function ($q) {
            $q->where('is_active', true)->orderBy('order_index');
        }])->where('is_active', true)->orderBy('order_index')->get();

        return view('admin.daily.create', compact('sites', 'date', 'groups'));
    }

    public function store(StoreDailyRequest $r)
    {
        $siteId = (int) $r->site_id;

        // Pastikan otorisasi per-site (admin auto lolos; user biasa harus punya akses ke site)
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', $siteId);
        } else {
            Gate::authorize('site-access',  $siteId);
        }

        $date   = Carbon::parse($r->date)->toDateString();
        $values = $r->values ?? [];
        $notes  = $r->notes ?? [];

        DB::transaction(function () use ($siteId, $date, $values, $notes) {
            foreach ($values as $indicatorId => $val) {
                if ($val === null || $val === '') continue;

                IndicatorDaily::updateOrCreate(
                    ['site_id' => $siteId, 'indicator_id' => $indicatorId, 'date' => $date],
                    ['value' => $val, 'note' => $notes[$indicatorId] ?? null]
                );
            }
        });

        return back()->with('ok', 'Data harian tersimpan.');
    }

    // List per hari (dibatasi sesuai akses)
    public function index(Request $r)
    {
        $allowed = $this->allowedSiteIds($r->user());

        $siteId = $r->integer('site_id');
        $month  = (int) $r->input('month', now()->month);
        $year   = (int) $r->input('year',  now()->year);

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end   = (clone $start)->endOfMonth();

        $sitesQ = Site::query()->orderBy('code');
        if (is_array($allowed)) {
            $sitesQ->whereIn('id', $allowed ?: [-1]);
        }
        $sites = $sitesQ->get(['id', 'code', 'name']);

        $rowsQ = IndicatorDaily::with(['indicator', 'site'])
            ->whereBetween('date', [$start, $end])
            ->orderBy('date');

        // batasi sesuai akses
        if (is_array($allowed)) {
            $rowsQ->whereIn('site_id', $allowed ?: [-1]);
        }

        // filter UI
        if ($siteId) $rowsQ->where('site_id', $siteId);

        $rows = $rowsQ->paginate(50)->withQueryString();

        return view('admin.daily.index', compact('rows', 'sites', 'siteId', 'month', 'year'));
    }
}

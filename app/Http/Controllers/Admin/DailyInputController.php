<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyRequest;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use App\Support\ShiftWindow; // 👈 tambahkan
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DailyInputController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // $this->middleware('can:is-admin')->only(['index','create']);
    }

    /** helper: daftar site_id yang boleh untuk user (null = semua) */
    private function allowedSiteIds($user): ?array
    {
        if (!$user) return [];

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return null; // semua site
        }

        if (method_exists($user, 'sites')) {
            return $user->sites()->pluck('sites.id')->all();
        }

        return [];
    }

    public function create(Request $r)
    {
        $allowed = $this->allowedSiteIds($r->user());

        $sitesQ = Site::query()->orderBy('code');
        if (is_array($allowed)) {
            $sitesQ->whereIn('id', $allowed ?: [-1]);
        }
        $sites = $sitesQ->get(['id', 'code', 'name']);

        $date  = $r->input('date', now(config('shifts.timezone', 'Asia/Jakarta'))->toDateString());

        $groups = IndicatorGroup::with(['indicators' => function ($q) {
            $q->where('is_active', true)->orderBy('order_index');
        }])->where('is_active', true)->orderBy('order_index')->get();

        // 👇 auto-deteksi shift & flag telat (untuk watermark di view)
        $shiftInfo = ShiftWindow::detect($date);

        return view('admin.daily.create', [
            'sites' => $sites,
            'date'  => $date,
            'groups' => $groups,
            'showLateWatermark' => $shiftInfo['is_late'],
            'currentShift'      => $shiftInfo['shift'],
            'shiftRanges'       => $shiftInfo['ranges'],
        ]);
    }

    public function store(StoreDailyRequest $r)
    {
        $siteId = (int) $r->site_id;

        // Otorisasi
        if (Gate::has('daily.manage')) {
            Gate::authorize('daily.manage', $siteId);
        } else {
            Gate::authorize('site-access',  $siteId);
        }

        $date   = Carbon::parse($r->date, config('shifts.timezone', 'Asia/Jakarta'))->toDateString();
        $values = $r->values ?? [];
        $notes  = $r->notes ?? [];

        // 👇 deteksi sekali (tanpa user milih shift/waktu)
        // app/Http/Controllers/Admin/DailyInputController.php  (ubah bagian store)
        $shiftInfo     = ShiftWindow::detect($date);
        $currentShift  = $shiftInfo['shift'] ?? $shiftInfo['closest_shift']; // << pakai fallback
        $inputAt       = $shiftInfo['now'];
        $isLate        = $shiftInfo['is_late'];

        DB::transaction(function () use ($siteId, $date, $values, $notes, $currentShift, $inputAt, $isLate) {
            foreach ($values as $indicatorId => $val) {
                if ($val === null || $val === '') continue;

                $delta = (float) $val;

                $row = IndicatorDaily::where([
                    'site_id'      => $siteId,
                    'indicator_id' => $indicatorId,
                    'date'         => $date,
                ])->lockForUpdate()->first();

                if ($row) {
                    // akumulasi nilai & update note
                    $row->value = (float) $row->value + $delta;
                    if (!empty($notes[$indicatorId])) {
                        $row->note = trim(($row->note ? $row->note . "\n" : '') . $notes[$indicatorId]);
                    }
                    // 👇 update metadata shift setiap input
                    $row->shift    = $currentShift;
                    $row->input_at = $inputAt;
                    $row->is_late  = $isLate;
                    $row->save();
                } else {
                    IndicatorDaily::create([
                        'site_id'      => $siteId,
                        'indicator_id' => $indicatorId,
                        'date'         => $date,
                        'value'        => $delta,
                        'note'         => $notes[$indicatorId] ?? null,
                        // 👇 auto set tanpa input user
                        'shift'        => $currentShift,
                        'input_at'     => $inputAt,
                        'is_late'      => $isLate,
                    ]);
                }
            }
        });

        return back()
            ->with('ok', 'Data harian diakumulasi.')
            ->with('late', $isLate); // opsional: untuk banner/watermark
    }

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

        if (is_array($allowed)) {
            $rowsQ->whereIn('site_id', $allowed ?: [-1]);
        }

        if ($siteId) $rowsQ->where('site_id', $siteId);

        $rows = $rowsQ->paginate(50)->withQueryString();

        return view('admin.daily.index', compact('rows', 'sites', 'siteId', 'month', 'year'));
    }
}

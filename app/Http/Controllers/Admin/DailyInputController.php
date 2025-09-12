<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDailyRequest;
use App\Models\Indicator;
use App\Models\IndicatorDaily;
use App\Models\IndicatorGroup;
use App\Models\Site;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyInputController extends Controller
{
    public function __construct() { $this->middleware(['auth']); }

    public function create(Request $r)
    {
        $sites = Site::orderBy('code')->get();
        $date  = $r->input('date', now()->toDateString());

        $groups = IndicatorGroup::with(['indicators' => function($q){
            $q->where('is_active',true)->orderBy('order_index');
        }])->where('is_active',true)->orderBy('order_index')->get();

        return view('admin.daily.create', compact('sites','date','groups'));
    }

    public function store(StoreDailyRequest $r)
    {
        $siteId = (int) $r->site_id;
        $date   = Carbon::parse($r->date)->toDateString();
        $values = $r->values ?? [];
        $notes  = $r->notes ?? [];

        DB::transaction(function () use ($siteId,$date,$values,$notes) {
            foreach ($values as $indicatorId => $val) {
                if ($val === null || $val === '') continue;

                IndicatorDaily::updateOrCreate(
                    ['site_id'=>$siteId,'indicator_id'=>$indicatorId,'date'=>$date],
                    ['value'=>$val, 'note'=>$notes[$indicatorId] ?? null]
                );
            }
        });

        return back()->with('ok','Data harian tersimpan.');
    }

    // List per hari (opsional)
    public function index(Request $r)
    {
        $siteId = $r->integer('site_id');
        $month  = (int) $r->input('month', now()->month);
        $year   = (int) $r->input('year', now()->year);

        $start = Carbon::create($year,$month,1)->startOfDay();
        $end   = (clone $start)->endOfMonth();

        $sites = Site::orderBy('code')->get();

        $rows = IndicatorDaily::with(['indicator','site'])
            ->when($siteId, fn($q)=>$q->where('site_id',$siteId))
            ->whereBetween('date', [$start,$end])
            ->orderBy('date')
            ->paginate(50);

        return view('admin.daily.index', compact('rows','sites','siteId','month','year'));
    }
}

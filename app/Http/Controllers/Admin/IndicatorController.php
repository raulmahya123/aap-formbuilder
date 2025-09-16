<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\IndicatorGroup;
use Illuminate\Http\Request;

class IndicatorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index()
    {
        $groups = IndicatorGroup::query()
            ->with(['indicators' => fn($q) => $q->orderBy('order_index')->orderBy('id')]) // urutkan indikator
            ->orderBy('order_index')
            ->get();

        return view('admin.indicators.index', compact('groups'));
    }

    public function create()
    {
        $groups = IndicatorGroup::orderBy('order_index')->get();
        return view('admin.indicators.create', compact('groups'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'indicator_group_id' => 'required|exists:indicator_groups,id',
            'name'               => 'required|string',
            'code'               => 'required|alpha_dash|unique:indicators,code',
            'data_type'          => 'required|in:int,decimal,currency,rate',
            'agg'                => 'required|in:sum,avg,max,min',
            'unit'               => 'nullable|string|max:50',
            'order_index'        => 'nullable|integer',
            'is_derived'         => 'sometimes|boolean',
            'formula'            => 'nullable|string',
            'threshold'          => 'nullable|string|max:100',
        ]);

        // Normalisasi & default
        $data['code']        = strtoupper(trim($data['code']));
        $data['is_derived']  = $r->boolean('is_derived');           // pastikan boolean
        $data['order_index'] = (int)($data['order_index'] ?? 0);    // default 0
        $data['threshold']   = isset($data['threshold']) ? trim($data['threshold']) : null;

        Indicator::create($data);

        return redirect()
            ->route('admin.indicators.index')
            ->with('ok', 'Indicator created');
    }

    public function edit(Indicator $indicator)
    {
        $groups = IndicatorGroup::orderBy('order_index')->get();
        return view('admin.indicators.edit', compact('indicator', 'groups'));
    }

    public function update(Request $r, Indicator $indicator)
    {
        $data = $r->validate([
            'indicator_group_id' => 'required|exists:indicator_groups,id',
            'name'               => 'required|string',
            'code'               => "required|alpha_dash|unique:indicators,code,{$indicator->id}",
            'data_type'          => 'required|in:int,decimal,currency,rate',
            'agg'                => 'required|in:sum,avg,max,min',
            'unit'               => 'nullable|string|max:50',
            'order_index'        => 'nullable|integer',
            'is_derived'         => 'sometimes|boolean',
            'formula'            => 'nullable|string',
            'threshold'          => 'nullable|string|max:100',
        ]);

        // Normalisasi & default
        $data['code']        = strtoupper(trim($data['code']));
        $data['is_derived']  = $r->boolean('is_derived');           // pastikan boolean
        $data['order_index'] = (int)($data['order_index'] ?? 0);
        $data['threshold']   = isset($data['threshold']) ? trim($data['threshold']) : null;

        $indicator->update($data);

        return redirect()
            ->route('admin.indicators.index')
            ->with('ok', 'Indicator updated');
    }

    public function destroy(Indicator $indicator)
    {
        $indicator->delete();
        return back()->with('ok', 'Indicator deleted');
    }
}

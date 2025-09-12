<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Indicator;
use App\Models\IndicatorGroup;
use Illuminate\Http\Request;

class IndicatorController extends Controller
{
    public function __construct() { $this->middleware(['auth']); }

    public function index() {
        $groups = IndicatorGroup::with('indicators')->orderBy('order_index')->get();
        return view('admin.indicators.index', compact('groups'));
    }

    public function create() {
        $groups = IndicatorGroup::orderBy('order_index')->get();
        return view('admin.indicators.create', compact('groups'));
    }

    public function store(Request $r) {
        $data = $r->validate([
            'indicator_group_id'=>'required|exists:indicator_groups,id',
            'name'=>'required','code'=>'required|alpha_dash|unique:indicators,code',
            'data_type'=>'required|in:int,decimal,currency,rate',
            'agg'=>'required|in:sum,avg,max,min',
            'unit'=>'nullable|max:50',
            'order_index'=>'nullable|integer',
            'is_derived'=>'boolean',
            'formula'=>'nullable|string'
        ]);
        Indicator::create($data);
        return redirect()->route('admin.indicators.index')->with('ok','Indicator created');
    }

    public function edit(Indicator $indicator) {
        $groups = IndicatorGroup::orderBy('order_index')->get();
        return view('admin.indicators.edit', compact('indicator','groups'));
    }

    public function update(Request $r, Indicator $indicator) {
        $data = $r->validate([
            'indicator_group_id'=>'required|exists:indicator_groups,id',
            'name'=>'required',
            'code'=>"required|alpha_dash|unique:indicators,code,{$indicator->id}",
            'data_type'=>'required|in:int,decimal,currency,rate',
            'agg'=>'required|in:sum,avg,max,min',
            'unit'=>'nullable|max:50',
            'order_index'=>'nullable|integer',
            'is_derived'=>'boolean',
            'formula'=>'nullable|string'
        ]);
        $indicator->update($data);
        return redirect()->route('admin.indicators.index')->with('ok','Indicator updated');
    }

    public function destroy(Indicator $indicator) {
        $indicator->delete();
        return back()->with('ok','Indicator deleted');
    }
}

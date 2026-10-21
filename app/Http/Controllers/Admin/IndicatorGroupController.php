<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IndicatorGroup;
use Illuminate\Http\Request;

class IndicatorGroupController extends Controller
{
    public function __construct() { $this->middleware(['auth']); }

    public function index(Request $request) {
        $groups = IndicatorGroup::query()->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->string('q').'%')->orWhere('code', 'like', '%'.$request->string('q').'%')))->orderBy('order_index')->paginate(\App\Support\Listing::perPage())->withQueryString();
        return view('admin.indicator-groups.index', compact('groups'));
    }

    public function create() { return view('admin.indicator-groups.form', ['group' => new IndicatorGroup]); }

    public function store(Request $r) {
        $data = $r->validate([
            'name'=>'required','code'=>'required|alpha_dash|unique:indicator_groups,code',
            'order_index'=>'nullable|integer','is_active'=>'boolean'
        ]);
        IndicatorGroup::create($data);
        return redirect()->route('admin.groups.index')->with('ok','Group created');
    }

    public function edit(IndicatorGroup $group) { return view('admin.indicator-groups.edit', compact('group')); }

    public function update(Request $r, IndicatorGroup $group) {
        $data = $r->validate([
            'name'=>'required','code'=>"required|alpha_dash|unique:indicator_groups,code,{$group->id}",
            'order_index'=>'nullable|integer','is_active'=>'boolean'
        ]);
        $group->update($data);
        return redirect()->route('admin.groups.index')->with('ok','Group updated');
    }

    public function destroy(IndicatorGroup $group) {
        $group->delete();
        return back()->with('ok','Group deleted');
    }
}

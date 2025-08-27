<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DepartmentController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Department::class);
        $departments = Department::orderBy('name')->paginate(20);
        return view('admin.departments.index', compact('departments'));
    }

    public function create()
    {
        $this->authorize('create', Department::class);
        return view('admin.departments.create');
    }

    public function store(Request $r)
    {
        $this->authorize('create', Department::class);
        $validated = $r->validate([
            'name' => ['required','string','max:150','unique:departments,name'],
        ]);
        Department::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);
        return redirect()->route('admin.departments.index')->with('ok','Department dibuat');
    }

    public function edit(Department $department)
    {
        $this->authorize('update', $department);
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $r, Department $department)
    {
        $this->authorize('update', $department);
        $validated = $r->validate([
            'name' => ['required','string','max:150',"unique:departments,name,{$department->id}"],
        ]);
        $department->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);
        return redirect()->route('admin.departments.index')->with('ok','Department diupdate');
    }

    public function destroy(Department $department)
    {
        $this->authorize('delete', $department);
        $department->delete();
        return back()->with('ok','Department dihapus');
    }

    public function show(Department $department)
    {
        $this->authorize('view', $department);
        return redirect()->route('admin.departments.members', $department);
    }
}

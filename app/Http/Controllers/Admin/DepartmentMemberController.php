<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Department, DepartmentUserRole, User};
use Illuminate\Http\Request;

class DepartmentMemberController extends Controller
{
    public function index(Request $request, Department $department)
    {
        $this->authorize('update', $department); // optional kalau pakai DepartmentPolicy
        $members = $department->users()->withPivot('dept_role')->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('users.name','like','%'.$request->string('q').'%')->orWhere('users.email','like','%'.$request->string('q').'%')))->paginate(\App\Support\Listing::perPage())->withQueryString();
        $users   = User::orderBy('name')->get();
        return view('admin.departments.members', compact('department','members','users'));
    }

    public function store(Request $r, Department $department)
    {
        $this->authorize('update', $department);
        $r->validate([
            'user_id' => ['required','exists:users,id'],
            'dept_role' => ['required','in:dept_admin,member'],
        ]);
        DepartmentUserRole::updateOrCreate(
            ['department_id'=>$department->id, 'user_id'=>$r->user_id],
            ['dept_role'=>$r->dept_role]
        );
        return back()->with('ok','Akses diperbarui');
    }

    public function destroy(Department $department, User $user)
    {
        $this->authorize('update', $department);
        DepartmentUserRole::where('department_id',$department->id)->where('user_id',$user->id)->delete();
        return back()->with('ok','Akses dihapus');
    }
}

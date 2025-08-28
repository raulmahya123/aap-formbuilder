<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserActiveController extends Controller
{
    public function __construct()
    {
        // Middleware: hanya super admin yang boleh masuk
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
                abort(403, 'Hanya Super Admin yang bisa mengakses menu ini.');
            }
            return $next($request);
        });
    }

    /**
     * Tampilkan daftar user beserta status aktifnya
     */
    public function index()
    {
        $users = User::orderBy('name')->paginate(20);
        return view('admin.users.active', compact('users'));
    }

    /**
     * Toggle aktif/nonaktif user
     */
    public function toggle(User $user)
    {
        // jangan izinkan super admin menonaktifkan dirinya sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    /**
     * Update status aktif secara eksplisit
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $user->is_active = $request->boolean('is_active');
        $user->save();

        return back()->with('success', 'Status user berhasil diubah.');
    }
}

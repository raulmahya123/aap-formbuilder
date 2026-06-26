<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

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

        // cegah menonaktifkan super admin mana pun
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return back()->with('error', 'Tidak bisa menonaktifkan Super Admin.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status user berhasil diperbarui.');
    }

    /**
     * Reset password user dari halaman Super Admin.
     */
    public function resetPassword(Request $request, User $user)
    {
        if ($user->id !== Auth::id() && method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return back()->with('error', 'Tidak bisa reset password Super Admin lain.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.min' => 'Password minimal :min karakter.',
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('success', "Password {$user->name} berhasil direset.");
    }

    /**
     * Update status aktif secara eksplisit
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        // cegah self-deactivate & nonaktifkan super admin
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun Anda sendiri.');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return back()->with('error', 'Tidak bisa menonaktifkan Super Admin.');
        }

        $user->is_active = $request->boolean('is_active');
        $user->save();

        return back()->with('success', 'Status user berhasil diubah.');
    }
}

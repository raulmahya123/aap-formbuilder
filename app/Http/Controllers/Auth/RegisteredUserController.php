<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses register user baru.
     * Catatan: user BARU TIDAK aktif & TIDAK otomatis login.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'is_active'  => false,          // <-- penting: belum aktif
            'role'    => 'user',          // (opsional) set role default
        ]);

        // Jika kamu pakai verifikasi email, event ini tetap biar kirim notifikasi/verify
        event(new Registered($user));

        // JANGAN login otomatis
        // Auth::login($user);

        // Arahkan ke halaman login (atau halaman informasi) dengan pesan
        return redirect()->route('login')->with(
            'ok',
            'Registrasi berhasil. Akun Anda menunggu persetujuan admin.'
        );
    }
}

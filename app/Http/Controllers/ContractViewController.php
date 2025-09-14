<?php

// app/Http/Controllers/ContractViewController.php (untuk user/guest)
namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ContractViewController extends Controller
{
  // GET /c/{slug}
  public function show(Request $r, string $slug)
  {
    $contract = Contract::where('slug',$slug)->firstOrFail();

    // Kalau belum lolos policy dan belum punya email session, tampilkan form minta email
    if (!Gate::forUser($r->user())->allows('view', $contract)) {
      if (!$r->session()->has('access_email')) {
        return view('contracts.email-gate', compact('contract'));
      }
      // sudah ada email di session tapi tetap tidak boleh → 403
      abort(403, 'Email Anda belum di-whitelist untuk kontrak ini.');
    }

    return view('contracts.show', compact('contract'));
  }

  // POST /c/{slug}/access
  public function gate(Request $r, string $slug)
  {
    $contract = Contract::where('slug',$slug)->firstOrFail();
    $data = $r->validate(['email' => ['required','email:rfc,dns']]);
    $r->session()->put('access_email', strtolower($data['email']));

    // cek lagi policy setelah simpan email
    if (!app('Illuminate\Contracts\Auth\Access\Gate')->forUser($r->user())->allows('view', $contract)) {
      return back()->withErrors(['email' => 'Email belum diizinkan. Hubungi admin.']);
    }
    return redirect()->route('contracts.show', $contract->slug);
  }
}

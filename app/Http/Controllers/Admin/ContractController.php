<?php

// app/Http/Controllers/Admin/ContractController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Contract, ContractAccess};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ContractController extends Controller
{
  public function index(Request $r) {
    $q = Contract::query()->withCount('accesses')->latest();
    if ($r->filled('site_id')) $q->where('site_id', $r->integer('site_id'));
    return view('admin.contracts.index', ['contracts' => $q->paginate(15)]);
  }

  public function create() { return view('admin.contracts.create'); }

  public function store(Request $r) {
    $data = $r->validate([
      'title'       => ['required','string','max:200'],
      'description' => ['nullable','string'],
      'visibility'  => ['required', Rule::in(['whitelist','link','private'])],
      'site_id'     => ['nullable','exists:sites,id'],
      'expires_at'  => ['nullable','date'],
      'images.*'    => ['required','image','mimes:jpg,jpeg,png','max:4096'],
    ]);

    $paths = [];
    foreach ($r->file('images', []) as $file) {
      $paths[] = $file->store('contracts/'.date('Y/m'), 'public');
    }

    $contract = Contract::create([
      ...$data,
      'images'     => $paths,
      'created_by' => $r->user()->id,
    ]);

    return redirect()->route('admin.contracts.edit', $contract)->with('ok','Kontrak dibuat.');
  }

  public function edit(Contract $contract) {
    $contract->load('accesses');
    return view('admin.contracts.edit', compact('contract'));
  }

  public function update(Request $r, Contract $contract) {
    $data = $r->validate([
      'title'       => ['required','string','max:200'],
      'description' => ['nullable','string'],
      'visibility'  => ['required', Rule::in(['whitelist','link','private'])],
      'site_id'     => ['nullable','exists:sites,id'],
      'expires_at'  => ['nullable','date'],
      'images.*'    => ['nullable','image','mimes:jpg,jpeg,png','max:4096'],
      'remove'      => ['array'], // index gambar yang dihapus
    ]);

    // remove selected
    $imgs = collect($contract->images);
    foreach ($data['remove'] ?? [] as $i) {
      if (isset($imgs[$i])) {
        Storage::disk('public')->delete($imgs[$i]);
        $imgs->forget($i);
      }
    }
    // add new
    foreach ($r->file('images', []) as $file) {
      $imgs->push($file->store('contracts/'.date('Y/m'), 'public'));
    }

    $contract->update([
      ...collect($data)->except(['images','remove'])->all(),
      'images' => array_values($imgs->all()),
    ]);

    return back()->with('ok','Kontrak diperbarui.');
  }

  public function destroy(Contract $contract) {
    foreach ($contract->images as $p) Storage::disk('public')->delete($p);
    $contract->delete();
    return redirect()->route('admin.contracts.index')->with('ok','Kontrak dihapus.');
  }

  // === ACCESS MANAGEMENT ===
  public function accessStore(Request $r, Contract $contract) {
    $this->authorize('manage', $contract);
    $data = $r->validate([
      'email'  => ['required','email:rfc,dns'],
      'status' => ['required', Rule::in(['approved','blocked','pending'])],
    ]);
    ContractAccess::updateOrCreate(
      ['contract_id' => $contract->id, 'email' => strtolower($data['email'])],
      ['status' => $data['status']]
    );
    return back()->with('ok','Whitelist diperbarui.');
  }

  public function accessDestroy(Contract $contract, ContractAccess $access) {
    $this->authorize('manage', $contract);
    abort_unless($access->contract_id === $contract->id, 404);
    $access->delete();
    return back()->with('ok','Email dihapus dari whitelist.');
  }

  // CSV import (kolom: email,status)
  public function accessImport(Request $r, Contract $contract) {
    $this->authorize('manage', $contract);
    $r->validate(['file' => ['required','file','mimes:csv,txt','max:1024']]);
    $rows = array_map('str_getcsv', file($r->file('file')->getRealPath()));
    foreach ($rows as [$email, $status]) {
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) continue;
      $status = in_array($status, ['approved','blocked','pending']) ? $status : 'approved';
      ContractAccess::updateOrCreate(
        ['contract_id'=>$contract->id, 'email'=>strtolower($email)],
        ['status'=>$status]
      );
    }
    return back()->with('ok','Import whitelist selesai.');
  }
}

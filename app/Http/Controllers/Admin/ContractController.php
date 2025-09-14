<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Contract, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ContractController extends Controller
{
    /**
     * Daftar kontrak yang dimiliki user atau dibagikan ke user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $contracts = Contract::query()
            ->with(['owner:id,name,email'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%');
            })
            ->where(function ($w) use ($user) {
                $w->where('owner_id', $user->id)
                  ->orWhereIn('id', function ($sub) use ($user) {
                      $sub->select('contract_id')
                          ->from('contract_acls')
                          ->where('user_id', $user->id);
                  });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contracts.index', compact('contracts'));
    }

    /**
     * Form upload kontrak.
     */
    public function create()
{
    $users = User::select('id','name','email')
        ->orderBy('name')
        ->get();

    return view('admin.contracts.create', compact('users'));
}
    /**
     * Simpan kontrak + set ACL awal (optional: by emails).
     */
    public function store(Request $request)
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'file'       => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB
            'emails'     => ['nullable'], // bisa array atau string "a@b.com, c@d.com"
            'emails.*'   => ['sometimes', 'email'],
        ]);

        // Normalisasi emails: dukung array atau string dipisah koma
        $emails = [];
        if ($request->has('emails')) {
            if (is_array($request->emails)) {
                $emails = array_filter(array_map('trim', $request->emails));
            } elseif (is_string($request->emails)) {
                $emails = array_filter(array_map('trim', explode(',', $request->emails)));
            }
            $emails = array_values(array_unique($emails));
        }

        // Simpan file ke disk private
        $file = $request->file('file');
        $path = $file->store('contracts', 'private');

        $contract = Contract::create([
            'owner_id'   => $request->user()->id,
            'title'      => $data['title'],
            'file_path'  => $path,
            'size_bytes' => $file->getSize(),
            'mime'       => $file->getMimeType() ?: 'application/pdf',
        ]);

        // Set ACL awal: hanya user terdaftar
        if (!empty($emails)) {
            $userIds = User::whereIn('email', $emails)->pluck('id')->all();
            if ($userIds) {
                $contract->viewers()->syncWithPivotValues($userIds, ['perm' => 'view'], false);
            }
        }

        return redirect()
            ->route('admin.contracts.show', $contract)
            ->with('ok', 'Kontrak diunggah & akses (viewer) diset.');
    }

    /**
     * Detail kontrak + daftar viewers.
     */
    public function show(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $contract->load(['owner:id,name,email', 'viewers:id,name,email']);

        return view('admin.contracts.show', compact('contract'));
    }

    /**
     * Download file PDF dari disk private (cek policy view).
     */
    public function download(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $downloadName = Str::of($contract->title)->slug('_') . '.pdf';

        return Storage::disk('private')->download($contract->file_path, $downloadName);
    }

    /**
     * Tambah viewers berdasarkan email (hanya owner melalui policy 'share').
     */
    public function share(Request $request, Contract $contract)
    {
        $this->authorize('share', $contract);

        $data = $request->validate([
            'emails'   => ['required'], // array atau string koma
            'emails.*' => ['sometimes', 'email'],
        ]);

        // Normalisasi emails
        $emails = [];
        if (is_array($request->emails)) {
            $emails = array_filter(array_map('trim', $request->emails));
        } elseif (is_string($request->emails)) {
            $emails = array_filter(array_map('trim', explode(',', $request->emails)));
        }
        $emails = array_values(array_unique($emails));

        if (empty($emails)) {
            return back()->with('err', 'Tidak ada email yang valid.');
        }

        $userIds = User::whereIn('email', $emails)->pluck('id')->all();
        if (empty($userIds)) {
            return back()->with('err', 'Tidak ada user terdaftar yang cocok dengan email tersebut.');
        }

        $contract->viewers()->syncWithPivotValues($userIds, ['perm' => 'view'], false);

        return back()->with('ok', 'Akses viewer ditambahkan/diperbarui.');
    }

    /**
     * Cabut akses viewer tertentu (hanya owner).
     */
    public function revoke(Request $request, Contract $contract)
    {
        $this->authorize('share', $contract);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $contract->viewers()->detach($data['user_id']);

        return back()->with('ok', 'Akses viewer dicabut.');
    }
}

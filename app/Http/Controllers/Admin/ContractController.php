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
     * Daftar kontrak yang dimiliki user (owner) atau dibagikan ke user (viewer).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $contracts = Contract::query()
            ->with(['owner:id,name,email'])
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->q.'%');
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
     * Simpan kontrak + set ACL awal (optional by email).
     */
    public function store(Request $request)
    {
        $this->authorize('create', Contract::class);

        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'file'       => ['required', 'file', 'mimes:pdf', 'max:20480'], // 20MB
            'emails'     => ['nullable'],          // array atau string "a@b.com, c@d.com"
            'emails.*'   => ['sometimes', 'email'],
        ]);

        // Normalisasi emails (array/string dipisah koma)
        $emails = [];
        if ($request->has('emails')) {
            if (is_array($request->emails)) {
                $emails = array_filter(array_map('trim', $request->emails));
            } elseif (is_string($request->emails)) {
                $emails = array_filter(array_map('trim', explode(',', $request->emails)));
            }
            $emails = array_values(array_unique($emails));
        }

        // Simpan file ke disk private -> storage/app/private/contracts/...
        $file = $request->file('file');
        $path = $file->store('contracts', 'private'); // simpan path relatif

        $contract = Contract::create([
            'owner_id'   => $request->user()->id,
            'title'      => $data['title'],
            'file_path'  => $path,                                  // contoh: "contracts/abc.pdf"
            'size_bytes' => $file->getSize(),
            'mime'       => $file->getMimeType() ?: 'application/pdf',
        ]);

        // Set ACL awal (hanya user terdaftar)
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
     * Preview inline PDF (iframe-friendly). Hanya untuk yang lolos policy 'view'.
     */
        public function preview(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $disk = 'private';  // sesuaikan dengan disk penyimpananmu
        $path = $contract->file_path;
        $mime = $contract->mime ?: 'application/pdf';
        $filename = Str::of($contract->title ?: "contract-{$contract->id}")->slug('_') . '.pdf';

        if (!Storage::disk($disk)->exists($path)) {
            abort(404, 'File kontrak tidak ditemukan.');
        }

        $stream = Storage::disk($disk)->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Accept-Ranges'       => 'bytes',
        ]);
    }

    /**
     * Download file PDF dari disk private (cek policy 'view' + cek exist).
     */
    public function download(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $disk = 'private';
        $path = $contract->file_path;

        if (!Storage::disk($disk)->exists($path)) {
            return back()->with('err', 'File kontrak tidak ditemukan di storage.');
        }

        $downloadName = Str::of($contract->title)->slug('_') . '.pdf';
        return Storage::disk($disk)->download($path, $downloadName);
    }

    /**
     * Hapus kontrak (file + record). Hanya owner/super admin (policy 'delete').
     */
    public function destroy(Request $request, Contract $contract)
    {
        $this->authorize('delete', $contract);

        $disk = 'private';
        $path = $contract->file_path;

        // Hapus file jika ada
        if ($path && Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->delete($path);
        }

        // Putuskan relasi ACL (kalau pakai many-to-many 'viewers')
        if (method_exists($contract, 'viewers')) {
            $contract->viewers()->detach();
        }

        // Hapus record
        $contract->delete();

        return redirect()
            ->route('admin.contracts.index')
            ->with('ok', 'Kontrak berhasil dihapus.');
    }

    /**
     * Tambah viewers berdasarkan email (hanya owner melalui policy 'share').
     */
    public function share(Request $request, Contract $contract)
    {
        $this->authorize('share', $contract);

        $data = $request->validate([
            'emails'   => ['required'], // array atau string dipisah koma
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

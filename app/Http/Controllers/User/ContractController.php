<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    /**
     * List kontrak milik user ATAU yang dibagikan ke user.
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
            ->withCount('viewers')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('user.contracts.index', compact('contracts'));
    }

    /**
     * Detail kontrak (tanpa fitur share/revoke).
     */
    public function show(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $contract->load(['owner:id,name,email', 'viewers:id,name,email']);

        return view('user.contracts.show', compact('contract'));
    }

    /**
 * Preview PDF inline di browser (cek policy view).
 */
public function preview(Request $request, Contract $contract)
{
    $this->authorize('view', $contract);

    $disk = 'private';                      // sesuaikan dengan disk penyimpananmu
    $path = $contract->file_path;           // path file pada disk
    $mime = $contract->mime ?: 'application/pdf';
    $filename = Str::of($contract->title ?: "contract-{$contract->id}")->slug('_').'.pdf';

    if (!Storage::disk($disk)->exists($path)) {
        abort(404, 'File kontrak tidak ditemukan.');
    }

    // Stream supaya tampil inline (bukan download)
    $stream = Storage::disk($disk)->readStream($path);

    return response()->stream(function () use ($stream) {
        fpassthru($stream);
        if (is_resource($stream)) fclose($stream);
    }, 200, [
        'Content-Type'        => $mime,
        'Content-Disposition' => 'inline; filename="'.$filename.'"',
        'Accept-Ranges'       => 'bytes', // membantu PDF viewer untuk seek
    ]);
}


    /**
     * Download file PDF (cek policy view).
     */
    public function download(Request $request, Contract $contract)
    {
        $this->authorize('view', $contract);

        $downloadName = Str::of($contract->title)->slug('_') . '.pdf';

        return Storage::disk('private')->download($contract->file_path, $downloadName);
    }
}

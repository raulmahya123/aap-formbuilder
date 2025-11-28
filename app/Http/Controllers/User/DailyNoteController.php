<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{DailyNote, Company, Site};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DailyNoteController extends Controller
{
    public function index(Request $request)
    {
        $dateInput   = $request->input('date');             // opsional
        $q           = trim((string) $request->input('q', ''));
        $userId      = $request->input('user_id');          // opsional: filter penulis
        $companyId   = $request->input('company_id');       // opsional: filter perusahaan
        $siteId      = $request->input('site_id');          // opsional: filter site
        $perPageRaw  = $request->input('perPage', '25');    // 'all' atau angka
        $perPageInt  = is_numeric($perPageRaw) ? (int) $perPageRaw : null;

        if ($perPageInt !== null) {
            $perPageInt = max(1, min($perPageInt, 100));
        }

        $query = DailyNote::query()
            ->with([
                'user:id,name',
                'company:id,code,name',
                'site:id,name,company_id',
            ])
            // jika perlu batasi ke milik user saat ini, buka komentar baris di bawah:
            // ->where('user_id', Auth::id())
            ->when($userId, fn($qb) => $qb->where('user_id', $userId))
            ->when($companyId, fn($qb) => $qb->where('company_id', $companyId))
            ->when($siteId, fn($qb) => $qb->where('site_id', $siteId))
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                      ->orWhere('content', 'like', "%{$q}%");
                });
            })
            ->when(!empty($dateInput), function ($qb) use ($dateInput) {
                $startUtc = Carbon::parse($dateInput, 'Asia/Jakarta')->startOfDay()->utc();
                $endUtc   = Carbon::parse($dateInput, 'Asia/Jakarta')->endOfDay()->utc();
                $qb->whereBetween('note_time', [$startUtc, $endUtc]);
            })
            ->orderByDesc('note_time')
            ->orderByDesc('id');

        $notes = $perPageRaw === 'all'
            ? $query->get()
            : $query->paginate($perPageInt ?? 25)->withQueryString();

        // siapkan daftar perusahaan & site untuk filter di view
        $companies = Company::orderBy('code')->get(['id','code','name']);
        $sites     = Site::orderBy('name')->get(['id','name','company_id']);

        return view('user.daily_notes.index', [
            'notes'       => $notes,
            'targetDate'  => $dateInput,
            'perPage'     => $perPageRaw,
            'query'       => $q,
            'userId'      => $userId,
            'companyId'   => $companyId,
            'siteId'      => $siteId,
            'companies'   => $companies,
            'sites'       => $sites,
        ]);
    }

    public function create()
    {
        $companies = Company::orderBy('code')->get(['id','code','name']);
        $sites     = Site::orderBy('name')->get(['id','name','company_id']);

        return view('user.daily_notes.create', [
            'companies' => $companies,
            'sites'     => $sites,
        ]);
    }

    public function store(Request $request)
    {
        // Validasi dasar + relasi company/site (opsional)
        $data = $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'content'    => ['required', 'string'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'site_id'    => ['nullable', 'exists:sites,id'],
        ]);

        // Validasi konsistensi: jika ada site_id & company_id, keduanya harus match
        if (!empty($data['site_id']) && !empty($data['company_id'])) {
            $siteOk = Site::where('id', $data['site_id'])
                ->where('company_id', $data['company_id'])
                ->exists();

            if (!$siteOk) {
                return back()
                    ->withErrors(['site_id' => 'Site tidak sesuai dengan perusahaan yang dipilih.'])
                    ->withInput();
            }
        }

        $data['user_id']   = Auth::id();
        $data['note_time'] = Carbon::now('Asia/Jakarta');

        DailyNote::create($data);

        return redirect()
            ->route('user.daily_notes.index')
            ->with('success', 'Catatan harian berhasil ditambahkan.');
    }
}

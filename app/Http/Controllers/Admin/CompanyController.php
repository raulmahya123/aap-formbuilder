<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * List companies (with simple search, sort, pagination).
     */
    public function index(Request $request)
    {
        $q    = trim((string) $request->get('q', ''));
        $sort = $request->get('sort', 'name');           // name|code|status|created_at|updated_at
        $dir  = strtolower($request->get('dir', 'asc')); // asc|desc
        $per  = max(1, (int) $request->get('per', 15));

        $query = Company::query();

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('legal_name', 'like', "%{$q}%")
                  ->orWhere('code', 'like', "%{$q}%")
                  ->orWhere('industry', 'like', "%{$q}%");
            });
        }

        if (! in_array($sort, ['name','code','status','created_at','updated_at'], true)) {
            $sort = 'name';
        }
        $dir = $dir === 'desc' ? 'desc' : 'asc';

        $companies = $query->orderBy($sort, $dir)
                           ->paginate($per)
                           ->appends($request->query());

        return view('admin.companies.index', compact('companies', 'q', 'sort', 'dir', 'per'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('admin.companies.create');
    }

    /**
     * Store new company.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'       => ['required','string','max:16',
                             Rule::unique('companies','code')->whereNull('deleted_at')],
            'name'       => ['required','string','max:255'],
            'legal_name' => ['nullable','string','max:255'],
            'slug'       => ['nullable','string','max:255',
                             Rule::unique('companies','slug')->whereNull('deleted_at')],
            'industry'   => ['nullable','string','max:100'],

            'registration_no' => ['nullable','string','max:64'],
            'npwp'            => ['nullable','string','max:32',
                                  Rule::unique('companies','npwp')->whereNull('deleted_at')],
            'nib'             => ['nullable','string','max:32',
                                  Rule::unique('companies','nib')->whereNull('deleted_at')],

            'email'       => ['nullable','email','max:255'],
            'phone'       => ['nullable','string','max:50'],
            'website'     => ['nullable','string','max:255'],

            'hq_address'  => ['nullable','string','max:500'],
            'city'        => ['nullable','string','max:100'],
            'province'    => ['nullable','string','max:100'],
            'postal_code' => ['nullable','string','max:16'],
            'country'     => ['nullable','string','size:2'],
            'addresses'   => ['nullable','array'],   // ← biarkan array, model akan cast

            'timezone'    => ['nullable','string','max:64'],
            'currency'    => ['nullable','string','size:3'],

            'status'      => ['nullable','in:active,inactive,archived'],

            'logo'        => ['nullable','file','mimes:png,jpg,jpeg,webp','max:2048'],
        ]);

        // Auto slug jika kosong + pastikan unik (abaikan soft-deleted)
        if (empty($data['slug'])) {
            $base = Str::slug($data['name'] ?: $data['code']);
            $slug = $base ?: Str::slug($data['code'] ?? Str::random(6));
            $i = 1;
            while (Company::where('slug', $slug)->whereNull('deleted_at')->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        // Defaults
        $data['country']  = $data['country']  ?? 'ID';
        $data['timezone'] = $data['timezone'] ?? 'Asia/Jakarta';
        $data['currency'] = $data['currency'] ?? 'IDR';
        $data['status']   = $data['status']   ?? 'active';

        // Handle logo (opsional) → simpan ke disk 'public'
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('logos/companies', 'public'); // ex: logos/companies/abc.png
        }

        $data['created_by'] = auth()->id();
        $company = Company::create($data);

        return redirect()
            ->route('admin.companies.show', $company->id)
            ->with('success', 'Perusahaan berhasil dibuat.');
    }

    /**
     * Show details.
     */
    public function show(Company $company)
    {
        return view('admin.companies.show', compact('company'));
    }

    /**
     * Show edit form.
     */
    public function edit(Company $company)
    {
        return view('admin.companies.edit', compact('company'));
    }

    /**
     * Update company.
     */
    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'code'       => ['required','string','max:16',
                             Rule::unique('companies','code')->ignore($company->id)->whereNull('deleted_at')],
            'name'       => ['required','string','max:255'],
            'legal_name' => ['nullable','string','max:255'],
            'slug'       => ['nullable','string','max:255',
                             Rule::unique('companies','slug')->ignore($company->id)->whereNull('deleted_at')],
            'industry'   => ['nullable','string','max:100'],

            'registration_no' => ['nullable','string','max:64'],
            'npwp'            => ['nullable','string','max:32',
                                  Rule::unique('companies','npwp')->ignore($company->id)->whereNull('deleted_at')],
            'nib'             => ['nullable','string','max:32',
                                  Rule::unique('companies','nib')->ignore($company->id)->whereNull('deleted_at')],

            'email'       => ['nullable','email','max:255'],
            'phone'       => ['nullable','string','max:50'],
            'website'     => ['nullable','string','max:255'],

            'hq_address'  => ['nullable','string','max:500'],
            'city'        => ['nullable','string','max:100'],
            'province'    => ['nullable','string','max:100'],
            'postal_code' => ['nullable','string','max:16'],
            'country'     => ['nullable','string','size:2'],
            'addresses'   => ['nullable','array'],   // ← biarkan array, model akan cast

            'timezone'    => ['nullable','string','max:64'],
            'currency'    => ['nullable','string','size:3'],

            'status'      => ['required','in:active,inactive,archived'],

            'logo'        => ['nullable','file','mimes:png,jpg,jpeg,webp','max:2048'],
            'remove_logo' => ['nullable','boolean'],
        ]);

        // Slug auto (kalau dikosongkan) + unik (abaikan soft-deleted)
        if (array_key_exists('slug', $data) && $data['slug'] === '') {
            $base = Str::slug($data['name'] ?: $data['code']);
            $slug = $base ?: Str::slug($data['code'] ?? Str::random(6));
            $i = 1;
            while (Company::where('slug', $slug)
                          ->where('id', '!=', $company->id)
                          ->whereNull('deleted_at')
                          ->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        // Handle logo: hapus jika diminta
        if ($request->boolean('remove_logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = null;
        }

        // Ganti logo jika upload baru
        if ($request->hasFile('logo')) {
            if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos/companies', 'public');
        }

        $data['updated_by'] = auth()->id();
        $company->update($data);

        return redirect()
            ->route('admin.companies.show', $company->id)
            ->with('success', 'Perusahaan berhasil diperbarui.');
    }

    /**
     * Delete company (soft delete) + bersihkan file logo.
     */
    public function destroy(Company $company)
    {
        if ($company->logo_path && Storage::disk('public')->exists($company->logo_path)) {
            Storage::disk('public')->delete($company->logo_path);
        }

        $company->delete(); // SoftDeletes aktif di model

        return redirect()
            ->route('admin.companies.index')
            ->with('success', 'Perusahaan berhasil dihapus.');
    }
}

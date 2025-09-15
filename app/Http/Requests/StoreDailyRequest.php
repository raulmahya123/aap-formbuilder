<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreDailyRequest extends FormRequest
{
    /**
     * Otorisasi:
     * - Admin & Super Admin otomatis lolos.
     * - User biasa lolos jika Gate 'daily.manage' mengizinkan untuk site_id yang dikirim.
     */
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) {
            return false;
        }

        // Ambil site_id dari input body
        $siteId = (int) $this->input('site_id');

        // 1) Pakai Gate 'daily.manage' dengan konteks site
        if (Gate::forUser($u)->allows('daily.manage', $siteId ?: null)) {
            return true;
        }

        // 2) Fallback: super admin & admin tetap boleh
        $isSuper = method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin();
        $isAdmin = method_exists($u, 'isAdmin') && $u->isAdmin();

        return $isSuper || $isAdmin;
    }

    /**
     * Normalisasi input sebelum rules dijalankan:
     * - Ubah '' pada values.* menjadi null
     * - Trim setiap notes.*
     */
    protected function prepareForValidation(): void
    {
        $values = $this->input('values', []);
        if (is_array($values)) {
            foreach ($values as $k => $v) {
                // kosong string -> null, selain itu biarkan (akan divalidasi numeric)
                if ($v === '' || $v === 'null') {
                    $values[$k] = null;
                }
            }
        }

        $notes = $this->input('notes', []);
        if (is_array($notes)) {
            foreach ($notes as $k => $v) {
                $notes[$k] = is_string($v) ? trim($v) : $v;
            }
        }

        $this->merge([
            'values' => $values,
            'notes'  => $notes,
        ]);
    }

    /**
     * Aturan validasi input.
     */
    public function rules(): array
    {
        return [
            'site_id'   => ['required', 'integer', 'exists:sites,id'],
            // gunakan format tanggal konsisten (opsional: ganti 'date' jadi 'date_format:Y-m-d')
            'date'      => ['required', 'date'],

            // values: key = indicator_id, value = angka atau null
            'values'    => ['required', 'array'],
            'values.*'  => ['nullable', 'numeric'],

            // catatan opsional per indikator
            'notes'     => ['sometimes', 'array'],
            'notes.*'   => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'site_id.required' => 'Site wajib dipilih.',
            'site_id.integer'  => 'Site tidak valid.',
            'site_id.exists'   => 'Site tidak ditemukan.',

            'date.required'    => 'Tanggal wajib diisi.',
            'date.date'        => 'Format tanggal tidak valid.',

            'values.required'  => 'Tidak ada nilai yang dikirim.',
            'values.array'     => 'Format data nilai tidak valid.',
            'values.*.numeric' => 'Nilai indikator harus angka.',

            'notes.array'      => 'Format catatan tidak valid.',
            'notes.*.max'      => 'Catatan terlalu panjang (maks 500 karakter).',
        ];
    }
}

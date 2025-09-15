<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        // 1) Kalau ada Gate/permission "daily.manage", utamakan itu
        if ($u && method_exists($u, 'can') && $u->can('daily.manage')) {
            return true;
        }

        // 2) Fallback: izinkan super admin & admin
        $isSuper = $u && method_exists($u, 'isSuperAdmin') && $u->isSuperAdmin();
        $isAdmin = $u && method_exists($u, 'isAdmin')      && $u->isAdmin();

        // (Opsional) kalau memang user biasa juga boleh input, tambahkan kondisi di bawah
        // $isUser  = $u && method_exists($u, 'isUser')       && $u->isUser();

        return $isSuper || $isAdmin; // || $isUser
    }

    public function rules(): array
    {
        return [
            'site_id'   => ['required', 'integer', 'exists:sites,id'],
            'date'      => ['required', 'date'],
            // values: key = indicator_id, value = angka atau null
            'values'    => ['required', 'array'],
            'values.*'  => ['nullable', 'numeric'],

            'notes'     => ['sometimes', 'array'],
            'notes.*'   => ['nullable', 'string', 'max:500'],
        ];
    }

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

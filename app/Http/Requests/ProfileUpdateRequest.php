<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'                    => ['required', 'string', 'max:255'],
            'nickname'                => ['nullable', 'string', 'max:100'],
            'email'                   => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'nik'                     => ['nullable', 'string', 'size:16'],
            'birth_place'             => ['nullable', 'string', 'max:100'],
            'birth_date'              => ['nullable', 'date', 'before:today'],
            'gender'                  => ['nullable', Rule::in(['L', 'P'])],
            'phone'                   => ['nullable', 'string', 'max:30'],
            'whatsapp'                => ['nullable', 'string', 'max:30'],
            'address'                 => ['nullable', 'string', 'max:1000'],
            'province'                => ['nullable', 'string', 'max:100'],
            'city'                    => ['nullable', 'string', 'max:100'],
            'district'                => ['nullable', 'string', 'max:100'],
            'postal_code'             => ['nullable', 'string', 'max:10'],
            'emergency_contact_name'  => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'avatar'                  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ];
    }
}

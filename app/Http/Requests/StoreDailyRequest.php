<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isAdmin() ?? false; }

    public function rules(): array
    {
        return [
            'site_id'              => ['required','exists:sites,id'],
            'date'                 => ['required','date'],
            'values'               => ['required','array'],         // key: indicator_id, val: number|null
            'values.*'             => ['nullable','numeric'],
            'notes'                => ['array'],
            'notes.*'              => ['nullable','string','max:500'],
        ];
    }
}

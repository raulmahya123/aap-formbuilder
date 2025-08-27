<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Form;

class DynamicEntryRequest extends FormRequest
{
    public Form $form;

    public function authorize(): bool
    {
        $slug = $this->route('form')->slug ?? $this->route('form'); // resource binding
        $this->form = $this->route('form'); // sudah di-inject dari route model binding
        return $this->user()?->can('submit', $this->form) ?? false;
    }

    public function rules(): array
    {
        $rules = [];
        $schema = $this->form->schema ?? [];

        foreach (($schema['fields'] ?? []) as $f) {
            $name = $f['name'] ?? null;
            if (!$name) continue;

            $base = [];
            $type = $f['type'] ?? 'text';
            $required = !empty($f['required']) ? 'required' : 'nullable';

            // tipe → aturan dasar
            switch ($type) {
                case 'email':   $base[] = 'string'; $base[] = 'email'; break;
                case 'date':    $base[] = 'date'; break;
                case 'number':  $base[] = 'numeric'; break;
                case 'textarea':
                case 'text':    $base[] = 'string'; break;
                case 'select':  $base[] = 'string'; break;
                case 'radio':   $base[] = 'string'; break;
                case 'checkbox':$base[] = 'array';  break;
                case 'file':
                    $base[] = 'file';
                    // opsi mimes & max (KB)
                    if (!empty($f['mimes'])) $base[] = 'mimes:'.$f['mimes'];
                    if (!empty($f['max']))   $base[] = 'max:'.$f['max'];
                    break;
                default: $base[] = 'string';
            }

            // aturan tambahan custom di schema (mis. min:3|max:80|regex:...)
            if (!empty($f['rules'])) {
                foreach (explode('|', $f['rules']) as $r) {
                    $r = trim($r);
                    if ($r !== '') $base[] = $r;
                }
            }

            // prepend required/nullable
            array_unshift($base, $required);

            $rules[$name] = $base;

            // untuk checkbox dengan opsi → pastikan setiap item adalah salah satu dari options
            if ($type === 'checkbox' && !empty($f['options'])) {
                $allowed = array_map(fn($o) => is_array($o) ? $o[0] : $o, $f['options']);
                $rules[$name.'.*'] = ['in:'.implode(',', $allowed)];
            }

            // untuk select/radio → in:options
            if (in_array($type, ['select','radio']) && !empty($f['options'])) {
                $allowed = array_map(fn($o) => is_array($o) ? $o[0] : $o, $f['options']);
                $rules[$name][] = 'in:'.implode(',', $allowed);
            }
        }

        return $rules;
    }
}

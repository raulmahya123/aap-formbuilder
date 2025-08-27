<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DynamicEntryRequest extends FormRequest
{
    public \App\Models\Form $form;

    public function authorize(): bool
    {
        $this->form = $this->route('form'); // route model binding {form:slug}
        return $this->user()?->can('submit', $this->form) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'data' => ['array'], // supaya data selalu array jika ada isian
        ];

        $schema = $this->form->schema ?? [];
        $fields = $schema['fields'] ?? [];

        foreach ($fields as $f) {
            $name = $f['name'] ?? null;
            if (!$name) continue;

            $required = !empty($f['required']) ? 'required' : 'nullable';
            $type     = $f['type'] ?? 'text';
            $multiple = (bool)($f['multiple'] ?? false);

            // util untuk ambil daftar opsi (value)
            $getAllowed = function(array $options): array {
                $out = [];
                foreach ($options as $k => $o) {
                    if (is_array($o)) {
                        if (array_key_exists('value', $o)) {
                            $out[] = (string)$o['value'];
                        } elseif (array_key_exists(0, $o)) {
                            $out[] = (string)$o[0];
                        }
                    } else {
                        $out[] = is_int($k) ? (string)$o : (string)$k;
                    }
                }
                return $out;
            };

            switch ($type) {
                case 'email':
                    $base = [$required, 'string', 'email'];
                    $rules["data.$name"] = $base;
                    break;

                case 'date':
                    $base = [$required, 'date'];
                    $rules["data.$name"] = $base;
                    break;

                case 'number':
                    $base = [$required, 'numeric'];
                    $rules["data.$name"] = $base;
                    break;

                case 'textarea':
                case 'text':
                    $base = [$required, 'string'];
                    $rules["data.$name"] = $base;
                    break;

                case 'select':
                case 'radio':
                    $base = [$required, 'string'];
                    if (!empty($f['options'])) {
                        $allowed = $getAllowed($f['options']);
                        $base[]  = 'in:'.implode(',', $allowed);
                    }
                    $rules["data.$name"] = $base;
                    break;

                case 'checkbox':
{
    $hasOptions = !empty($f['options']);
    $isMultiple = $multiple && $hasOptions;

    if ($isMultiple) {
        // checkbox dengan banyak opsi
        $rules["data.$name"] = [$required, 'array'];
        // batasi setiap item ke daftar opsi yang valid
        $allowed = [];
        foreach ($f['options'] as $k => $o) {
            if (is_array($o)) {
                if (array_key_exists('value', $o))      $allowed[] = (string)$o['value'];
                elseif (array_key_exists(0, $o))         $allowed[] = (string)$o[0];
            } else {
                $allowed[] = is_int($k) ? (string)$o : (string)$k;
            }
        }
        $rules["data.$name.*"] = ['in:' . implode(',', $allowed)];
    } else {
        // checkbox single (boolean like)
        if ($required === 'required') {
            // wajib dicentang
            $rules["data.$name"] = ['accepted'];
        } else {
            // opsional: hanya kirim "1" jika dicentang
            $rules["data.$name"] = ['nullable', 'in:1'];
        }
    }
    break;
}


                case 'file':
                    if ($multiple) {
                        $rules["data.$name"] = [$required, 'array'];
                        $fileRules = ['file'];
                        if (!empty($f['mimes'])) $fileRules[] = 'mimes:'.$f['mimes'];
                        if (!empty($f['max']))   $fileRules[] = 'max:'.$f['max'];
                        $rules["data.$name.*"] = $fileRules;
                    } else {
                        $base = [$required, 'file'];
                        if (!empty($f['mimes'])) $base[] = 'mimes:'.$f['mimes'];
                        if (!empty($f['max']))   $base[] = 'max:'.$f['max'];
                        $rules["data.$name"] = $base;
                    }
                    break;

                default:
                    $rules["data.$name"] = [$required, 'string'];
            }

            // rules tambahan custom dari schema (mis. min:3|max:80)
            if (!empty($f['rules'])) {
                foreach (explode('|', $f['rules']) as $extra) {
                    $extra = trim($extra);
                    if ($extra !== '') {
                        // tambahkan ke aturan utama (bukan ke *. untuk array)
                        $rules["data.$name"][] = $extra;
                    }
                }
            }
        }

        return $rules;
    }
}

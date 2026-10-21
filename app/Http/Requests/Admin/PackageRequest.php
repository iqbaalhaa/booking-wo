<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            // "35.000.000" → 35000000
            'price' => preg_replace('/\D/', '', (string) $this->input('price')),
            // baris fitur kosong dari form dinamis diabaikan
            'features' => array_values(array_filter(array_map('trim', (array) $this->input('features', [])), 'strlen')),
            'is_active' => $this->boolean('is_active'),
            'remove_thumbnail' => $this->boolean('remove_thumbnail'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('packages')->ignore($this->route('package'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'integer', 'min:0', 'max:9999999999'],
            'max_guests' => ['required', 'integer', 'min:1', 'max:100000'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['boolean'],
            'is_active' => ['boolean'],
            'features' => ['required', 'array', 'min:1', 'max:30'],
            'features.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'features.required' => 'Tambahkan minimal satu fitur paket.',
            'features.*.distinct' => 'Ada fitur yang ditulis dua kali.',
        ];
    }
}

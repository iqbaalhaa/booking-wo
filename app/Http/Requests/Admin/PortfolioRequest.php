<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            // wajib saat membuat, opsional saat edit (gambar lama dipertahankan)
            'image' => [$this->route('portfolio') ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'event_date' => ['nullable', 'date'],
            'package_id' => ['nullable', 'exists:packages,id'],
        ];
    }
}

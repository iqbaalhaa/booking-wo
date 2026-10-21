<?php

namespace App\Http\Requests;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Package;
use App\Rules\AvailableEventDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Booking::class);
    }

    protected function prepareForValidation(): void
    {
        // Form mengirim addons[id][selected] & addons[id][quantity]; disederhanakan jadi [id => quantity] untuk yang dicentang
        $addons = collect((array) $this->input('addons', []))
            ->filter(fn ($row) => is_array($row) && ! empty($row['selected']))
            ->map(fn ($row) => $row['quantity'] ?? 1)
            ->all();

        $this->merge(['addons' => $addons]);
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', Rule::exists('packages', 'id')->where('is_active', true)],
            'event_date' => ['bail', 'required', 'date_format:Y-m-d', new AvailableEventDate],
            'event_location' => ['required', 'string', 'max:255'],
            'groom_name' => ['required', 'string', 'max:100'],
            'bride_name' => ['required', 'string', 'max:100'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:'.($this->package()?->max_guests ?? PHP_INT_MAX)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'addons' => ['array'],
            'addons.*' => ['integer', 'min:1', 'max:'.config('restikey.max_addon_quantity')],
        ];
    }

    public function messages(): array
    {
        return [
            'guest_count.max' => 'Jumlah tamu melebihi kapasitas paket (maksimal :max tamu).',
            'addons.*.max' => 'Jumlah add-on maksimal :max.',
            'event_date.date_format' => 'Format tanggal acara tidak valid.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $chosen = array_keys($this->input('addons', []));
                $valid = Addon::active()->whereIn('id', $chosen)->count();

                if ($valid !== count($chosen)) {
                    $validator->errors()->add('addons', 'Ada add-on yang dipilih sudah tidak tersedia. Silakan pilih ulang.');
                }
            },
        ];
    }

    public function package(): ?Package
    {
        return once(fn () => Package::active()->find($this->input('package_id')));
    }
}

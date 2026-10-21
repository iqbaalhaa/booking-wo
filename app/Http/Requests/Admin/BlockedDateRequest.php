<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;

class BlockedDateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.after_or_equal' => 'Tanggal yang sudah lewat tidak perlu diblokir.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                // tanggal baru aman di-parse setelah aturan dasar lolos
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // pengaman salah ketik, bukan aturan bisnis
                if ($this->startDate()->diffInDays($this->endDate()) > 366) {
                    $validator->errors()->add('end_date', 'Rentang tanggal maksimal satu tahun.');

                    return;
                }

                $taken = Booking::locking()
                    ->whereBetween('event_date', [$this->startDate()->toDateString(), $this->endDate()->toDateString()])
                    ->orderBy('event_date')
                    ->pluck('event_date');

                if ($taken->isNotEmpty()) {
                    $validator->errors()->add('start_date', 'Tidak bisa memblokir tanggal yang sudah dibooking: '
                        .$taken->map(fn ($date) => tanggal_indo($date))->join(', ').'.');
                }
            },
        ];
    }

    public function startDate(): Carbon
    {
        return Carbon::parse($this->input('start_date'))->startOfDay();
    }

    public function endDate(): Carbon
    {
        return Carbon::parse($this->input('end_date') ?: $this->input('start_date'))->startOfDay();
    }

    /** @return Collection<int, Carbon> */
    public function dates(): Collection
    {
        return collect($this->startDate()->toPeriod($this->endDate())->toArray());
    }
}

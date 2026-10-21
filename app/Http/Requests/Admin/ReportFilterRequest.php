<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Filter laporan dari query string. Default periode: bulan berjalan.
 */
class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'basis' => ['nullable', Rule::in(['event_date', 'created_at'])],
            'status' => ['nullable', Rule::in(array_keys(Booking::STATUSES))],
            'paket' => ['nullable', 'integer', 'exists:packages,id'],
            'tipe' => ['nullable', Rule::in(array_keys(Payment::TYPES))],
        ];
    }

    public function messages(): array
    {
        return ['sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh sebelum tanggal awal.'];
    }

    /** @return array{from: Carbon, to: Carbon, basis: string, status: ?string, package_id: ?int, type: ?string} */
    public function filters(): array
    {
        $from = $this->validated('dari') ? Carbon::parse($this->validated('dari')) : today()->startOfMonth();
        $to = $this->validated('sampai') ? Carbon::parse($this->validated('sampai')) : $from->copy()->endOfMonth()->startOfDay();

        return [
            'from' => $from,
            'to' => $to,
            'basis' => $this->validated('basis') ?? 'event_date',
            'status' => $this->validated('status'),
            'package_id' => $this->validated('paket') ? (int) $this->validated('paket') : null,
            'type' => $this->validated('tipe'),
        ];
    }
}

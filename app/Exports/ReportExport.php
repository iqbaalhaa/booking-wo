<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Ekspor Excel dari view Blade, agar isi & urutan kolom sama dengan laporan di layar dan PDF.
 */
class ReportExport implements FromView, ShouldAutoSize, WithTitle
{
    public function __construct(private string $view, private array $data, private string $title)
    {
    }

    public function view(): View
    {
        return view($this->view, $this->data);
    }

    public function title(): string
    {
        return $this->title;
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class BladeComponentsTest extends TestCase
{
    public function test_badge_status_uses_label_and_color_per_type(): void
    {
        $this->blade('<x-badge-status status="paid" />')
            ->assertSee('Lunas')
            ->assertSee('text-bg-primary', false);

        $this->blade('<x-badge-status status="rejected" type="payment" />')
            ->assertSee('Ditolak')
            ->assertSee('text-bg-danger', false);
    }

    public function test_confirm_form_renders_modal_with_csrf_method_and_reason(): void
    {
        $html = (string) $this->blade(
            '<x-confirm-form action="/admin/x" method="DELETE" title="Hapus?" reason="cancel_reason">Hapus</x-confirm-form>@stack(\'modals\')'
        );

        preg_match('/data-bs-target="#(confirm-\w+)"/', $html, $match);
        $this->assertNotEmpty($match, 'Tombol pemicu modal tidak ditemukan');
        $this->assertStringContainsString('id="'.$match[1].'"', $html);
        $this->assertStringContainsString('name="_token"', $html);
        $this->assertStringContainsString('name="_method" value="DELETE"', $html);
        $this->assertStringContainsString('name="cancel_reason"', $html);
    }

    public function test_alert_shows_flash_message_escaped(): void
    {
        session()->flash('success', '<b>Booking tersimpan</b>');

        $this->blade('<x-alert />')
            ->assertSee('alert-success', false)
            ->assertSee('&lt;b&gt;Booking tersimpan&lt;/b&gt;', false);
    }
}

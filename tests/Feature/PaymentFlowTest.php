<?php

namespace Tests\Feature;

use App\Mail\PaymentRejected;
use App\Mail\PaymentVerified;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();

        $this->customer = User::factory()->create();
        $this->admin = User::factory()->admin()->create();
        $this->booking = Booking::factory()->for($this->customer)->confirmed()
            ->for(Package::factory()->create(['price' => 50_000_000]))
            ->create(); // total 50 jt → DP minimal 15 jt
    }

    private function pay(array $overrides = [], ?Booking $booking = null)
    {
        return $this->actingAs($this->customer)->post(route('payments.store', $booking ?? $this->booking), array_merge([
            'type' => Payment::TYPE_DP,
            'amount' => '15.000.000',
            'bank_name' => 'BCA',
            'account_name' => 'Rina',
            'proof' => UploadedFile::fake()->image('bukti.jpg'),
        ], $overrides));
    }

    public function test_dp_below_minimum_percentage_is_rejected(): void
    {
        $this->pay(['amount' => '14.999.999'])
            ->assertSessionHasErrors(['amount' => 'Nominal DP minimal Rp 15.000.000 (30% dari total).']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_valid_dp_is_stored_privately_and_blocks_another_submission_until_checked(): void
    {
        $this->pay()->assertRedirect(route('bookings.show', $this->booking))->assertSessionHas('success');

        $payment = Payment::sole();
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame(15_000_000, $payment->amount);
        Storage::disk('local')->assertExists($payment->proof_image);

        $this->pay()->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_payment_only_for_confirmed_booking_and_pelunasan_only_after_verified_dp(): void
    {
        $pending = Booking::factory()->for($this->customer)->create();
        $this->pay([], $pending)->assertForbidden();

        $this->pay(['type' => Payment::TYPE_PELUNASAN, 'amount' => '35.000.000'])
            ->assertSessionHasErrors(['type' => 'Pelunasan bisa dilakukan setelah DP diverifikasi.']);
    }

    public function test_amount_cannot_exceed_remaining_bill(): void
    {
        $this->pay(['amount' => '50.000.001'])->assertSessionHasErrors('amount');
    }

    public function test_verifying_dp_keeps_confirmed_and_verifying_pelunasan_marks_booking_paid(): void
    {
        $this->pay();
        $dp = Payment::sole();

        $this->actingAs($this->admin)->post(route('admin.payments.verify', $dp))->assertSessionHas('success');
        $this->assertSame(Payment::STATUS_VERIFIED, $dp->fresh()->status);
        $this->assertSame($this->admin->id, $dp->fresh()->verified_by);
        $this->assertSame(Booking::STATUS_CONFIRMED, $this->booking->fresh()->status);

        $this->pay(['type' => Payment::TYPE_DP, 'amount' => '1000000'])
            ->assertSessionHasErrors(['type' => 'DP sudah diterima. Silakan lakukan pelunasan.']);

        $this->pay(['type' => Payment::TYPE_PELUNASAN, 'amount' => '35.000.000'])->assertSessionHasNoErrors();
        $pelunasan = Payment::pelunasan()->sole();

        $this->actingAs($this->admin)->post(route('admin.payments.verify', $pelunasan));

        $this->assertSame(Booking::STATUS_PAID, $this->booking->fresh()->status);
        Mail::assertQueued(PaymentVerified::class, 2);
    }

    public function test_payment_can_only_be_processed_once(): void
    {
        $this->pay();
        $payment = Payment::sole();

        $this->actingAs($this->admin)->post(route('admin.payments.verify', $payment));
        $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), ['rejection_reason' => 'Tidak jelas'])
            ->assertSessionHasErrors(['payment' => 'Pembayaran ini sudah diproses sebelumnya.']);

        $this->assertSame(Payment::STATUS_VERIFIED, $payment->fresh()->status);
    }

    public function test_rejecting_payment_requires_reason_and_lets_customer_upload_again(): void
    {
        $this->pay();
        $payment = Payment::sole();

        $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), ['rejection_reason' => ''])
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($this->admin)->post(route('admin.payments.reject', $payment), ['rejection_reason' => 'Nominal tidak sesuai'])
            ->assertSessionHas('success');

        $this->assertSame('Nominal tidak sesuai', $payment->fresh()->rejection_reason);
        Mail::assertQueued(PaymentRejected::class, fn ($mail) => $mail->hasTo($this->customer->email));

        $this->pay()->assertSessionHasNoErrors();
        $this->assertSame(1, Payment::pending()->count());
    }

    public function test_proof_is_visible_only_to_owner_and_admin(): void
    {
        $this->pay();
        $payment = Payment::sole();
        auth()->logout();

        $this->get(route('payments.proof', $payment))->assertRedirect(route('login'));
        $this->actingAs($this->customer)->get(route('payments.proof', $payment))->assertOk();
        $this->actingAs($this->admin)->get(route('payments.proof', $payment))->assertOk();
        $this->actingAs(User::factory()->create())->get(route('payments.proof', $payment))->assertForbidden();
    }

    public function test_payment_page_shows_bank_account_and_minimum_dp(): void
    {
        $this->actingAs($this->customer)->get(route('payments.create', $this->booking))
            ->assertOk()
            ->assertSee(config('restikey.bank.account_number'))
            ->assertSee('data-copy="'.config('restikey.bank.account_number').'"', false)
            ->assertSee('Rp 15.000.000');
    }
}

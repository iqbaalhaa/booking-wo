<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileAndErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_updates_profile_but_not_email_or_role(): void
    {
        $user = User::factory()->create(['email' => 'rina@example.com']);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Rina Baru',
            'phone' => '081298765432',
            'address' => 'Jl. Melati 5, Bandung',
            'email' => 'lain@example.com',
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect(route('profile.edit'))->assertSessionHas('success');

        $user->refresh();
        $this->assertSame(['Rina Baru', '081298765432', 'Jl. Melati 5, Bandung'], [$user->name, $user->phone, $user->address]);
        $this->assertSame('rina@example.com', $user->email);
        $this->assertSame(User::ROLE_CUSTOMER, $user->role);
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'salah',
            'password' => 'rahasiaBaru123',
            'password_confirmation' => 'rahasiaBaru123',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'rahasiaBaru123',
            'password_confirmation' => 'rahasiaBaru123',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('rahasiaBaru123', $user->fresh()->password));
    }

    public function test_profile_page_requires_login(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('profile.edit'))->assertOk()->assertSee('Ganti kata sandi');
    }

    public function test_custom_error_pages_render_in_indonesian(): void
    {
        $this->get('/halaman-yang-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');

        $this->actingAs(User::factory()->create())->get('/admin/dashboard')
            ->assertForbidden()->assertSee('Halaman ini khusus admin.');

        $booking = Booking::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('bookings.show', $booking))
            ->assertForbidden()->assertSee('Anda tidak punya akses ke halaman ini');

        Route::get('/_test-500', fn () => throw new \RuntimeException('boom'));
        config(['app.debug' => false]);
        $this->get('/_test-500')->assertStatus(500)->assertSee('Maaf, terjadi kesalahan di sistem kami');
    }
}

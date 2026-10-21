<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_admin_area(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_customer_cannot_access_admin_area(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Booking terbaru');
    }

    public function test_login_redirects_by_role(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->post('/logout');

        $this->post('/login', ['email' => $customer->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_opening_customer_dashboard_is_sent_to_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_register_creates_customer_with_phone_and_ignores_role_input(): void
    {
        $this->post('/register', [
            'name' => 'Sari Pengantin',
            'email' => 'sari@example.com',
            'phone' => '081234567890',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
            'role' => User::ROLE_ADMIN,
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'sari@example.com',
            'phone' => '081234567890',
            'role' => User::ROLE_CUSTOMER,
        ]);
    }

    public function test_register_validation_messages_are_indonesian(): void
    {
        $this->from('/register')->post('/register', ['phone' => '12345'])
            ->assertSessionHasErrors([
                'name' => 'Nama wajib diisi.',
                'phone' => 'Format nomor telepon tidak valid, contoh: 081234567890.',
            ]);
    }
}

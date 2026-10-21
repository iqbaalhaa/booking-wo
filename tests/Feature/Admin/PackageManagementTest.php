<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PackageManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_create_package_with_features_and_thumbnail(): void
    {
        $this->actingAs($this->admin)->post(route('admin.packages.store'), [
            'name' => 'Gold Premium',
            'description' => 'Paket lengkap.',
            'price' => '60.000.000',
            'max_guests' => 500,
            'is_active' => '1',
            'features' => ['Dekorasi pelaminan', '  ', 'MC profesional'],
            'thumbnail' => UploadedFile::fake()->image('gold.jpg'),
        ])->assertRedirect(route('admin.packages.index'));

        $package = Package::with('features')->sole();

        $this->assertSame('gold-premium', $package->slug);
        $this->assertSame(60_000_000, $package->price);
        $this->assertTrue($package->is_active);
        $this->assertSame(['Dekorasi pelaminan', 'MC profesional'], $package->features->pluck('feature')->all());
        Storage::disk('public')->assertExists($package->thumbnail);
    }

    public function test_package_requires_at_least_one_feature(): void
    {
        $this->actingAs($this->admin)->post(route('admin.packages.store'), [
            'name' => 'Tanpa Fitur',
            'price' => '1000000',
            'max_guests' => 100,
            'features' => ['', ' '],
        ])->assertSessionHasErrors(['features' => 'Tambahkan minimal satu fitur paket.']);

        $this->assertDatabaseCount('packages', 0);
    }

    public function test_duplicate_name_gets_unique_slug_and_slug_stays_after_rename(): void
    {
        Package::factory()->create(['name' => 'Silver', 'slug' => 'silver']);
        $other = Package::factory()->create(['name' => 'Silver Baru', 'slug' => null]);

        $this->actingAs($this->admin)->put(route('admin.packages.update', $other), [
            'name' => 'Silver Spesial',
            'price' => '40000000',
            'max_guests' => 300,
            'features' => ['Dekorasi'],
        ])->assertRedirect();

        $this->assertSame('silver-baru', $other->fresh()->slug);
        $this->assertSame('silver-2', Package::uniqueSlug('Silver'));
    }

    public function test_replacing_thumbnail_deletes_old_file(): void
    {
        $old = UploadedFile::fake()->image('lama.jpg')->store('packages', 'public');
        $package = Package::factory()->create(['thumbnail' => $old]);

        $this->actingAs($this->admin)->put(route('admin.packages.update', $package), [
            'name' => $package->name,
            'price' => (string) $package->price,
            'max_guests' => $package->max_guests,
            'features' => ['Dekorasi'],
            'thumbnail' => UploadedFile::fake()->image('baru.jpg'),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($package->fresh()->thumbnail);
    }

    public function test_package_used_by_booking_cannot_be_deleted(): void
    {
        $package = Package::factory()->create();
        Booking::factory()->for($package)->create();

        $this->actingAs($this->admin)
            ->from(route('admin.packages.index'))
            ->delete(route('admin.packages.destroy', $package))
            ->assertRedirect(route('admin.packages.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($package);
    }

    public function test_unused_package_is_deleted_with_its_thumbnail(): void
    {
        $file = UploadedFile::fake()->image('x.jpg')->store('packages', 'public');
        $package = Package::factory()->create(['thumbnail' => $file]);
        $package->features()->create(['feature' => 'Dekorasi']);

        $this->actingAs($this->admin)->delete(route('admin.packages.destroy', $package))
            ->assertSessionHas('success');

        $this->assertModelMissing($package);
        $this->assertDatabaseCount('package_features', 0);
        Storage::disk('public')->assertMissing($file);
    }

    public function test_toggle_deactivates_package(): void
    {
        $package = Package::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)->patch(route('admin.packages.toggle', $package));

        $this->assertFalse($package->fresh()->is_active);
    }

    public function test_customer_cannot_manage_packages(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.packages.store'), [])
            ->assertForbidden();
    }
}

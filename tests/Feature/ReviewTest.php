<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create();
    }

    private function review(Booking $booking, array $data = ['rating' => 5, 'comment' => 'Acara berjalan sangat lancar, terima kasih!'])
    {
        return $this->actingAs($this->customer)->post(route('reviews.store', $booking), $data);
    }

    public function test_review_only_for_completed_booking(): void
    {
        foreach (['pending' => [], 'confirmed' => ['confirmed'], 'paid' => ['paid'], 'cancelled' => ['cancelled']] as $states) {
            $factory = Booking::factory()->for($this->customer);
            foreach ($states as $state) {
                $factory = $factory->{$state}();
            }
            $this->review($factory->create())->assertForbidden();
        }

        $completed = Booking::factory()->for($this->customer)->completed()->create();
        $this->review($completed)->assertRedirect(route('bookings.show', $completed))->assertSessionHas('success');

        $review = Review::sole();
        $this->assertSame(5, $review->rating);
        $this->assertFalse($review->is_published);
        $this->assertSame($this->customer->id, $review->user_id);
    }

    public function test_only_one_review_per_booking_and_only_by_owner(): void
    {
        $completed = Booking::factory()->for($this->customer)->completed()->create();

        $this->review($completed)->assertSessionHasNoErrors();
        $this->review($completed)->assertForbidden();

        $other = Booking::factory()->completed()->create();
        $this->review($other)->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $completed = Booking::factory()->for($this->customer)->completed()->create();

        $this->review($completed, ['rating' => 6, 'comment' => 'Komentar yang cukup panjang'])->assertSessionHasErrors('rating');
        $this->review($completed, ['comment' => 'Komentar yang cukup panjang'])
            ->assertSessionHasErrors(['rating' => 'Pilih rating bintang terlebih dahulu.']);
    }

    public function test_admin_publishes_review_and_it_appears_on_landing_and_package_page(): void
    {
        $admin = User::factory()->admin()->create();
        $review = Review::factory()->create(['comment' => 'Dekorasinya luar biasa indah']);
        $package = $review->booking->package;

        $this->get('/')->assertDontSee('Dekorasinya luar biasa indah');

        $this->actingAs($admin)->patch(route('admin.reviews.toggle', $review))->assertSessionHas('success');
        $this->assertTrue($review->fresh()->is_published);

        auth()->logout();
        $this->get('/')->assertSee('Dekorasinya luar biasa indah');
        $this->get(route('packages.show', $package))->assertSee('Dekorasinya luar biasa indah');

        $this->actingAs($admin)->get(route('admin.reviews.index', ['tampil' => 'ya']))->assertOk()->assertSee('Dekorasinya luar biasa indah');
        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review));
        $this->assertModelMissing($review);
    }

    public function test_review_form_shows_on_completed_booking_page(): void
    {
        $completed = Booking::factory()->for($this->customer)->completed()->create();

        $this->actingAs($this->customer)->get(route('bookings.show', $completed))
            ->assertOk()
            ->assertSee('id="ulasan"', false)
            ->assertSee('name="rating" value="5"', false);
    }
}

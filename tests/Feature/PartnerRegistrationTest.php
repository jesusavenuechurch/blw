<?php

namespace Tests\Feature;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PartnerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function invited(): Partner
    {
        return Partner::create(['full_name' => 'Guest', 'email' => 'p@example.com', 'tier' => 'diamond']);
    }

    private function form(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Reverend',
            'designation' => 'Church Pastor',
            'full_name' => 'Ada Obi',
            'phone' => '+2348031234567',
            'will_attend_ippc' => true,
            'will_be_at_exhibition' => true,
            'coming_with_spouse' => true,
            'spouse_title' => 'Brother',
            'spouse_name' => 'Chidi Obi',
        ], $overrides);
    }

    public function test_registration_page_renders_for_a_valid_token(): void
    {
        $partner = $this->invited();

        $this->get("/partner/register/{$partner->registration_token}")->assertInertia(fn (Assert $page) => $page
            ->component('partner/register')
            ->where('partner.tier', 'diamond')
            ->has('options.titles'));
    }

    public function test_unknown_token_shows_the_invalid_page(): void
    {
        $this->get('/partner/register/nope')->assertInertia(fn (Assert $page) => $page->component('partner/invalid'));
    }

    public function test_a_partner_can_register_and_sees_their_own_pass(): void
    {
        $partner = $this->invited();

        $this->post("/partner/register/{$partner->registration_token}", $this->form())
            ->assertSessionHasNoErrors()
            ->assertRedirect('/partner/success');

        $partner->refresh();
        $this->assertTrue($partner->is_registered);
        $this->assertSame('Reverend', $partner->title);
        $this->assertNull($partner->delivery_method);

        $this->get('/partner/success')->assertInertia(fn (Assert $page) => $page
            ->component('partner/success')
            ->where('partner.full_name', 'Ada Obi'));

        // The link can't be used twice.
        $this->get("/partner/register/{$partner->registration_token}")->assertInertia(fn (Assert $page) => $page->component('partner/invalid'));
    }

    public function test_success_page_does_not_leak_another_partners_pass(): void
    {
        $other = $this->invited();
        $other->update(['is_registered' => true, 'token_used_at' => now()]);

        $this->get('/partner/success')->assertInertia(fn (Assert $page) => $page->component('partner/invalid'));
    }

    public function test_delivery_method_is_required_when_not_visiting_the_lounge(): void
    {
        $partner = $this->invited();

        $this->post("/partner/register/{$partner->registration_token}", $this->form(['will_be_at_exhibition' => false]))
            ->assertSessionHasErrors('delivery_method');
    }

    public function test_spouse_name_is_required_when_coming_with_spouse(): void
    {
        $partner = $this->invited();

        $this->post("/partner/register/{$partner->registration_token}", $this->form(['spouse_name' => '']))
            ->assertSessionHasErrors('spouse_name');
    }

    public function test_qr_verification_page(): void
    {
        $partner = $this->invited();

        $this->get("/partner/verify/{$partner->id}")->assertInertia(fn (Assert $page) => $page
            ->component('partner/verify')
            ->where('partner.is_registered', false));
    }

    public function test_scanner_api_is_unchanged(): void
    {
        $partner = $this->invited();
        $partner->update(['is_registered' => true, 'coming_with_spouse' => 1]);

        $this->getJson('/api/partner/download')
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('partners.0.coming_with_spouse', 1);

        $this->postJson('/api/checkin', ['partner_id' => $partner->id])->assertCreated();
        $this->getJson("/api/checkins/status/{$partner->id}")->assertJsonPath('checked_in', true);
    }
}

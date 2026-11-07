<?php

namespace Tests\Feature;

use App\Jobs\SendPartnerEmail;
use App\Models\CheckIn;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function partnerPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Pastor',
            'designation' => 'Zonal Pastor',
            'full_name' => 'Ada Obi',
            'email' => 'ada@example.com',
            'tier' => 'gold',
            'ror_copies_sponsored' => 500,
            'will_attend_ippc' => true,
            'will_be_at_exhibition' => true,
            'coming_with_spouse' => false,
        ], $overrides);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/workspace')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect('/workspace');
    }

    public function test_staff_can_sign_in(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->post('/login', ['email' => $user->email, 'password' => 'secret-pass'])->assertRedirect('/workspace');
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = User::factory()->create(['password' => 'secret-pass']);

        $this->post('/login', ['email' => $user->email, 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_workspace_pages_render(): void
    {
        $this->actingAs(User::factory()->create());
        $partner = Partner::create($this->partnerPayload(['is_registered' => true]));
        CheckIn::create(['partner_id' => $partner->id, 'checked_in_at' => now()]);

        $this->get('/workspace')->assertInertia(fn (Assert $page) => $page
            ->component('workspace/home')
            ->where('pulse.total', 1)
            ->where('pulse.funnel.checked_in', 1));

        $this->get('/workspace/people')->assertInertia(fn (Assert $page) => $page
            ->component('workspace/people')
            ->has('partners', 1)
            ->where('partners.0.stage', 'checked_in')
            ->where('selected', null));

        $this->get("/workspace/people?partner={$partner->id}")->assertInertia(fn (Assert $page) => $page
            ->where('selected.id', $partner->id)
            ->has('selected.check_ins', 1));

        foreach (['check-ins' => 'workspace/checkins', 'outreach' => 'workspace/outreach', 'import' => 'workspace/import', 'team' => 'workspace/team'] as $url => $component) {
            $this->get("/workspace/{$url}")->assertInertia(fn (Assert $page) => $page->component($component));
        }
    }

    public function test_creating_a_partner_queues_their_invitation(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());

        $this->post('/workspace/people', $this->partnerPayload())->assertRedirect();

        $partner = Partner::where('email', 'ada@example.com')->firstOrFail();
        $this->assertNotNull($partner->registration_token);
        $this->assertNull($partner->delivery_method);
        Queue::assertPushed(SendPartnerEmail::class, fn ($job) => $job->partnerId === $partner->id);
    }

    public function test_delivery_method_is_required_unless_attending_ippc_and_lounge(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/workspace/people', $this->partnerPayload(['will_be_at_exhibition' => false]))
            ->assertSessionHasErrors('delivery_method');
    }

    public function test_spouse_fields_are_cleared_when_not_coming_with_spouse(): void
    {
        $this->actingAs(User::factory()->create());
        $partner = Partner::create($this->partnerPayload([
            'coming_with_spouse' => true, 'spouse_title' => 'Brother', 'spouse_name' => 'Chidi',
        ]));

        $this->put("/workspace/people/{$partner->id}", $this->partnerPayload(['coming_with_spouse' => false, 'spouse_name' => 'Chidi']))
            ->assertSessionHasNoErrors();

        $this->assertNull($partner->fresh()->spouse_name);
    }

    public function test_resend_and_bulk_actions(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $a = Partner::create($this->partnerPayload(['email' => 'a@example.com', 'email_failed' => true]));
        $b = Partner::create($this->partnerPayload(['email' => 'b@example.com']));

        $this->post("/workspace/people/{$a->id}/resend")->assertSessionHas('success');
        $this->assertTrue($a->fresh()->email_pending);
        $this->assertFalse($a->fresh()->email_failed);

        $this->post('/workspace/people/bulk', ['action' => 'resend', 'ids' => [$a->id, $b->id]])->assertSessionHas('success');
        Queue::assertPushed(SendPartnerEmail::class, 3);

        $this->post('/workspace/people/bulk', ['action' => 'delete', 'ids' => [$a->id, $b->id]]);
        $this->assertSame(0, Partner::count());
    }

    public function test_regenerating_a_link_changes_the_token(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        $partner = Partner::create($this->partnerPayload());
        $old = $partner->registration_token;

        $this->post("/workspace/people/{$partner->id}/regenerate")->assertSessionHas('success');

        $this->assertNotSame($old, $partner->fresh()->registration_token);
    }

    public function test_outreach_queues_custom_emails_for_selected_tiers(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());
        Partner::create($this->partnerPayload(['email' => 'g@example.com', 'tier' => 'gold']));
        Partner::create($this->partnerPayload(['email' => 'r@example.com', 'tier' => 'ruby']));

        $this->post('/workspace/outreach', ['tiers' => ['gold'], 'subject' => 'Dinner', 'message' => 'Hello'])->assertSessionHas('success');

        Queue::assertPushed(SendPartnerEmail::class, fn ($job) => $job->customSubject === 'Dinner');
        Queue::assertPushed(SendPartnerEmail::class, 1);
    }

    public function test_team_members_can_be_managed_but_not_self_removed(): void
    {
        $me = User::factory()->create();
        $this->actingAs($me);

        $this->post('/workspace/team', ['name' => 'Usher', 'email' => 'usher@example.com', 'password' => 'password1'])->assertSessionHas('success');
        $usher = User::where('email', 'usher@example.com')->firstOrFail();

        $this->delete("/workspace/team/{$me->id}")->assertSessionHas('error');
        $this->delete("/workspace/team/{$usher->id}")->assertSessionHas('success');
        $this->assertModelMissing($usher);
    }

    public function test_command_palette_search(): void
    {
        $this->actingAs(User::factory()->create());
        Partner::create($this->partnerPayload());

        $this->getJson('/workspace/search?q=ada')->assertJsonCount(1)->assertJsonPath('0.full_name', 'Ada Obi');
    }
}

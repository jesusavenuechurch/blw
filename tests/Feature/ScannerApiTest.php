<?php

namespace Tests\Feature;

use App\Models\CheckIn;
use App\Models\Partner;
use App\Models\ScannerDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScannerApiTest extends TestCase
{
    use RefreshDatabase;

    private function pairedDevice(): array
    {
        $device = ScannerDevice::invite('Main entrance');
        $res = $this->postJson('/api/scanner/v1/pair', ['code' => strtolower($device->pairing_code), 'platform' => 'android', 'app_version' => '1.0.0'])->assertOk();

        return [$device->fresh(), $res->json('token')];
    }

    private function partner(array $attrs = []): Partner
    {
        return Partner::create(array_merge(['full_name' => 'Ada Obi', 'email' => Str::random(6).'@example.com', 'tier' => 'gold', 'is_registered' => true], $attrs));
    }

    public function test_pairing_issues_a_token_once(): void
    {
        $device = ScannerDevice::invite('Door 1');
        $code = $device->pairing_code;

        $this->postJson('/api/scanner/v1/pair', ['code' => $code])->assertOk()->assertJsonStructure(['token', 'device' => ['id', 'name']]);
        $this->assertSame('active', $device->fresh()->status);

        // The same code can't pair a second phone.
        $this->postJson('/api/scanner/v1/pair', ['code' => $code])->assertStatus(422);
    }

    public function test_expired_codes_are_rejected(): void
    {
        $device = ScannerDevice::invite('Door 1');
        $device->update(['pairing_expires_at' => now()->subMinute()]);

        $this->postJson('/api/scanner/v1/pair', ['code' => $device->pairing_code])->assertStatus(422);
    }

    public function test_api_requires_a_device_token(): void
    {
        $this->getJson('/api/scanner/v1/partners')->assertUnauthorized();

        // A staff user's token is not a scanner.
        $user = User::factory()->create();
        $pat = \Laravel\Sanctum\PersonalAccessToken::forceCreate([
            'tokenable_type' => User::class, 'tokenable_id' => $user->id, 'name' => 'x',
            'token' => hash('sha256', 'plain-secret'), 'abilities' => ['*'],
        ]);
        $token = $pat->id.'|plain-secret';
        $this->withToken($token)->getJson('/api/scanner/v1/partners')->assertUnauthorized();
    }

    public function test_revoked_devices_lose_access(): void
    {
        [$device, $token] = $this->pairedDevice();
        $this->withToken($token)->getJson('/api/scanner/v1/me')->assertOk();

        $device->revoke();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/scanner/v1/me')->assertUnauthorized();
    }

    public function test_full_and_delta_partner_sync_with_deletions(): void
    {
        [, $token] = $this->pairedDevice();
        $a = $this->partner(['full_name' => 'Ada']);
        $b = $this->partner(['full_name' => 'Ben', 'is_registered' => false]);

        $full = $this->withToken($token)->getJson('/api/scanner/v1/partners')->assertOk();
        $full->assertJsonPath('full', true)->assertJsonCount(2, 'partners')->assertJsonPath('partners.0.qr_token', $a->verification_token);
        $since = $full->json('server_time');

        $this->travel(5)->seconds();
        $a->update(['tier' => 'diamond']);
        $b->delete();

        $delta = $this->withToken($token)->getJson('/api/scanner/v1/partners?since='.urlencode($since))->assertOk();
        $delta->assertJsonPath('full', false)
            ->assertJsonCount(1, 'partners')
            ->assertJsonPath('partners.0.tier', 'diamond')
            ->assertJsonPath('deleted_ids', [$b->id]);
    }

    public function test_checkin_upload_is_idempotent(): void
    {
        [$device, $token] = $this->pairedDevice();
        $p = $this->partner();
        $uuid = (string) Str::uuid();
        $payload = ['checkins' => [
            ['uuid' => $uuid, 'partner_id' => $p->id, 'checked_in_at' => now()->toIso8601String()],
            ['uuid' => (string) Str::uuid(), 'partner_id' => 999999, 'checked_in_at' => now()->toIso8601String()],
        ]];

        $this->withToken($token)->postJson('/api/scanner/v1/checkins', $payload)
            ->assertOk()
            ->assertJsonPath('accepted', [$uuid])
            ->assertJsonPath('rejected.0.reason', 'partner_not_found');

        // Retrying after a dropped connection doesn't duplicate.
        $this->withToken($token)->postJson('/api/scanner/v1/checkins', $payload)->assertJsonPath('accepted', [$uuid]);

        $this->assertSame(1, CheckIn::count());
        $this->assertSame($device->id, CheckIn::first()->scanner_device_id);
    }

    public function test_future_times_from_a_wrong_phone_clock_are_clamped(): void
    {
        [, $token] = $this->pairedDevice();
        $p = $this->partner();

        $this->withToken($token)->postJson('/api/scanner/v1/checkins', ['checkins' => [
            ['uuid' => (string) Str::uuid(), 'partner_id' => $p->id, 'checked_in_at' => now()->addDay()->toIso8601String()],
        ]])->assertOk();

        $this->assertTrue(CheckIn::first()->checked_in_at->lte(now()->addMinute()));
    }

    public function test_pulling_checkins_from_other_doors(): void
    {
        [, $token] = $this->pairedDevice();
        $p = $this->partner();
        $old = CheckIn::create(['partner_id' => $p->id, 'checked_in_at' => now()->subDays(10)]);
        $recent = CheckIn::create(['partner_id' => $p->id, 'checked_in_at' => now()]);

        $first = $this->withToken($token)->getJson('/api/scanner/v1/checkins')->assertOk();
        $first->assertJsonCount(1, 'checkins')->assertJsonPath('checkins.0.id', $recent->id)->assertJsonPath('last_id', $recent->id);

        $this->withToken($token)->getJson('/api/scanner/v1/checkins?after_id='.$recent->id)->assertJsonCount(0, 'checkins')->assertJsonPath('last_id', $recent->id);
    }

    public function test_online_lookup_checks_the_qr_token(): void
    {
        [, $token] = $this->pairedDevice();
        $p = $this->partner();

        $this->withToken($token)->getJson('/api/scanner/v1/lookup?code='.urlencode($p->verification_url))->assertOk()->assertJsonPath('partner.id', $p->id);
        $this->withToken($token)->getJson('/api/scanner/v1/lookup?code='.urlencode("https://x.test/partner/verify/{$p->id}"))->assertOk();
        $this->withToken($token)->getJson('/api/scanner/v1/lookup?code='.urlencode("https://x.test/partner/verify/{$p->id}?t=forged"))->assertNotFound();
    }

    public function test_qr_codes_encode_the_verification_token(): void
    {
        $p = $this->partner();
        $this->assertStringContainsString("/partner/verify/{$p->id}?t={$p->verification_token}", $p->verification_url);

        $this->get("/partner/verify/{$p->id}?t=wrong")->assertInertia(fn ($page) => $page->component('partner/invalid'));
        $this->get($p->verification_url)->assertInertia(fn ($page) => $page->component('partner/verify'));
    }

    public function test_workspace_can_pair_and_revoke_scanners(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/workspace/scanners', ['name' => 'Door 2'])->assertSessionHas('pairing_device_id');
        $device = ScannerDevice::firstOrFail();

        $this->get('/workspace/scanners')->assertInertia(fn ($page) => $page
            ->component('workspace/scanners')
            ->where('pairing.code', $device->pairing_code)
            ->has('devices', 1));

        $this->delete("/workspace/scanners/{$device->id}");
        $this->assertModelMissing($device);
    }
}

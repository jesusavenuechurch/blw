<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\ScannerDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PartnerPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Pastor', 'designation' => 'Church Pastor', 'full_name' => 'Ada Obi', 'kc_handle' => 'ada',
            'will_attend_ippc' => '1', 'will_be_at_exhibition' => '1', 'coming_with_spouse' => '0',
        ], $overrides);
    }

    public function test_partner_can_add_a_photo_when_registering(): void
    {
        $partner = Partner::create(['full_name' => 'Guest', 'email' => 'p@example.com', 'tier' => 'gold']);

        $this->post("/partner/register/{$partner->registration_token}", $this->registration([
            'photo' => UploadedFile::fake()->image('me.jpg', 1600, 1200),
            'photo_consent' => '1',
        ]))->assertSessionHasNoErrors()->assertRedirect('/partner/success');

        $partner->refresh();
        $this->assertNotNull($partner->photo_path);
        $this->assertNotNull($partner->photo_consent_at);
        Storage::disk('local')->assertExists($partner->photo_path);

        [$w, $h] = getimagesizefromstring(Storage::disk('local')->get($partner->photo_path));
        $this->assertSame([480, 480], [$w, $h]);

        // The success page links the photo with a signed URL that works…
        $url = null;
        $this->get('/partner/success')->assertInertia(function ($page) use (&$url) {
            $url = $page->toArray()['props']['partner']['photo_url'];
        });
        $this->get($url)->assertOk();
        // …and the plain URL doesn't.
        $this->get("/partner/photo/{$partner->id}")->assertForbidden();
    }

    public function test_photo_requires_consent(): void
    {
        $partner = Partner::create(['full_name' => 'Guest', 'email' => 'p@example.com', 'tier' => 'gold']);

        $this->post("/partner/register/{$partner->registration_token}", $this->registration([
            'photo' => UploadedFile::fake()->image('me.jpg'),
        ]))->assertSessionHasErrors('photo_consent');
    }

    public function test_staff_can_upload_and_remove_photos(): void
    {
        $this->actingAs(User::factory()->create());
        $partner = Partner::create(['full_name' => 'Ada', 'email' => 'a@example.com', 'tier' => 'gold']);

        $this->post("/workspace/people/{$partner->id}/photo", ['photo' => UploadedFile::fake()->image('a.png', 300, 500)])->assertSessionHas('success');
        $path = $partner->fresh()->photo_path;
        Storage::disk('local')->assertExists($path);
        $this->get("/workspace/people/{$partner->id}/photo")->assertOk();

        $this->delete("/workspace/people/{$partner->id}/photo");
        $this->assertNull($partner->fresh()->photo_path);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_scanners_can_download_photos_but_guests_cannot(): void
    {
        $partner = Partner::create(['full_name' => 'Ada', 'email' => 'a@example.com', 'tier' => 'gold']);
        app(\App\Services\PartnerPhotoService::class)->store($partner, UploadedFile::fake()->image('a.jpg', 600, 600));

        $this->get("/workspace/people/{$partner->id}/photo")->assertRedirect('/login');
        $this->getJson("/api/scanner/v1/partners/{$partner->id}/photo")->assertUnauthorized();

        $device = ScannerDevice::invite('Door');
        $token = $this->postJson('/api/scanner/v1/pair', ['code' => $device->pairing_code])->json('token');
        $this->withToken($token)->get("/api/scanner/v1/partners/{$partner->id}/photo")->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        $this->withToken($token)->getJson('/api/scanner/v1/partners')->assertJsonPath('partners.0.photo_version', $partner->fresh()->photo_version);
    }
}

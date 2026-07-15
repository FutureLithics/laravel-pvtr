<?php

namespace Tests\Feature;

use App\Models\LicenseRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_verify_active_current_unexpired_license(): void
    {
        LicenseRecord::create([
            'license_number' => '100-001',
            'license_prefix' => '01126',
            'entity_name' => 'Example Person',
            'entity_type' => 'Individual',
            'license_status' => 'Active',
            'email' => 'person@example.com',
            'expiration_date' => now()->addYear()->toDateString(),
            'is_current' => true,
        ]);

        $this->followingRedirects()
            ->post(route('verification.verify'), [
                'license_number' => '100-001',
                'email' => 'PERSON@example.com',
            ])
            ->assertOk()
            ->assertSee('License is valid.')
            ->assertSee('Example Person');
    }

    public function test_public_lookup_rejects_mismatched_details(): void
    {
        LicenseRecord::create([
            'license_number' => '100-001',
            'license_prefix' => '01126',
            'entity_name' => 'Example Person',
            'entity_type' => 'Individual',
            'license_status' => 'Active',
            'email' => 'person@example.com',
            'expiration_date' => now()->addYear()->toDateString(),
            'is_current' => true,
        ]);

        $this->followingRedirects()
            ->post(route('verification.verify'), [
                'license_number' => '100-001',
                'email' => 'other@example.com',
            ])
            ->assertOk()
            ->assertSee('No valid matching license was found.')
            ->assertDontSee('Example Person');
    }

    public function test_public_lookup_rejects_stale_or_expired_license(): void
    {
        LicenseRecord::create([
            'license_number' => '100-001',
            'license_prefix' => '01126',
            'entity_name' => 'Stale Person',
            'entity_type' => 'Individual',
            'license_status' => 'Active',
            'email' => 'stale@example.com',
            'expiration_date' => now()->addYear()->toDateString(),
            'is_current' => false,
        ]);

        LicenseRecord::create([
            'license_number' => '100-002',
            'license_prefix' => '01126',
            'entity_name' => 'Expired Person',
            'entity_type' => 'Individual',
            'license_status' => 'Active',
            'email' => 'expired@example.com',
            'expiration_date' => now()->subDay()->toDateString(),
            'is_current' => true,
        ]);

        $this->followingRedirects()
            ->post(route('verification.verify'), [
                'license_number' => '100-001',
                'email' => 'stale@example.com',
            ])
            ->assertSee('No valid matching license was found.');

        $this->followingRedirects()
            ->post(route('verification.verify'), [
                'license_number' => '100-002',
                'email' => 'expired@example.com',
            ])
            ->assertSee('No valid matching license was found.');
    }

    public function test_verify_redirects_to_home_so_refresh_does_not_require_get_verify_route(): void
    {
        LicenseRecord::create([
            'license_number' => '100-001',
            'license_prefix' => '01126',
            'entity_name' => 'Example Person',
            'entity_type' => 'Individual',
            'license_status' => 'Active',
            'email' => 'person@example.com',
            'expiration_date' => now()->addYear()->toDateString(),
            'is_current' => true,
        ]);

        $this->post(route('verification.verify'), [
            'license_number' => '100-001',
            'email' => 'person@example.com',
        ])
            ->assertRedirect(route('verification.index'));

        $this->get(route('verification.index'))
            ->assertOk()
            ->assertSee('License is valid.');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_users(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_view_user_management(): void
    {
        $user = User::factory()->create([
            'name' => 'Existing Admin',
            'email' => 'existing@example.com',
        ]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('User management')
            ->assertSee('existing@example.com');
    }

    public function test_authenticated_admin_can_create_another_admin_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.users.store'), [
                'name' => 'Second Admin',
                'email' => 'second@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Admin user created.');

        $created = User::where('email', 'second@example.com')->firstOrFail();

        $this->assertSame('Second Admin', $created->name);
        $this->assertTrue(Hash::check('new-password', $created->password));
    }

    public function test_user_creation_requires_unique_email(): void
    {
        $user = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $this->actingAs($user)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Duplicate Admin',
                'email' => 'taken@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('email');
    }

    public function test_authenticated_admin_can_change_their_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->put(route('admin.users.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Password updated.');

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'old-password',
        ]);

        $this->actingAs($user)
            ->from(route('admin.users.index'))
            ->put(route('admin.users.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->refresh()->password));
    }
}

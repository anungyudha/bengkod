<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_all_roles_can_access_profile_page(): void
    {
        foreach (['admin', 'dokter', 'pasien'] as $role) {
            $user = User::factory()->create([
                'nama' => "User {$role}",
                'email' => "{$role}@example.com",
                'role' => $role,
                'password' => Hash::make('password123'),
            ]);

            $response = $this->actingAs($user)->get(route('profile.edit'));
            $response->assertOk();
            $response->assertSee('Ganti Password');
            $response->assertSee($user->nama);
        }
    }

    public function test_change_password_requires_current_password(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => '',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_change_password_fails_if_current_password_incorrect(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('current_password');
    }

    public function test_change_password_fails_if_new_password_less_than_8_chars(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'short1',
            'password_confirmation' => 'short1',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_change_password_fails_if_confirmation_does_not_match(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_change_password_fails_if_new_password_is_same_as_old_password(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'oldpassword123',
            'password_confirmation' => 'oldpassword123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_can_successfully_change_password(): void
    {
        $user = User::factory()->create([
            'role' => 'pasien',
            'password' => Hash::make('oldpassword123'),
        ]);

        $response = $this->actingAs($user)->put(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword123', $user->password));
        // Verify it is hashed and not plaintext
        $this->assertNotEquals('newpassword123', $user->password);
    }
}


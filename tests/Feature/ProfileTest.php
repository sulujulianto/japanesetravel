<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('testuser', $user->username);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'username' => $user->username,
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_personal_profile_information_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => 'Edo Wardana',
            'phone' => '+62 812-3456-7890',
            'preferred_locale' => 'id',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $profile = $user->profile()->firstOrFail();

        $this->assertSame('Edo Wardana', $profile->full_name);
        $this->assertSame('+62 812-3456-7890', $profile->phone);
        $this->assertSame('id', $profile->preferred_locale);

        $this->actingAs($user)->patch('/profile', [
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => 'Edo Updated',
            'phone' => null,
            'preferred_locale' => 'en',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, UserProfile::query()->whereBelongsTo($user)->count());
        $this->assertSame('Edo Updated', $profile->refresh()->full_name);
        $this->assertNull($profile->phone);
        $this->assertSame('en', $profile->preferred_locale);
    }

    public function test_personal_profile_information_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->patch('/profile', [
            'username' => $user->username,
            'email' => $user->email,
            'full_name' => str_repeat('a', 101),
            'phone' => 'invalid-phone',
            'preferred_locale' => 'ja',
        ])->assertSessionHasErrors(['full_name', 'phone', 'preferred_locale']);

        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}

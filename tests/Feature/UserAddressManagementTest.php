<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAddressManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_address_becomes_default_automatically(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.addresses.store'), [
                ...$this->addressPayload(),
                'user_id' => $otherUser->id,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit').'#addresses');

        $address = $user->addresses()->firstOrFail();

        $this->assertTrue($address->is_default);
        $this->assertTrue($address->user->is($user));
        $this->assertSame('Home', $address->label);
        $this->assertSame('Edo Wardana', $address->recipient_name);
    }

    public function test_creating_a_new_default_address_demotes_the_previous_default(): void
    {
        $user = User::factory()->create();
        $first = UserAddress::factory()->for($user)->asDefault()->create(['label' => 'Home']);

        $this->actingAs($user)->post(route('profile.addresses.store'), [
            ...$this->addressPayload(),
            'label' => 'Office',
            'is_default' => true,
        ])->assertSessionHasNoErrors();

        $second = $user->addresses()->whereKeyNot($first->getKey())->firstOrFail();

        $this->assertFalse($first->refresh()->is_default);
        $this->assertSame('Office', $second->label);
        $this->assertTrue($second->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    public function test_user_can_update_their_address_without_changing_its_default_state(): void
    {
        $user = User::factory()->create();
        $address = UserAddress::factory()->for($user)->asDefault()->create();

        $this->actingAs($user)->patch(route('profile.addresses.update', $address), [
            'label' => 'Updated Home',
        ])->assertSessionHasNoErrors();

        $address->refresh();

        $this->assertSame('Updated Home', $address->label);
        $this->assertTrue($address->is_default);
    }

    public function test_explicitly_unsetting_the_default_promotes_an_existing_address(): void
    {
        $user = User::factory()->create();
        $first = UserAddress::factory()->for($user)->asDefault()->create();
        $second = UserAddress::factory()->for($user)->create();

        $this->actingAs($user)->patch(route('profile.addresses.update', $first), [
            ...$this->addressPayload(),
            'is_default' => false,
        ])->assertSessionHasNoErrors();

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);
        $this->assertSame(1, $user->addresses()->where('is_default', true)->count());
    }

    public function test_the_only_address_cannot_be_left_without_a_default(): void
    {
        $user = User::factory()->create();
        $address = UserAddress::factory()->for($user)->asDefault()->create();

        $this->actingAs($user)->patch(route('profile.addresses.update', $address), [
            ...$this->addressPayload(),
            'is_default' => false,
        ])->assertSessionHasNoErrors();

        $this->assertTrue($address->refresh()->is_default);
    }

    public function test_user_can_select_and_delete_a_default_address(): void
    {
        $user = User::factory()->create();
        $first = UserAddress::factory()->for($user)->asDefault()->create();
        $second = UserAddress::factory()->for($user)->create();

        $this->actingAs($user)
            ->patch(route('profile.addresses.default', $second))
            ->assertRedirect(route('profile.edit').'#addresses');

        $this->assertFalse($first->refresh()->is_default);
        $this->assertTrue($second->refresh()->is_default);

        $this->actingAs($user)
            ->delete(route('profile.addresses.destroy', $second))
            ->assertRedirect(route('profile.edit').'#addresses');

        $this->assertModelMissing($second);
        $this->assertTrue($first->refresh()->is_default);
    }

    public function test_user_cannot_manage_another_users_address(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $address = UserAddress::factory()->for($owner)->asDefault()->create();

        $this->actingAs($attacker)
            ->patch(route('profile.addresses.update', $address), $this->addressPayload())
            ->assertNotFound();

        $this->actingAs($attacker)
            ->patch(route('profile.addresses.default', $address))
            ->assertNotFound();

        $this->actingAs($attacker)
            ->delete(route('profile.addresses.destroy', $address))
            ->assertNotFound();

        $this->assertModelExists($address);
        $this->assertTrue($address->refresh()->is_default);
    }

    public function test_address_payload_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.addresses.store'), [
            ...$this->addressPayload(),
            'recipient_phone' => 'not-a-phone',
            'country_code' => 'JP',
        ])->assertSessionHasErrors(['recipient_phone', 'country_code']);

        $this->assertDatabaseCount('user_addresses', 0);
    }

    public function test_unverified_users_cannot_manage_addresses(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->post(route('profile.addresses.store'), $this->addressPayload())
            ->assertRedirect(route('verification.notice'));

        $this->assertDatabaseCount('user_addresses', 0);
    }

    public function test_admin_guard_cannot_manage_user_addresses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->post(route('profile.addresses.store'), $this->addressPayload())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('user_addresses', 0);
    }

    /** @return array<string, mixed> */
    private function addressPayload(): array
    {
        return [
            'label' => 'Home',
            'recipient_name' => 'Edo Wardana',
            'recipient_phone' => '+62 812-3456-7890',
            'address_line_1' => 'Jalan Sakura Nomor 10',
            'address_line_2' => null,
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
            'country_code' => 'ID',
        ];
    }
}

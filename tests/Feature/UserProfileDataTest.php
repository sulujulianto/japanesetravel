<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserProfile;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserProfileDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_one_profile_and_many_addresses(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->for($user)->create();
        $addresses = UserAddress::factory()->count(2)->for($user)->create();

        $user->refresh();

        $this->assertTrue($user->profile->is($profile));
        $this->assertCount(2, $user->addresses);
        $this->assertTrue($user->addresses->contains($addresses[0]));
        $this->assertTrue($user->addresses->contains($addresses[1]));
        $this->assertTrue($profile->user->is($user));
    }

    public function test_database_prevents_more_than_one_profile_per_user(): void
    {
        $user = User::factory()->create();

        UserProfile::factory()->for($user)->create();

        $this->expectException(QueryException::class);

        UserProfile::factory()->for($user)->create();
    }

    public function test_personal_profile_and_address_values_are_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->for($user)->create([
            'full_name' => 'Edo Wardana',
            'phone' => '+6281234567890',
        ]);
        $address = UserAddress::factory()->for($user)->create([
            'recipient_name' => 'Edo Wardana',
            'recipient_phone' => '+6281234567890',
            'address_line_1' => 'Jalan Sakura Nomor 10',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
        ]);

        $rawProfile = DB::table('user_profiles')->where('id', $profile->id)->firstOrFail();
        $rawAddress = DB::table('user_addresses')->where('id', $address->id)->firstOrFail();

        $this->assertNotSame('Edo Wardana', $rawProfile->full_name);
        $this->assertNotSame('+6281234567890', $rawProfile->phone);
        $this->assertNotSame('Edo Wardana', $rawAddress->recipient_name);
        $this->assertNotSame('Jalan Sakura Nomor 10', $rawAddress->address_line_1);
        $this->assertNotSame('Jakarta', $rawAddress->city);

        $this->assertSame('Edo Wardana', $profile->refresh()->full_name);
        $this->assertSame('+6281234567890', $profile->phone);
        $this->assertSame('Jalan Sakura Nomor 10', $address->refresh()->address_line_1);
        $this->assertSame('Jakarta', $address->city);
    }

    public function test_address_uses_safe_shipping_defaults(): void
    {
        $user = User::factory()->create();
        $address = UserAddress::query()->create([
            'user_id' => $user->id,
            'label' => 'Home',
            'recipient_name' => 'Edo Wardana',
            'recipient_phone' => '+6281234567890',
            'address_line_1' => 'Jalan Sakura Nomor 10',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '10110',
        ]);

        $rawAddress = DB::table('user_addresses')->where('id', $address->id)->firstOrFail();

        $this->assertSame('ID', $rawAddress->country_code);
        $this->assertFalse((bool) $rawAddress->is_default);
    }

    public function test_deleting_a_user_cascades_profile_and_addresses(): void
    {
        $user = User::factory()->create();
        $profile = UserProfile::factory()->for($user)->create();
        $addresses = UserAddress::factory()->count(2)->for($user)->create();

        $user->delete();

        $this->assertDatabaseMissing('user_profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('user_addresses', ['id' => $addresses[0]->id]);
        $this->assertDatabaseMissing('user_addresses', ['id' => $addresses[1]->id]);
    }
}

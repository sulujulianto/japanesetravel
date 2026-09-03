<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserProfile;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_users_are_rendered_by_inertia_with_explicit_contract(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->withCookie('locale', 'id')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->where('copy.title', 'Pengguna')
                ->where('copy.users', 'Pengguna')
                ->where('routes.users', '/admin/users')
                ->where('filters.search', '')
                ->where('filters.verification', '')
                ->has('options.verificationStatuses', 2)
                ->has('users.data', 0)
                ->where('users.pagination.currentPage', 1)
                ->where('users.pagination.total', 0)
            );
    }

    public function test_user_list_serializes_only_customer_accounts_without_exposing_models(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'username' => 'profile-reader',
            'email' => 'profile-reader@example.test',
        ]);
        UserProfile::factory()->for($customer)->create(['full_name' => 'Edo Wardana']);
        UserAddress::factory()->for($customer)->asDefault()->create();
        Order::create([
            'user_id' => $customer->id,
            'total_price' => 150000,
            'status' => 'pending',
            'note' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Index')
                ->has('users.data', 1)
                ->where('users.data.0.id', $customer->id)
                ->where('users.data.0.username', 'profile-reader')
                ->where('users.data.0.email', 'profile-reader@example.test')
                ->where('users.data.0.fullName', 'Edo Wardana')
                ->where('users.data.0.addressCount', 1)
                ->where('users.data.0.orderCount', 1)
                ->where('users.data.0.verification.verified', true)
                ->where('users.data.0.url', '/admin/users/'.$customer->id)
                ->missing('users.data.0.password')
                ->missing('users.data.0.remember_token')
                ->missing('users.data.0.role')
                ->missing('users.data.0.profile')
                ->missing('users.data.0.addresses')
            );
    }

    public function test_user_filters_are_applied_and_invalid_values_are_ignored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = User::factory()->create([
            'username' => 'matching-customer',
            'email' => 'matching@example.test',
        ]);
        User::factory()->unverified()->create([
            'username' => 'other-customer',
            'email' => 'other@example.test',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', [
                'q' => 'matching@example.test',
                'verification' => 'verified',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.search', 'matching@example.test')
                ->where('filters.verification', 'verified')
                ->has('users.data', 1)
                ->where('users.data.0.id', $matching->id)
            );

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['verification' => 'unknown']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filters.verification', '')
                ->has('users.data', 2)
            );
    }

    public function test_user_pagination_preserves_active_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->count(16)->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['verification' => 'unverified']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('users.data', 15)
                ->where('users.pagination.currentPage', 1)
                ->where('users.pagination.lastPage', 2)
                ->where('users.pagination.total', 16)
                ->where('users.pagination.nextUrl', '/admin/users?verification=unverified&page=2')
                ->where('users.pagination.pages.1.url', '/admin/users?verification=unverified&page=2')
            );
    }

    public function test_admin_can_view_decrypted_user_profile_addresses_and_order_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'username' => 'detail-customer',
            'email' => 'detail@example.test',
            'last_seen' => CarbonImmutable::parse('2026-09-02 08:15:00'),
        ]);
        $customer->forceFill([
            'created_at' => CarbonImmutable::parse('2026-09-01 07:30:00'),
            'email_verified_at' => CarbonImmutable::parse('2026-09-01 08:00:00'),
        ])->save();
        UserProfile::factory()->for($customer)->create([
            'full_name' => 'Edo Wardana',
            'phone' => '+62 812-3456-7890',
            'preferred_locale' => 'en',
        ]);
        UserAddress::factory()->for($customer)->create([
            'label' => 'Office',
            'recipient_name' => 'Office Recipient',
            'is_default' => false,
        ]);
        $defaultAddress = UserAddress::factory()->for($customer)->asDefault()->create([
            'label' => 'Home',
            'recipient_name' => 'Edo Wardana',
            'recipient_phone' => '+62 811-0000-0000',
            'address_line_1' => 'Jalan Sakura 10',
            'address_line_2' => 'Lantai 2',
            'city' => 'Jakarta Timur',
            'province' => 'DKI Jakarta',
            'postal_code' => '13450',
        ]);
        Order::create([
            'user_id' => $customer->id,
            'total_price' => 250000,
            'status' => 'completed',
            'note' => null,
        ]);
        Order::create([
            'user_id' => $customer->id,
            'total_price' => 100000,
            'status' => 'pending',
            'note' => null,
        ]);

        $this->actingAs($admin, 'admin')
            ->withCookie('locale', 'en')
            ->get(route('admin.users.show', $customer))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Users/Show')
                ->where('copy.title', 'User details')
                ->where('account.id', $customer->id)
                ->where('account.username', 'detail-customer')
                ->where('account.email', 'detail@example.test')
                ->where('account.profile.fullName', 'Edo Wardana')
                ->where('account.profile.phone', '+62 812-3456-7890')
                ->where('account.profile.preferredLocale.value', 'en')
                ->where('account.profile.preferredLocale.label', 'English')
                ->has('account.addresses', 2)
                ->where('account.addresses.0.id', $defaultAddress->id)
                ->where('account.addresses.0.isDefault', true)
                ->where('account.addresses.0.recipientName', 'Edo Wardana')
                ->where('account.addresses.0.addressLine1', 'Jalan Sakura 10')
                ->where('account.addresses.0.addressLine2', 'Lantai 2')
                ->where('account.addresses.0.city', 'Jakarta Timur')
                ->where('account.addresses.0.province', 'DKI Jakarta')
                ->where('account.addresses.0.postalCode', '13450')
                ->where('account.addresses.0.country', 'Indonesia')
                ->where('account.addresses.0.countryCode', 'ID')
                ->where('account.orderSummary.count', '2')
                ->where('account.orderSummary.spent', 'IDR 250,000')
                ->where('routes.ordersForUser', '/admin/orders?q=detail%40example.test')
                ->missing('account.password')
                ->missing('account.remember_token')
                ->missing('account.role')
                ->missing('account.profile.user_id')
                ->missing('account.addresses.0.user_id')
                ->missing('account.addresses.0.created_at')
            );
    }

    public function test_admin_accounts_are_not_available_through_customer_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.show', $otherAdmin))
            ->assertNotFound();
    }

    public function test_regular_user_cannot_access_admin_user_pages(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('admin.login'));

        $this->actingAs($user)
            ->get(route('admin.users.show', $otherUser))
            ->assertRedirect(route('admin.login'));
    }
}

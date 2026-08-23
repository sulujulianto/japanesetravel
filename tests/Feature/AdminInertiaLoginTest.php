<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminInertiaLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_is_rendered_by_inertia_with_localized_props(): void
    {
        $response = $this
            ->withCookie('locale', 'id')
            ->get(route('admin.login'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Auth/Login')
            ->where('locale', 'id')
            ->where('copy.title', 'Portal Admin')
            ->where('routes.submit', '/admin/login')
        );
    }

    public function test_admin_login_shares_the_authenticated_user_by_username(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('admin.login'));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.user.username', $user->username)
            ->where('auth.user.email', $user->email)
            ->where('auth.user.role', $user->role)
            ->where('auth.admin', null)
        );
    }

    public function test_admin_login_rejects_invalid_payload(): void
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest('admin');
    }

    public function test_admin_can_authenticate_through_the_inertia_login_endpoint(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
            'remember' => true,
        ]);

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_inertia_login_uses_an_external_redirect_while_dashboard_is_still_blade(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $response = $this
            ->withHeader('X-Inertia', 'true')
            ->post(route('admin.login.store'), [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $response
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSessionIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_user_sessions_are_independent(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'password' => Hash::make('password'),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user, 'web');
        $this->actingAs($admin, 'admin');

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();

        $this->post(route('logout'))->assertRedirect('/');
        $this->get(route('admin.dashboard'))->assertOk();

        $this->actingAs($user, 'web');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->get(route('dashboard'))->assertOk();
    }
}

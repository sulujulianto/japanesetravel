<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLegacyShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_admin_shell_uses_csp_safe_dialog_controls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.places.index'));

        $response
            ->assertOk()
            ->assertSee('data-admin-menu-open', false)
            ->assertSee('data-admin-menu-dialog', false)
            ->assertDontSee('x-data', false)
            ->assertDontSee('x-on:', false)
            ->assertDontSee('<summary', false);
    }
}

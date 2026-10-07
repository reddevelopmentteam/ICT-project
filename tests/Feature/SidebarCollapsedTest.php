<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SidebarCollapsedTest extends TestCase
{
    use RefreshDatabase;

    public function test_sidebar_renders_collapsed_when_raw_cookie_is_present(): void
    {
        $html = $this->actingAs($this->createUser())
            // Raw (unencrypted) cookie, exactly as written by document.cookie on toggle.
            ->withUnencryptedCookie('sidebar-collapsed', '1')
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        // Server-rendered static class (Alpine :class binding never contains this exact suffix).
        $this->assertStringContainsString(
            'relative sidebar-collapsed"',
            $html,
            'Aside must carry the server-rendered collapsed class when the cookie is set.'
        );
    }

    public function test_sidebar_renders_expanded_without_cookie(): void
    {
        $html = $this->actingAs($this->createUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="app-sidebar"', $html);
        $this->assertStringNotContainsString(
            'relative sidebar-collapsed"',
            $html,
            'Aside must not carry the server-rendered collapsed class without the cookie.'
        );
    }

    private function createUser(): User
    {
        $role = Role::query()->create([
            'name' => 'Administrator',
            'permissions' => ['dashboard'],
        ]);

        return User::query()->create([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('secret-password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}

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

        // Server-rendered static class on the aside element. Whitespace between
        // the last utility and the Blade conditional is not stable (prettier
        // reformats it), but the Alpine :class binding never contains this
        // exact `...)] sidebar-collapsed"` suffix.
        $this->assertMatchesRegularExpression(
            '/shadow-\[8px_0_30px_rgba\(0,0,0,0\.15\)\]\s+sidebar-collapsed"/',
            $html,
            'Aside must carry the server-rendered collapsed class when the cookie is set.',
        );
    }

    public function test_sidebar_renders_expanded_without_cookie(): void
    {
        $html = $this->actingAs($this->createUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="app-sidebar"', $html);
        $this->assertDoesNotMatchRegularExpression(
            '/shadow-\[8px_0_30px_rgba\(0,0,0,0\.15\)\]\s+sidebar-collapsed"/',
            $html,
            'Aside must not carry the server-rendered collapsed class without the cookie.',
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

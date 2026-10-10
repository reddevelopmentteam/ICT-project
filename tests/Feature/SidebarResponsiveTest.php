<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SidebarResponsiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_drawer_is_right_anchored_and_hidden_by_default(): void
    {
        $html = $this->actingAs($this->createUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        // Mobile: fixed drawer anchored right, slides via translate-x-full.
        $this->assertStringContainsString(
            'fixed md:sticky top-0 right-0 md:right-auto md:left-0',
            $html,
        );
        $this->assertStringContainsString(
            'translate-x-full md:translate-x-0',
            $html,
        );
        // Mobile drawer must not be affected by the desktop collapsed rail.
        $this->assertStringContainsString(
            'class="fixed inset-0 z-40 bg-black/40 md:hidden"',
            $html,
        );
        // Hamburger only below md; collapse toggle only md+.
        $this->assertStringContainsString(
            'navbar-toggle block md:hidden',
            $html,
        );
        $this->assertStringContainsString(
            'sidebar-toggle ml-auto mr-2 hidden',
            $html,
        );
        // Drawer closes on Escape, overlay tap, link navigation, and its own close button.
        $this->assertStringContainsString(
            '@keydown.escape.window="openside = false"',
            $html,
        );
        $this->assertStringContainsString('@click="openside = false"', $html);
        $this->assertStringContainsString(
            '@click="if ($event.target.closest(\'a[href]\')) openside = false"',
            $html,
        );
        $this->assertStringContainsString('sidebar-close ml-auto flex', $html);
    }

    public function test_tablet_and_desktop_share_left_sticky_sidebar(): void
    {
        $html = $this->actingAs($this->createUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('md:sticky', $html);
        $this->assertStringContainsString('md:left-0', $html);
        $this->assertStringContainsString('md:w-72 lg:w-80', $html);
    }

    public function test_drawer_scroll_lock_and_resize_guard_are_wired(): void
    {
        $html = $this->actingAs($this->createUser())
            ->get(route('dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('sidebar-drawer-open', $html);
        $this->assertStringContainsString('__sidebarResizeGuard', $html);
        $this->assertStringContainsString('navbar-toggle', $html);

        $css = file_get_contents(base_path('resources/css/app.css'));
        $this->assertStringContainsString(
            'body.sidebar-drawer-open main > div',
            $css,
        );
        $this->assertStringContainsString(
            '@media (prefers-reduced-motion: reduce)',
            $css,
        );
        $this->assertStringContainsString(
            '.navbar-toggle-icon.navbar-toggle-open',
            $css,
        );
        // Collapsed icon rail must be scoped to md+ so it never breaks the mobile drawer.
        $this->assertMatchesRegularExpression(
            '/@media \(min-width: 768px\)[\s\S]*?#app-sidebar\.sidebar-collapsed/s',
            $css,
            'Collapsed rail CSS must live inside the md+ media query.',
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

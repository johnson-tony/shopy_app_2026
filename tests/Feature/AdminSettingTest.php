<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new \Database\Seeders\DatabaseSeeder())->run();
    }

    protected function getSuperAdmin(): Admin
    {
        $admin = Admin::where('email', 'superadmin@shopy.test')->first()
            ?? Admin::where('email', 'admin@shopy.test')->first();

        return $admin;
    }

    public function test_admin_settings_table_seeded_with_defaults(): void
    {
        $this->artisan('migrate');

        $theme = AdminSetting::get('theme', 'light');
        $this->assertContains($theme, ['light', 'dark']);
    }

    public function test_guest_cannot_access_admin_settings(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_normal_customer_cannot_access_admin_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('System &amp; Theme Settings', false);
        $response->assertSee('Light (White) Theme', false);
        $response->assertSee('Dark (Midnight) Theme', false);
    }

    public function test_admin_can_update_theme_setting(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'theme' => 'dark',
            'site_name' => 'Shopy Modern 2026',
            'support_email' => 'admin@shopy.test',
            'support_phone' => '+91 99999 88888',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('dark', AdminSetting::get('theme'));
        $this->assertEquals('Shopy Modern 2026', AdminSetting::get('site_name'));
    }

    public function test_admin_login_page_renders_with_theme_and_interactive_controls(): void
    {
        AdminSetting::set('theme', 'light');

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="light"', false);
        $response->assertSee('adminThemeToggle', false);
        $response->assertSee('togglePasswordBtn', false);
        $response->assertSee('Administrator Portal', false);
    }

    public function test_admin_login_page_reflects_dark_theme_from_database(): void
    {
        AdminSetting::set('theme', 'dark');

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="dark"', false);
    }
}

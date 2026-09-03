<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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
        return Admin::where('email', 'superadmin@shopy.test')->first()
            ?? Admin::where('email', 'admin@shopy.test')->first();
    }

    public function test_admin_settings_table_has_single_boolean_column(): void
    {
        $columns = Schema::getColumnListing('admin_settings');

        // Verify key, value, group are NOT present
        $this->assertNotContains('key', $columns);
        $this->assertNotContains('value', $columns);
        $this->assertNotContains('group', $columns);

        // Verify is_dark_mode boolean column IS present
        $this->assertContains('is_dark_mode', $columns);

        // Verify default is false (Light Theme / No)
        $this->assertFalse(AdminSetting::isDarkMode());
        $this->assertEquals('light', AdminSetting::currentTheme());
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
        $response->assertSee('Theme Settings', false);
        $response->assertSee('Dark Theme Permission', false);
        $response->assertSee('No &mdash; Only Light Theme', false);
        $response->assertSee('Yes &mdash; Enable Dark Theme (User Can Change Both)', false);
    }

    public function test_admin_can_enable_dark_mode_boolean(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'is_dark_mode' => '1',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertTrue(AdminSetting::isDarkMode());
        $this->assertEquals('dark', AdminSetting::currentTheme());
    }

    public function test_admin_can_disable_dark_mode_boolean(): void
    {
        $admin = $this->getSuperAdmin();

        AdminSetting::setDarkMode(true);
        $this->assertTrue(AdminSetting::isDarkMode());

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'is_dark_mode' => '0',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertFalse(AdminSetting::isDarkMode());
        $this->assertEquals('light', AdminSetting::currentTheme());
    }

    public function test_admin_login_page_renders_with_default_light_theme(): void
    {
        AdminSetting::setDarkMode(false);

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="light"', false);
        $response->assertSee('adminThemeToggle', false);
        $response->assertSee('togglePasswordBtn', false);
        $response->assertSee('Administrator Portal', false);
    }

    public function test_admin_login_page_reflects_dark_theme_when_enabled(): void
    {
        AdminSetting::setDarkMode(true);

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="dark"', false);
    }
}

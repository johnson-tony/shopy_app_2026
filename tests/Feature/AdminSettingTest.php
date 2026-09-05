<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminSetting;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Product;
use App\Models\Role;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    protected function getSuperAdmin(): Admin
    {
        return Admin::whereHas('roles', fn ($q) => $q->where('slug', 'super-admin'))->firstOrFail();
    }

    public function test_admin_settings_table_structure(): void
    {
        $columns = Schema::getColumnListing('admin_settings');

        // Verify key, value, group are NOT present
        $this->assertNotContains('key', $columns);
        $this->assertNotContains('value', $columns);
        $this->assertNotContains('group', $columns);

        // Verify is_dark_mode, site_name, site_logo columns ARE present
        $this->assertContains('is_dark_mode', $columns);
        $this->assertContains('site_name', $columns);
        $this->assertContains('site_logo', $columns);

        // Verify default is false (Light Theme / No)
        $this->assertFalse(AdminSetting::isDarkMode());
        $this->assertEquals('light', AdminSetting::currentTheme());
        $this->assertEquals('Shopy', AdminSetting::siteName());
        $this->assertStringContainsString('images/logo/logo.png', AdminSetting::siteLogoUrl());
    }

    public function test_guest_cannot_access_admin_settings(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_without_settings_view_permission_cannot_access(): void
    {
        $role = Role::create([
            'name' => 'Restricted Admin',
            'slug' => 'restricted-admin',
            'status' => true,
        ]);

        $admin = Admin::create([
            'name' => 'Restricted Staff',
            'email' => 'restricted@shopy.test',
            'password' => bcrypt('password'),
            'status' => Admin::STATUS_ACTIVE,
        ]);

        $admin->roles()->attach($role);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_settings_page(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Site &amp; System Settings', false);
        $response->assertSee('Site / Brand Name', false);
        $response->assertSee('Site Logo', false);
        $response->assertSee('Dark Theme Permission', false);
        $response->assertSee('No &mdash; Only Light Theme', false);
        $response->assertSee('Yes &mdash; Enable Dark Theme (User Can Change Both)', false);
    }

    public function test_admin_can_enable_dark_mode_boolean(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'is_dark_mode' => '1',
            'site_name' => 'Shopy 2026',
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
            'site_name' => 'Shopy',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertFalse(AdminSetting::isDarkMode());
        $this->assertEquals('light', AdminSetting::currentTheme());
    }

    public function test_admin_can_update_site_name_in_database(): void
    {
        $admin = $this->getSuperAdmin();

        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'site_name' => 'Nexus Superstore',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $this->assertEquals('Nexus Superstore', AdminSetting::siteName());

        $this->assertDatabaseHas('admin_settings', [
            'id' => 1,
            'site_name' => 'Nexus Superstore',
        ]);
    }

    public function test_admin_can_upload_and_remove_site_logo(): void
    {
        Storage::fake('public');
        $admin = $this->getSuperAdmin();

        $logoFile = UploadedFile::fake()->image('custom-logo.png', 300, 100);

        // Upload custom logo
        $response = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'site_name' => 'Custom Brand',
            'site_logo' => $logoFile,
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $this->assertTrue(AdminSetting::hasCustomLogo());
        $this->assertStringContainsString('storage/site/', AdminSetting::siteLogoUrl());

        // Remove custom logo to restore default
        $responseRemove = $this->actingAs($admin, 'admin')->put(route('admin.settings.update'), [
            'site_name' => 'Custom Brand',
            'remove_logo' => '1',
        ]);

        $responseRemove->assertRedirect(route('admin.settings.index'));
        $this->assertFalse(AdminSetting::hasCustomLogo());
        $this->assertStringContainsString('images/logo/logo.png', AdminSetting::siteLogoUrl());
    }

    public function test_admin_login_page_renders_with_default_light_theme(): void
    {
        AdminSetting::setDarkMode(false);

        $response = $this->get(route('admin.login'));

        $response->assertStatus(200);
        $response->assertSee('data-theme="light"', false);
        $response->assertDontSee('adminThemeToggle', false);
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

    public function test_admin_portal_has_theme_toggle_inside_topbar(): void
    {
        $admin = Admin::firstOrFail();

        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('adminThemeToggle', false);
        $response->assertSee('themeToggleIcon', false);
        $response->assertSee('themeToggleText', false);
        $response->assertDontSee('View Storefront', false);
    }

    public function test_admin_products_table_paginates_at_10_records(): void
    {
        $admin = $this->getSuperAdmin();
        $mode = Mode::where('slug', 'shopy')->firstOrFail();
        $category = Category::where('mode_id', $mode->id)->firstOrFail();

        // Create 15 products
        for ($i = 1; $i <= 15; $i++) {
            Product::create([
                'mode_id' => $mode->id,
                'category_id' => $category->id,
                'name' => "Pagination Product {$i}",
                'slug' => "pagination-product-{$i}",
                'sku' => "PAG-SKU-{$i}",
                'price' => 100 + $i,
                'stock' => 10,
                'status' => true,
            ]);
        }

        // Page 1 should contain exactly 10 products
        $response = $this->actingAs($admin, 'admin')->get(route('admin.products.index', ['search' => 'Pagination Product']));

        $response->assertStatus(200);
        $productsPage1 = $response->viewData('products');
        $this->assertEquals(10, $productsPage1->perPage());
        $this->assertEquals(10, $productsPage1->count());
        $this->assertEquals(15, $productsPage1->total());
    }
}

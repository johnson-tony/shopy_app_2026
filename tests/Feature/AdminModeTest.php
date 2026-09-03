<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Mode;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ModeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModeTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = Admin::where('email', 'superadmin@shopy.test')->first()
            ?? Admin::where('email', 'admin@shopy.test')->first();
    }

    public function test_guest_cannot_access_admin_modes(): void
    {
        $response = $this->get(route('admin.modes.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_normal_customer_cannot_access_admin_modes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.modes.index'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_initial_three_modes_are_seeded(): void
    {
        $this->assertDatabaseCount('modes', 3);

        $this->assertDatabaseHas('modes', [
            'name' => 'Shopy',
            'slug' => 'shopy',
            'status' => true,
            'sort_order' => 1,
        ]);

        $this->assertDatabaseHas('modes', [
            'name' => 'Food',
            'slug' => 'food',
            'status' => true,
            'sort_order' => 2,
        ]);

        $this->assertDatabaseHas('modes', [
            'name' => 'Minutes',
            'slug' => 'minutes',
            'status' => true,
            'sort_order' => 3,
        ]);
    }

    public function test_mode_seeder_is_idempotent(): void
    {
        // Re-run seeder
        $this->seed(ModeSeeder::class);
        $this->seed(ModeSeeder::class);

        $this->assertDatabaseCount('modes', 3);
    }

    public function test_admin_can_view_modes_index_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.modes.index'));

        $response->assertStatus(200);
        $response->assertSee('Shopping Mode Management');
        $response->assertSee('Shopy');
        $response->assertSee('Food');
        $response->assertSee('Minutes');
        $response->assertSee('Total Modes');
        $response->assertSee('Active Modes');
    }

    public function test_modes_are_ordered_by_sort_order_asc_and_id_asc(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.modes.index'));

        $response->assertStatus(200);

        $modes = $response->viewData('modes');
        $this->assertCount(3, $modes);
        $this->assertEquals('Shopy', $modes[0]->name);
        $this->assertEquals('Food', $modes[1]->name);
        $this->assertEquals('Minutes', $modes[2]->name);
    }

    public function test_admin_can_filter_modes_by_search_query(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.modes.index', [
            'search' => 'Minutes',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Minutes');
        $response->assertDontSee('General shopping');
    }

    public function test_admin_can_view_create_mode_page(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.modes.create'));

        $response->assertStatus(200);
        $response->assertSee('Create New Shopping Mode');
        $response->assertSee('Mode Name');
        $response->assertSee('URL Slug');
    }

    public function test_admin_can_store_new_mode_with_custom_slug(): void
    {
        $payload = [
            'name' => 'Pharmacy',
            'slug' => 'pharmacy',
            'description' => 'Medicines and health essentials',
            'icon' => 'fa-solid fa-pills',
            'status' => '1',
            'sort_order' => 4,
        ];

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.modes.store'), $payload);

        $response->assertRedirect(route('admin.modes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('modes', [
            'name' => 'Pharmacy',
            'slug' => 'pharmacy',
            'icon' => 'fa-solid fa-pills',
            'status' => true,
            'sort_order' => 4,
        ]);
    }

    public function test_admin_can_store_new_mode_with_empty_slug_auto_generated(): void
    {
        $payload = [
            'name' => 'Pet Supplies & Care',
            'slug' => '',
            'description' => 'Everything for your beloved pets',
            'status' => '1',
            'sort_order' => 5,
        ];

        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.modes.store'), $payload);

        $response->assertRedirect(route('admin.modes.index'));

        $this->assertDatabaseHas('modes', [
            'name' => 'Pet Supplies & Care',
            'slug' => 'pet-supplies-care',
        ]);
    }

    public function test_mode_store_validates_required_name(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.modes.store'), [
            'name' => '',
            'slug' => 'test-slug',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_mode_store_validates_unique_slug(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->post(route('admin.modes.store'), [
            'name' => 'Another Minutes',
            'slug' => 'minutes', // Already exists in seeded modes
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_admin_can_view_edit_mode_page(): void
    {
        $mode = Mode::where('slug', 'minutes')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.modes.edit', $mode));

        $response->assertStatus(200);
        $response->assertSee('Edit Shopping Mode');
        $response->assertSee('Minutes');
        $response->assertSee('Grocery and quick commerce');
    }

    public function test_admin_can_update_mode_keeping_same_slug(): void
    {
        $mode = Mode::where('slug', 'minutes')->firstOrFail();

        $response = $this->actingAs($this->admin, 'admin')->put(route('admin.modes.update', $mode), [
            'name' => 'Quick Minutes',
            'slug' => 'minutes', // Same slug
            'description' => 'Superfast 10-minute grocery delivery',
            'icon' => 'fa-solid fa-bolt',
            'status' => '1',
            'sort_order' => 10,
        ]);

        $response->assertRedirect(route('admin.modes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('modes', [
            'id' => $mode->id,
            'name' => 'Quick Minutes',
            'slug' => 'minutes',
            'sort_order' => 10,
        ]);
    }

    public function test_admin_can_toggle_mode_status(): void
    {
        $mode = Mode::where('slug', 'food')->firstOrFail();
        $this->assertTrue($mode->status);

        // Toggle to Inactive
        $response = $this->actingAs($this->admin, 'admin')
            ->patch(route('admin.modes.toggleStatus', $mode));

        $response->assertSessionHas('success');
        $this->assertFalse($mode->fresh()->status);

        // Toggle back to Active via AJAX / JSON
        $jsonResponse = $this->actingAs($this->admin, 'admin')
            ->patchJson(route('admin.modes.toggleStatus', $mode));

        $jsonResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => true,
            ]);

        $this->assertTrue($mode->fresh()->status);
    }

    public function test_admin_can_delete_unused_mode(): void
    {
        $mode = Mode::create([
            'name' => 'Temporary Test Mode',
            'slug' => 'temporary-test-mode',
            'status' => false,
            'sort_order' => 99,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.modes.destroy', $mode));

        $response->assertRedirect(route('admin.modes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('modes', [
            'id' => $mode->id,
        ]);
    }

    public function test_admin_sidebar_displays_modes_navigation_link(): void
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee(route('admin.modes.index'));
        $response->assertSee('Modes');
    }
}

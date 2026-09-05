<?php

namespace Tests\Feature;

use App\Mail\AdminInvitationMail;
use App\Models\Admin;
use App\Models\AdminInvitation;
use App\Models\Category;
use App\Models\Mode;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminRBACTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $superAdmin;
    protected Admin $foodManager;
    protected Admin $minutesManager;

    protected Mode $shopyMode;
    protected Mode $foodMode;
    protected Mode $minutesMode;

    protected Product $foodProduct;
    protected Product $minutesProduct;
    protected Product $shopyProduct;

    protected Category $foodCategory;
    protected Category $minutesCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        $this->superAdmin = Admin::where('email', 'superadmin@shopy.test')->firstOrFail();

        $this->shopyMode = Mode::where('slug', 'shopy')->firstOrFail();
        $this->foodMode = Mode::where('slug', 'food')->firstOrFail();
        $this->minutesMode = Mode::where('slug', 'minutes')->firstOrFail();

        // Create Food Manager
        $this->foodManager = Admin::create([
            'name' => 'Mario Rossi',
            'email' => 'mario@shopyfood.test',
            'password' => bcrypt('password123'),
            'status' => Admin::STATUS_ACTIVE,
        ]);
        $this->foodManager->assignRole('food-manager');

        // Create Minutes Manager
        $this->minutesManager = Admin::create([
            'name' => 'Zara Quick',
            'email' => 'zara@shopyminutes.test',
            'password' => bcrypt('password123'),
            'status' => Admin::STATUS_ACTIVE,
        ]);
        $this->minutesManager->assignRole('minutes-manager');

        // Fetch sample products
        $this->foodProduct = Product::where('mode_id', $this->foodMode->id)->firstOrFail();
        $this->minutesProduct = Product::where('mode_id', $this->minutesMode->id)->firstOrFail();
        $this->shopyProduct = Product::where('mode_id', $this->shopyMode->id)->firstOrFail();

        // Fetch sample categories
        $this->foodCategory = Category::where('mode_id', $this->foodMode->id)->firstOrFail();
        $this->minutesCategory = Category::where('mode_id', $this->minutesMode->id)->firstOrFail();
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin Tests
    |--------------------------------------------------------------------------
    */

    public function test_super_admin_bypasses_all_permission_checks(): void
    {
        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->hasPermission('arbitrary.nonexistent.permission'));
        $this->assertTrue($this->superAdmin->hasModeAccess($this->foodMode));
        $this->assertTrue($this->superAdmin->hasModeAccess($this->minutesMode));
        $this->assertTrue($this->superAdmin->hasModeAccess($this->shopyMode));

        // Can access roles management
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.roles.index'));
        $response->assertStatus(200);

        // Can access admins management
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.admins.index'));
        $response->assertStatus(200);

        // Can access settings
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.settings.index'));
        $response->assertStatus(200);
    }

    public function test_super_admin_cannot_be_deleted_or_deactivated(): void
    {
        // Attempt to delete super admin via admin management
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.admins.destroy', $this->superAdmin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('admins', ['id' => $this->superAdmin->id]);

        // Attempt to toggle super admin status
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->patch(route('admin.admins.toggleStatus', $this->superAdmin));

        $response->assertSessionHas('error');
        $this->superAdmin->refresh();
        $this->assertEquals(Admin::STATUS_ACTIVE, $this->superAdmin->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Sub Admin Permission Enforcement
    |--------------------------------------------------------------------------
    */

    public function test_sub_admin_can_access_assigned_permissions(): void
    {
        // Food Manager has products.view and categories.view
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.categories.index'));
        $response->assertStatus(200);
    }

    public function test_sub_admin_cannot_access_unassigned_permissions(): void
    {
        // Food Manager does NOT have settings.view
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.settings.index'));
        $response->assertStatus(403);

        // Food Manager does NOT have roles.view
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.roles.index'));
        $response->assertStatus(403);

        // Food Manager does NOT have admins.view
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.admins.index'));
        $response->assertStatus(403);

        // Food Manager does NOT have modes.view
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.modes.index'));
        $response->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | Mode-Based Access Control & Direct URL Tampering Protection
    |--------------------------------------------------------------------------
    */

    public function test_food_manager_can_access_food_products_but_not_other_modes(): void
    {
        // Food Manager can view Food product
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.show', $this->foodProduct));
        $response->assertStatus(200);
        $response->assertSee($this->foodProduct->name);

        // Food Manager CANNOT view Minutes product via direct URL
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.show', $this->minutesProduct));
        $response->assertStatus(403);

        // Food Manager CANNOT view Shopy product via direct URL
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.show', $this->shopyProduct));
        $response->assertStatus(403);

        // Food Manager CANNOT edit Minutes product via direct URL
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.edit', $this->minutesProduct));
        $response->assertStatus(403);
    }

    public function test_food_manager_product_list_only_contains_food_mode_products(): void
    {
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertSee($this->foodProduct->name);
        $response->assertDontSee($this->minutesProduct->name);
        $response->assertDontSee($this->shopyProduct->name);
    }

    public function test_food_manager_cannot_create_product_in_unauthorized_mode(): void
    {
        $payload = [
            'mode_id' => $this->minutesMode->id, // Unauthorized mode!
            'category_id' => $this->minutesCategory->id,
            'name' => 'Illegal Cross-Channel Item',
            'price' => '10.00',
            'stock' => '5',
        ];

        $response = $this->actingAs($this->foodManager, 'admin')
            ->post(route('admin.products.store'), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('products', ['name' => 'Illegal Cross-Channel Item']);
    }

    public function test_food_manager_cannot_access_minutes_category(): void
    {
        // Food Manager CANNOT edit Minutes category via direct URL
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.categories.edit', $this->minutesCategory));
        $response->assertStatus(403);

        // Food Manager CAN edit Food category
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.categories.edit', $this->foodCategory));
        $response->assertStatus(200);
    }

    /*
    |--------------------------------------------------------------------------
    | Role Management Tests
    |--------------------------------------------------------------------------
    */

    public function test_super_admin_can_create_custom_role_with_permissions_and_modes(): void
    {
        $payload = [
            'name' => 'Regional Logistics Lead',
            'slug' => 'logistics-lead',
            'description' => 'Oversees fulfillment across Quick Commerce and Food.',
            'status' => '1',
            'permissions' => Permission::whereIn('slug', ['products.view', 'categories.view'])->pluck('id')->all(),
            'modes' => [$this->foodMode->id, $this->minutesMode->id],
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.roles.store'), $payload);

        $response->assertRedirect(route('admin.roles.index'));

        $role = Role::where('slug', 'logistics-lead')->firstOrFail();
        $this->assertEquals(2, $role->permissions()->count());
        $this->assertEquals(2, $role->modes()->count());
        $this->assertTrue($role->hasModeAccess($this->foodMode));
        $this->assertTrue($role->hasModeAccess($this->minutesMode));
        $this->assertFalse($role->hasModeAccess($this->shopyMode));
    }

    public function test_super_admin_role_cannot_be_deleted(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.roles.destroy', $superAdminRole));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['slug' => 'super-admin']);
    }

    public function test_role_cannot_be_deleted_if_assigned_to_admins(): void
    {
        $foodRole = Role::where('slug', 'food-manager')->firstOrFail();

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->delete(route('admin.roles.destroy', $foodRole));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['slug' => 'food-manager']);
    }

    /*
    |--------------------------------------------------------------------------
    | Sub Admin Invitation Flow Tests
    |--------------------------------------------------------------------------
    */

    public function test_super_admin_can_invite_sub_admin_without_plaintext_password(): void
    {
        Mail::fake();

        $role = Role::where('slug', 'food-manager')->firstOrFail();

        $payload = [
            'name' => 'Clara Barton',
            'email' => 'clara@hospitality.test',
            'phone' => '+15550188',
            'role_id' => $role->id,
            'modes' => [$this->foodMode->id],
        ];

        $response = $this->actingAs($this->superAdmin, 'admin')
            ->post(route('admin.admins.store'), $payload);

        $response->assertRedirect(route('admin.admins.index'));

        // Verify account created in inactive status
        $admin = Admin::where('email', 'clara@hospitality.test')->firstOrFail();
        $this->assertEquals(Admin::STATUS_INACTIVE, $admin->status);
        $this->assertTrue($admin->hasRole('food-manager'));

        // Verify invitation token generated
        $invitation = AdminInvitation::where('email', 'clara@hospitality.test')->firstOrFail();
        $this->assertNotEmpty($invitation->token);
        $this->assertFalse($invitation->isExpired());
        $this->assertFalse($invitation->isAccepted());

        // Verify invitation email dispatched
        Mail::assertSent(AdminInvitationMail::class, function ($mail) use ($invitation) {
            return $mail->hasTo('clara@hospitality.test')
                && $mail->invitation->token === $invitation->token;
        });
    }

    public function test_sub_admin_can_set_password_via_invitation_link(): void
    {
        $role = Role::where('slug', 'food-manager')->firstOrFail();

        $admin = Admin::create([
            'name' => 'Invited Staff',
            'email' => 'invited@shopy.test',
            'password' => Hash::make('placeholder-random'),
            'status' => Admin::STATUS_INACTIVE,
        ]);
        $admin->roles()->attach($role->id);

        $invitation = AdminInvitation::create([
            'admin_id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'token' => AdminInvitation::generateUniqueToken(),
            'role_id' => $role->id,
            'expires_at' => Carbon::now()->addHours(48),
        ]);

        // Visit accept form
        $response = $this->get(route('admin.invitations.accept', ['token' => $invitation->token]));
        $response->assertStatus(200);
        $response->assertSee('Activate Administrator Account');
        $response->assertSee('invited@shopy.test');

        // Submit password
        $processResponse = $this->post(route('admin.invitations.process', ['token' => $invitation->token]), [
            'password' => 'NewSecretPassword123!',
            'password_confirmation' => 'NewSecretPassword123!',
        ]);

        $processResponse->assertRedirect(route('admin.dashboard'));

        // Verify account is now active
        $admin->refresh();
        $this->assertEquals(Admin::STATUS_ACTIVE, $admin->status);
        $this->assertTrue(Hash::check('NewSecretPassword123!', $admin->password));

        // Verify invitation is marked accepted
        $invitation->refresh();
        $this->assertTrue($invitation->isAccepted());

        // Reusing the token must fail
        $reuseResponse = $this->get(route('admin.invitations.accept', ['token' => $invitation->token]));
        $reuseResponse->assertStatus(200);
        $reuseResponse->assertSee('Invitation Already Accepted');
    }

    public function test_expired_invitation_cannot_be_used(): void
    {
        $role = Role::where('slug', 'food-manager')->firstOrFail();

        $invitation = AdminInvitation::create([
            'name' => 'Expired User',
            'email' => 'expired@shopy.test',
            'token' => AdminInvitation::generateUniqueToken(),
            'role_id' => $role->id,
            'expires_at' => Carbon::now()->subHour(), // Expired 1 hour ago
        ]);

        $response = $this->get(route('admin.invitations.accept', ['token' => $invitation->token]));
        $response->assertStatus(200);
        $response->assertSee('Invitation Expired');

        $processResponse = $this->post(route('admin.invitations.process', ['token' => $invitation->token]), [
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $processResponse->assertRedirect(route('admin.login'));
        $processResponse->assertSessionHas('error');
    }

    public function test_sub_admin_cannot_manage_or_delete_super_admin(): void
    {
        // Give Food Manager admins.edit and admins.delete permissions to test protection
        $adminsEdit = Permission::where('slug', 'admins.edit')->firstOrFail();
        $adminsDelete = Permission::where('slug', 'admins.delete')->firstOrFail();

        $foodRole = Role::where('slug', 'food-manager')->firstOrFail();
        $foodRole->permissions()->attach([$adminsEdit->id, $adminsDelete->id]);

        // Attempt to edit Super Admin
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.admins.edit', $this->superAdmin));
        $response->assertStatus(403);

        // Attempt to update Super Admin
        $response = $this->actingAs($this->foodManager, 'admin')
            ->put(route('admin.admins.update', $this->superAdmin), [
                'name' => 'Hacked Super Admin',
                'email' => 'hacked@shopy.test',
                'role_id' => $foodRole->id,
            ]);
        $response->assertStatus(403);

        // Attempt to delete Super Admin
        $response = $this->actingAs($this->foodManager, 'admin')
            ->delete(route('admin.admins.destroy', $this->superAdmin));
        $response->assertSessionHas('error');
    }

    public function test_sidebar_respects_permissions(): void
    {
        // Super Admin sees everything in sidebar
        $response = $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Administrators');
        $response->assertSee('Roles & Permissions');
        $response->assertSee('System Settings');

        // Food Manager sees Dashboard, Categories, Products, but NOT Administrators or Roles or Settings
        $response = $this->actingAs($this->foodManager, 'admin')
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Categories');
        $response->assertSee('Products');
        $response->assertDontSee('Administrators');
        $response->assertDontSee('Roles & Permissions');
        $response->assertDontSee('System Settings');
    }
}

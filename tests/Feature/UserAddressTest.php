<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAddressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    public function test_guest_cannot_access_addresses(): void
    {
        $response = $this->get(route('user.addresses.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_view_empty_addresses_page(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $user->addresses()->delete();

        $response = $this->actingAs($user, 'web')->get(route('user.addresses.index'));

        $response->assertStatus(200);
        $response->assertSee('My Addresses');
        $response->assertSee('No addresses saved yet');
    }

    public function test_user_can_view_create_address_form(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        $response = $this->actingAs($user, 'web')->get(route('user.addresses.create'));

        $response->assertStatus(200);
        $response->assertSee('Add New Address');
        $response->assertSee('Address Type');
        $response->assertSee('Address Line 1');
    }

    public function test_user_can_store_new_address(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $user->addresses()->delete();

        $response = $this->actingAs($user, 'web')->post(route('user.addresses.store'), [
            'address_type' => 'home',
            'full_name' => 'Johnson Tony',
            'phone' => '+91 98765 43210',
            'address_line1' => '123 Main Street',
            'address_line2' => 'Apt 4B',
            'landmark' => 'Near City Mall',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'is_default' => 0,
        ]);

        $response->assertRedirect(route('user.addresses.index'));
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'full_name' => 'Johnson Tony',
            'address_line1' => '123 Main Street',
            'city' => 'Bengaluru',
            'is_default' => 1, // First address becomes default automatically
        ]);
    }

    public function test_user_can_add_multiple_addresses_and_switch_default(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        $address1 = UserAddress::create([
            'user_id' => $user->id,
            'address_type' => 'home',
            'full_name' => 'Johnson Home',
            'phone' => '+91 98765 43210',
            'address_line1' => 'Home Address Line 1',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'is_default' => true,
        ]);

        $this->actingAs($user, 'web')->post(route('user.addresses.store'), [
            'address_type' => 'work',
            'full_name' => 'Johnson Work',
            'phone' => '+91 98765 43210',
            'address_line1' => 'Office Tech Park',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560100',
            'country' => 'India',
            'is_default' => 1,
        ]);

        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $user->id,
            'full_name' => 'Johnson Work',
            'is_default' => 1,
        ]);

        $address1->refresh();
        $this->assertFalse((bool)$address1->is_default);
    }

    public function test_user_can_edit_and_update_address(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        $address = UserAddress::create([
            'user_id' => $user->id,
            'address_type' => 'home',
            'full_name' => 'Johnson Tony',
            'phone' => '+91 98765 43210',
            'address_line1' => '123 Old St',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('user.addresses.edit', $address));
        $response->assertStatus(200);
        $response->assertSee('Edit Address');

        $updateResponse = $this->actingAs($user, 'web')->put(route('user.addresses.update', $address), [
            'address_type' => 'work',
            'full_name' => 'Johnson Tony Updated',
            'phone' => '+91 98765 43210',
            'address_line1' => '456 New St',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560002',
            'country' => 'India',
            'is_default' => 1,
        ]);

        $updateResponse->assertRedirect(route('user.addresses.index'));
        $this->assertDatabaseHas('user_addresses', [
            'id' => $address->id,
            'full_name' => 'Johnson Tony Updated',
            'address_line1' => '456 New St',
        ]);
    }

    public function test_user_can_delete_address(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        $address = UserAddress::create([
            'user_id' => $user->id,
            'address_type' => 'home',
            'full_name' => 'Johnson Tony',
            'phone' => '+91 98765 43210',
            'address_line1' => '123 Main St',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user, 'web')->delete(route('user.addresses.destroy', $address));
        $response->assertRedirect(route('user.addresses.index'));

        $this->assertDatabaseMissing('user_addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_user_can_set_address_as_default(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        $address1 = UserAddress::create([
            'user_id' => $user->id,
            'address_type' => 'home',
            'full_name' => 'Address 1',
            'address_line1' => '123 Main St',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560001',
            'country' => 'India',
            'is_default' => true,
        ]);

        $address2 = UserAddress::create([
            'user_id' => $user->id,
            'address_type' => 'work',
            'full_name' => 'Address 2',
            'address_line1' => '456 Work Rd',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560002',
            'country' => 'India',
            'is_default' => false,
        ]);

        $response = $this->actingAs($user, 'web')->patch(route('user.addresses.default', $address2));
        $response->assertRedirect(route('user.addresses.index'));

        $address1->refresh();
        $address2->refresh();

        $this->assertFalse((bool)$address1->is_default);
        $this->assertTrue((bool)$address2->is_default);
    }

    public function test_user_cannot_modify_another_users_address(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();
        $otherUser = User::factory()->create();

        $otherAddress = UserAddress::create([
            'user_id' => $otherUser->id,
            'address_type' => 'home',
            'full_name' => 'Other Person',
            'address_line1' => '999 Other St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'country' => 'India',
            'is_default' => true,
        ]);

        $response = $this->actingAs($user, 'web')->get(route('user.addresses.edit', $otherAddress));
        $response->assertStatus(403);

        $response = $this->actingAs($user, 'web')->delete(route('user.addresses.destroy', $otherAddress));
        $response->assertStatus(403);

        $response = $this->actingAs($user, 'web')->patch(route('user.addresses.default', $otherAddress));
        $response->assertStatus(403);
    }

    public function test_reverse_geocode_validation(): void
    {
        $user = User::where('email', 'customer@shopy.test')->first();

        // Guest cannot reverse geocode
        $guestResponse = $this->postJson(route('user.addresses.reverseGeocode'), [
            'latitude' => 12.9716,
            'longitude' => 77.5946,
        ]);
        $guestResponse->assertStatus(401);

        // Invalid lat/lng validation
        $invalidResponse = $this->actingAs($user, 'web')->postJson(route('user.addresses.reverseGeocode'), [
            'latitude' => 999,
            'longitude' => 999,
        ]);
        $invalidResponse->assertStatus(422);
    }
}

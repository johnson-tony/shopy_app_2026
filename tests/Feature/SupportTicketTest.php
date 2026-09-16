<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DeliveryPartner;
use App\Models\Mode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserAddress;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new DatabaseSeeder())->run();
    }

    protected function getCustomer(): User
    {
        return User::where('email', 'customer@shopy.test')->firstOrFail();
    }

    protected function getSuperAdmin(): Admin
    {
        return Admin::where('email', 'superadmin@shopy.test')->firstOrFail();
    }

    protected function getRider(): DeliveryPartner
    {
        return DeliveryPartner::where('email', 'rider@shopy.test')->firstOrFail();
    }

    protected function createTestOrder(User $user, array $overrides = []): Order
    {
        $mode = Mode::where('slug', 'shopy')->first() ?? Mode::first();

        $address = UserAddress::where('user_id', $user->id)->first();
        if (!$address) {
            $address = UserAddress::create([
                'user_id'       => $user->id,
                'full_name'     => $user->name,
                'phone'         => '9876543210',
                'address_line1' => '100 Main Street',
                'city'          => 'Bengaluru',
                'state'         => 'Karnataka',
                'postal_code'   => '560001',
                'address_type'  => 'home',
                'is_default'    => true,
            ]);
        }

        $order = Order::create(array_merge([
            'order_number'          => Order::generateOrderNumber(),
            'user_id'               => $user->id,
            'mode_id'               => $mode->id,
            'address_id'            => $address->id,
            'shipping_name'         => $address->full_name,
            'shipping_phone'        => $address->phone,
            'shipping_address_line1'=> $address->address_line1,
            'shipping_city'         => $address->city,
            'shipping_state'        => $address->state,
            'shipping_postal_code'  => $address->postal_code,
            'status'                => Order::STATUS_DELIVERED,
            'payment_method'        => 'cod',
            'payment_status'        => 'paid',
            'subtotal'              => 1000.00,
            'delivery_fee'          => 50.00,
            'tax_amount'            => 50.00,
            'discount_amount'       => 0.00,
            'grand_total'           => 1100.00,
        ], $overrides));

        $product = Product::first();
        if ($product) {
            OrderItem::create([
                'order_id'       => $order->id,
                'product_id'     => $product->id,
                'product_name'   => $product->name,
                'product_slug'   => $product->slug,
                'quantity'       => 1,
                'unit_price'     => 1000.00,
                'subtotal'       => 1000.00,
            ]);
        }

        return $order;
    }

    // ------------------------------------------------------------------
    // Schema & Database Structure
    // ------------------------------------------------------------------

    public function test_support_tables_and_columns_exist(): void
    {
        $this->assertTrue(Schema::hasTable('support_tickets'));
        $this->assertTrue(Schema::hasTable('support_messages'));

        $ticketCols = Schema::getColumnListing('support_tickets');
        $this->assertContains('ticket_number', $ticketCols);
        $this->assertContains('user_id', $ticketCols);
        $this->assertContains('order_id', $ticketCols);
        $this->assertContains('category', $ticketCols);
        $this->assertContains('priority', $ticketCols);
        $this->assertContains('status', $ticketCols);
        $this->assertContains('partner_contacted', $ticketCols);
        $this->assertContains('partner_contact_notes', $ticketCols);

        $msgCols = Schema::getColumnListing('support_messages');
        $this->assertContains('support_ticket_id', $msgCols);
        $this->assertContains('sender_type', $msgCols);
        $this->assertContains('message', $msgCols);
        $this->assertContains('attachment', $msgCols);
        $this->assertContains('is_internal', $msgCols);
    }

    // ------------------------------------------------------------------
    // Customer Support Hub & Ticket Creation
    // ------------------------------------------------------------------

    public function test_customer_can_view_support_hub(): void
    {
        $user = $this->getCustomer();

        $response = $this->actingAs($user)->get(route('support.index'));

        $response->assertStatus(200);
        $response->assertSee('24/7 Dedicated Support Center');
        $response->assertSee('+91 63796 44145');
        $response->assertSee('Open Support Ticket');
    }

    public function test_customer_can_view_support_create_form_with_order(): void
    {
        $user = $this->getCustomer();
        $order = $this->createTestOrder($user, [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($user)->get(route('support.create', ['order_number' => $order->order_number]));

        $response->assertStatus(200);
        $response->assertSee('Linked to Order');
        $response->assertSee('#' . $order->order_number);
        $response->assertSee($user->email);
    }

    public function test_customer_can_create_ticket_with_order_and_attachment(): void
    {
        Storage::fake('public');
        $user = $this->getCustomer();
        $order = $this->createTestOrder($user, [
            'status' => Order::STATUS_OUT_FOR_DELIVERY,
        ]);

        $file = UploadedFile::fake()->image('parcel_delay.jpg');

        $payload = [
            'name'       => 'Test User',
            'email'      => $user->email,
            'phone'      => '+919876543210',
            'subject'    => 'Rider delayed delivery past ETA',
            'category'   => 'partner_issue',
            'priority'   => 'urgent',
            'order_id'   => $order->id,
            'message'    => 'The delivery driver has been stopped near the junction for 40 minutes.',
            'attachment' => $file,
        ];

        $response = $this->actingAs($user)->post(route('support.store'), $payload);

        $ticket = SupportTicket::where('subject', 'Rider delayed delivery past ETA')->first();
        $this->assertNotNull($ticket);
        $this->assertEquals($order->id, $ticket->order_id);
        $this->assertEquals('partner_issue', $ticket->category);
        $this->assertEquals('urgent', $ticket->priority);
        $this->assertEquals(SupportTicket::STATUS_OPEN, $ticket->status);

        $response->assertRedirect(route('support.show', $ticket->ticket_number));

        // Message verification
        $message = $ticket->messages()->first();
        $this->assertNotNull($message);
        $this->assertEquals(SupportMessage::SENDER_USER, $message->sender_type);
        $this->assertFalse($message->is_internal);
        $this->assertNotNull($message->attachment);
        Storage::disk('public')->assertExists($message->attachment);
    }

    public function test_customer_can_chat_and_reply_to_ticket(): void
    {
        $user = $this->getCustomer();
        $ticket = SupportTicket::create([
            'user_id'       => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'subject'       => 'Need address update',
            'category'      => 'order_issue',
            'priority'      => 'normal',
            'status'        => SupportTicket::STATUS_OPEN,
        ]);

        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_type'       => SupportMessage::SENDER_USER,
            'user_id'           => $user->id,
            'message'           => 'Initial message from customer',
        ]);

        $replyResponse = $this->actingAs($user)->post(route('support.reply', $ticket->ticket_number), [
            'message' => 'Here is my updated flat number 402B.',
        ]);

        $replyResponse->assertSessionHas('success');
        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'message'           => 'Here is my updated flat number 402B.',
            'sender_type'       => SupportMessage::SENDER_USER,
        ]);

        $viewResponse = $this->actingAs($user)->get(route('support.show', $ticket->ticket_number));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Here is my updated flat number 402B.');
    }

    public function test_customer_cannot_view_another_customers_ticket(): void
    {
        $user1 = $this->getCustomer();
        $user2 = User::factory()->create();

        $ticket = SupportTicket::create([
            'user_id'       => $user1->id,
            'name'          => $user1->name,
            'email'         => $user1->email,
            'subject'       => 'Private customer issue',
            'category'      => 'account',
            'status'        => SupportTicket::STATUS_OPEN,
        ]);

        $response = $this->actingAs($user2)->get(route('support.show', $ticket->ticket_number));
        $response->assertStatus(403);
    }

    // ------------------------------------------------------------------
    // Admin Support Helpdesk & Workspace
    // ------------------------------------------------------------------

    public function test_admin_can_view_support_index_and_filter_by_partner_issue(): void
    {
        $admin = $this->getSuperAdmin();

        SupportTicket::create([
            'name'     => 'Customer A',
            'email'    => 'a@example.com',
            'subject'  => 'General return query',
            'category' => 'return_refund',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        SupportTicket::create([
            'name'     => 'Customer B',
            'email'    => 'b@example.com',
            'subject'  => 'Rider did not deliver parcel',
            'category' => 'partner_issue',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.support.index', ['category' => 'partner_issue']));

        $response->assertStatus(200);
        $response->assertSee('Rider did not deliver parcel');
        $response->assertDontSee('General return query');
    }

    public function test_admin_can_reply_to_customer_and_it_changes_status_to_in_progress(): void
    {
        $admin = $this->getSuperAdmin();
        $ticket = SupportTicket::create([
            'name'     => 'Customer Query',
            'email'    => 'c@example.com',
            'subject'  => 'Where is my order?',
            'category' => 'delivery_delay',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.support.reply', $ticket->id), [
            'message'     => 'Hello, our team has tracked your shipment and it is on the way.',
            'is_internal' => 0,
        ]);

        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(SupportTicket::STATUS_IN_PROGRESS, $ticket->status);
        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'admin_id'          => $admin->id,
            'sender_type'       => SupportMessage::SENDER_ADMIN,
            'is_internal'       => false,
            'message'           => 'Hello, our team has tracked your shipment and it is on the way.',
        ]);
    }

    public function test_internal_staff_notes_are_invisible_to_customer(): void
    {
        $admin = $this->getSuperAdmin();
        $user = $this->getCustomer();

        $ticket = SupportTicket::create([
            'user_id'  => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'subject'  => 'Damage claim',
            'category' => 'damage_missing',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        // Post internal note
        $this->actingAs($admin, 'admin')->post(route('admin.support.reply', $ticket->id), [
            'message'     => 'CONFIDENTIAL: Customer has claimed damaged item 3 times this month. Verify packaging log.',
            'is_internal' => 1,
        ]);

        // Customer views ticket
        $customerView = $this->actingAs($user)->get(route('support.show', $ticket->ticket_number));
        $customerView->assertStatus(200);
        $customerView->assertDontSee('CONFIDENTIAL: Customer has claimed damaged item');

        // Admin views ticket
        $adminView = $this->actingAs($admin, 'admin')->get(route('admin.support.show', $ticket->id));
        $adminView->assertStatus(200);
        $adminView->assertSee('CONFIDENTIAL: Customer has claimed damaged item');
    }

    // ------------------------------------------------------------------
    // Delivery Partner Direct Call & Escalation Logging
    // ------------------------------------------------------------------

    public function test_admin_can_call_delivery_partner_and_log_call_notes(): void
    {
        $admin = $this->getSuperAdmin();
        $rider = $this->getRider();
        $user = $this->getCustomer();

        $order = $this->createTestOrder($user, [
            'delivery_partner_id' => $rider->id,
            'status'              => Order::STATUS_OUT_FOR_DELIVERY,
        ]);

        $ticket = SupportTicket::create([
            'order_id' => $order->id,
            'name'     => 'Customer Call Test',
            'email'    => 'calltest@example.com',
            'subject'  => 'Driver not answering doorstep doorbell',
            'category' => 'partner_issue',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        // Verify partner call card renders in admin view
        $viewResponse = $this->actingAs($admin, 'admin')->get(route('admin.support.show', $ticket->id));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($rider->name);
        $viewResponse->assertSee('Call Partner');
        $viewResponse->assertSee('tel:' . $rider->phone);

        // Admin logs phone call
        $callNotes = 'Spoke with rider Ramesh on cell phone. He confirmed he reached the building gate and is climbing stairs to 3rd floor.';

        $callResponse = $this->actingAs($admin, 'admin')->post(route('admin.support.call-partner', $ticket->id), [
            'call_notes' => $callNotes,
        ]);

        $callResponse->assertSessionHas('success');

        $ticket->refresh();
        $this->assertTrue($ticket->partner_contacted);
        $this->assertStringContainsString($callNotes, $ticket->partner_contact_notes);
        $this->assertEquals(SupportTicket::STATUS_IN_PROGRESS, $ticket->status);

        // Check timeline internal note was created
        $this->assertDatabaseHas('support_messages', [
            'support_ticket_id' => $ticket->id,
            'is_internal'       => true,
        ]);
    }

    public function test_admin_can_update_status_and_assign_staff(): void
    {
        $admin = $this->getSuperAdmin();
        $ticket = SupportTicket::create([
            'name'     => 'Status Test',
            'email'    => 'statustest@example.com',
            'subject'  => 'Return inquiry',
            'category' => 'return_refund',
            'status'   => SupportTicket::STATUS_OPEN,
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.support.status', $ticket->id), [
            'status'            => SupportTicket::STATUS_RESOLVED,
            'assigned_admin_id' => $admin->id,
        ]);

        $response->assertSessionHas('success');

        $ticket->refresh();
        $this->assertEquals(SupportTicket::STATUS_RESOLVED, $ticket->status);
        $this->assertEquals($admin->id, $ticket->assigned_admin_id);
        $this->assertNotNull($ticket->resolved_at);
    }

    public function test_customer_replying_to_resolved_ticket_reopens_it(): void
    {
        $user = $this->getCustomer();
        $ticket = SupportTicket::create([
            'user_id'  => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'subject'  => 'Issue with missing item',
            'category' => 'damage_missing',
            'status'   => SupportTicket::STATUS_RESOLVED,
        ]);

        $this->actingAs($user)->post(route('support.reply', $ticket->ticket_number), [
            'message' => 'Wait, I checked the box again and one item is still missing.',
        ]);

        $ticket->refresh();
        $this->assertEquals(SupportTicket::STATUS_OPEN, $ticket->status);
    }

    public function test_order_show_page_displays_support_assistance_and_chat_button(): void
    {
        $user = $this->getCustomer();
        $order = $this->createTestOrder($user, [
            'status' => Order::STATUS_DELIVERED,
        ]);

        $response = $this->actingAs($user)->get(route('orders.show', $order->order_number));

        $response->assertStatus(200);
        $response->assertSee('Need Help With This Order?');
        $response->assertSee(route('support.create', ['order_number' => $order->order_number]));
        $response->assertSee('tel:+916379644145');
    }
}

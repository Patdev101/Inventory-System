<?php

namespace Tests\Feature;

use App\Http\Controllers\StockMovementRequestController;
use App\Mail\PurchaseOrderMail;
use App\Models\Inventory;
use App\Models\PurchaseOrder;
use App\Models\StockMovementRequest;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

/**
 * Regression tests for the QA findings (IDs match the QA report).
 */
class QaRegressionTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private const TOKEN = 'qa-token-123';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.pos.api_token' => self::TOKEN]);
    }

    private function api(): array
    {
        return ['Authorization' => 'Bearer ' . self::TOKEN, 'Accept' => 'application/json'];
    }

    private function stocked(float $stock = 10, array $productAttrs = []): array
    {
        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $product = $this->makeProduct($company, $this->makeCategory(), $piece = $this->makeUnit(['name' => 'Piece']), $productAttrs);
        $unit = $this->makeProductUnit($product, $piece, 1, true);
        $inventory = $this->makeInventory($product, $location, $unit, $stock, $stock);

        return compact('company', 'location', 'product', 'unit', 'inventory');
    }

    /** Simulate a different browser: new session store, no cookies. */
    private function freshDevice(): void
    {
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        $this->app['auth']->forgetGuards();
        $this->defaultCookies = [];
    }

    private function loginOnNewDevice(string $email, string $password): string
    {
        $this->freshDevice();
        $this->post(route('login.store'), ['email' => $email, 'password' => $password]);
        $sessionId = $this->app['session']->getId();

        // A browser follows the post-login redirect straight away.
        $this->freshDevice();
        $this->withCookie(config('session.cookie'), $sessionId)->get('/dashboard')->assertOk();

        return $sessionId;
    }

    private function dashboardStatusFor(string $sessionId): int
    {
        $this->freshDevice();

        return $this->withCookie(config('session.cookie'), $sessionId)->get('/dashboard')->status();
    }

    // ------------------------------------------------------------- POS API

    public function test_qa001_api_rejects_missing_wrong_and_empty_tokens(): void
    {
        $this->getJson('/api/products')->assertStatus(401);
        $this->getJson('/api/products', ['Authorization' => 'Bearer wrong'])->assertStatus(401);

        config(['services.pos.api_token' => '']);
        $this->getJson('/api/products', ['Authorization' => 'Bearer '])->assertStatus(401);
    }

    public function test_qa002_api_stock_out_rejects_bad_quantities(): void
    {
        $s = $this->stocked(10);

        foreach ([0, -5, 'abc', '', null] as $bad) {
            $this->postJson('/api/inventory/out', [
                'product_id' => $s['product']->id, 'location_id' => $s['location']->id,
                'product_unit_id' => $s['unit']->id, 'quantity' => $bad,
            ], $this->api())->assertStatus(422);
        }

        $this->assertEquals(10, (float) $s['inventory']->fresh()->base_quantity);
    }

    public function test_qa004_api_rejects_absurd_quantity(): void
    {
        $s = $this->stocked(10);

        $this->postJson('/api/inventory/in', [
            'product_id' => $s['product']->id, 'location_id' => $s['location']->id,
            'product_unit_id' => $s['unit']->id, 'quantity' => 1e20,
        ], $this->api())->assertStatus(422)->assertJsonValidationErrors('quantity');

        $this->assertEquals(10, (float) $s['inventory']->fresh()->base_quantity);
    }

    public function test_qa006_api_products_does_not_expose_cost_or_margin(): void
    {
        $this->stocked(5, ['cost_price' => 40, 'selling_price' => 65]);

        $product = $this->getJson('/api/products', $this->api())->assertOk()->json(0);

        $this->assertEquals(65, (float) $product['selling_price']);
        foreach (['cost_price', 'markup_percentage', 'pricing_method', 'profit', 'profit_margin'] as $field) {
            $this->assertArrayNotHasKey($field, $product);
        }
    }

    public function test_qa007_api_rejects_stock_movement_at_deleted_location(): void
    {
        $s = $this->stocked(10);
        $s['location']->delete();

        $this->postJson('/api/inventory/out', [
            'product_id' => $s['product']->id, 'location_id' => $s['location']->id,
            'product_unit_id' => $s['unit']->id, 'quantity' => 1,
        ], $this->api())->assertStatus(422)->assertJsonValidationErrors('location_id');

        $this->assertEquals(10, (float) $s['inventory']->fresh()->base_quantity);
    }

    public function test_qa008_api_round_trips_unicode_names(): void
    {
        $name = 'Kape ☕ Barako – Niño 「特」';
        $this->stocked(5, ['name' => $name]);

        $this->assertSame($name, $this->getJson('/api/products', $this->api())->json('0.name'));
    }

    // ------------------------------------------------------ stock editing

    public function test_qa010_a_stock_request_cannot_be_approved_twice(): void
    {
        $s = $this->stocked(10);
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $req = StockMovementRequest::create([
            'inventory_id' => $s['inventory']->id, 'product_id' => $s['product']->id,
            'location_id' => $s['location']->id, 'product_unit_id' => $s['unit']->id,
            'type' => 'in', 'quantity' => 5, 'status' => StockMovementRequest::STATUS_PENDING,
            'requested_by' => $this->makeUser(User::ROLE_STAFF)->id,
        ]);

        // Both approvals loaded the request before either one saved.
        $seenByA = StockMovementRequest::find($req->id);
        $seenByB = StockMovementRequest::find($req->id);

        $http = Request::create('/', 'PATCH');
        $http->setUserResolver(fn () => $manager);
        $controller = $this->app->make(StockMovementRequestController::class);

        $controller->approve($http, $seenByA);
        $controller->approve($http, $seenByB);

        $this->assertEquals(15, (float) $s['inventory']->fresh()->base_quantity);
    }

    public function test_qa011_edit_cannot_apply_another_products_unit(): void
    {
        $a = $this->stocked(10);
        $productB = $this->makeProduct($a['company'], $this->makeCategory(), $this->makeUnit(), ['name' => 'Product B', 'sku' => 'B-1']);
        $bCase = $this->makeProductUnit($productB, $this->makeUnit(['name' => 'Case']), 12, true);

        $this->actingAs($this->makeUser(User::ROLE_MANAGER))
            ->put(route('inventories.update', $a['inventory']), [
                'product_id' => $productB->id, 'location_id' => $a['location']->id,
                'product_unit_id' => $bCase->id, 'movement_type' => 'in', 'quantity' => 1,
            ])->assertSessionHasErrors('product_unit_id');

        $this->assertEquals(10, (float) $a['inventory']->fresh()->base_quantity);
    }

    public function test_qa012_deactivated_product_cannot_receive_stock_through_another_product(): void
    {
        $a = $this->stocked(10, ['is_active' => false]);
        $b = $this->makeProduct($a['company'], $this->makeCategory(), $piece = $this->makeUnit(), ['name' => 'Active B', 'sku' => 'B-2']);
        $bUnit = $this->makeProductUnit($b, $piece, 1, true);

        $this->actingAs($this->makeUser(User::ROLE_MANAGER))
            ->put(route('inventories.update', $a['inventory']), [
                'product_id' => $b->id, 'location_id' => $a['location']->id,
                'product_unit_id' => $bUnit->id, 'movement_type' => 'in', 'quantity' => 5,
            ]);

        $this->assertEquals(10, (float) $a['inventory']->fresh()->base_quantity);
    }

    public function test_qa012b_edit_form_location_is_fixed_to_the_stock_record(): void
    {
        $a = $this->stocked(10);
        $warehouseB = $this->makeLocation($a['company'], ['name' => 'Warehouse B']);
        $manager = $this->makeUser(User::ROLE_MANAGER);

        $this->actingAs($manager)->get(route('inventories.edit', $a['inventory']))
            ->assertOk()->assertDontSee('Warehouse B');

        $this->actingAs($manager)->put(route('inventories.update', $a['inventory']), [
            'location_id' => $warehouseB->id, 'product_unit_id' => $a['unit']->id,
            'movement_type' => 'in', 'quantity' => 5,
        ]);

        $this->assertEquals(15, (float) $a['inventory']->fresh()->base_quantity);
        $this->assertFalse(Inventory::where('location_id', $warehouseB->id)->exists());
    }

    public function test_qa013_po_receive_rejects_duplicate_lines_over_ordered_quantity(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $po = $this->orderedPurchaseOrder($admin, 10);
        $item = $po->items()->first();

        $this->actingAs($admin)->patch(route('purchase-orders.receive', $po), [
            'lines' => [
                ['purchase_order_item_id' => $item->id, 'quantity_received' => 6],
                ['purchase_order_item_id' => $item->id, 'quantity_received' => 6],
            ],
        ]);

        $this->assertLessThanOrEqual(10, (float) $item->fresh()->quantity_received);
    }

    // ---------------------------------------------------- sessions/auth

    public function test_qa014_email_password_reset_logs_out_other_sessions(): void
    {
        config(['session.driver' => 'file']);
        $user = $this->makeUser(User::ROLE_STAFF, ['email' => 'victim@example.com', 'password' => Hash::make('OldPassword1!')]);
        $oldSession = $this->loginOnNewDevice('victim@example.com', 'OldPassword1!');

        $this->freshDevice();
        $this->post(route('password.update'), [
            'token' => Password::broker('users')->createToken($user), 'email' => 'victim@example.com',
            'password' => 'NewPassword1!', 'password_confirmation' => 'NewPassword1!',
        ])->assertRedirect(route('login'));

        $this->assertNotSame(200, $this->dashboardStatusFor($oldSession));
    }

    public function test_qa015_admin_password_reset_logs_out_the_users_sessions(): void
    {
        config(['session.driver' => 'file']);
        $admin = $this->makeUser(User::ROLE_ADMIN);
        $target = $this->makeUser(User::ROLE_STAFF, ['email' => 'staff1@example.com', 'password' => Hash::make('OldPassword1!')]);
        $oldSession = $this->loginOnNewDevice('staff1@example.com', 'OldPassword1!');

        $this->freshDevice();
        $this->actingAs($admin)->post(route('users.reset-password.store', $target), [
            'password' => 'AdminSet1!pass', 'password_confirmation' => 'AdminSet1!pass',
        ]);

        $this->assertNotSame(200, $this->dashboardStatusFor($oldSession));
    }

    public function test_qa015b_changing_your_own_password_keeps_you_logged_in(): void
    {
        config(['session.driver' => 'file']);
        $this->makeUser(User::ROLE_STAFF, ['email' => 'self@example.com', 'password' => Hash::make('OldPassword1!')]);
        $session = $this->loginOnNewDevice('self@example.com', 'OldPassword1!');

        $this->freshDevice();
        $this->withCookie(config('session.cookie'), $session)->put(route('account.password.update'), [
            'current_password' => 'OldPassword1!',
            'password' => 'NewPassword1!x', 'password_confirmation' => 'NewPassword1!x',
        ])->assertSessionHasNoErrors();

        $this->assertSame(200, $this->dashboardStatusFor($session));
    }

    public function test_qa016_forgot_password_is_rate_limited(): void
    {
        $statuses = [];
        for ($i = 0; $i < 8; $i++) {
            $statuses[] = $this->post(route('password.email'), ['email' => "probe{$i}@example.com"])->status();
        }

        $this->assertContains(429, $statuses);
    }

    public function test_qa017_role_boundaries_hold(): void
    {
        $s = $this->stocked(5);
        $staff = $this->makeUser(User::ROLE_STAFF);
        $manager = $this->makeUser(User::ROLE_MANAGER);
        $admin = $this->makeUser(User::ROLE_ADMIN);

        $this->actingAs($staff)->delete(route('inventories.destroy', $s['inventory']))->assertForbidden();
        $this->actingAs($staff)->get('/users')->assertForbidden();
        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Sneaky', 'email' => 'sneaky@example.com',
            'password' => 'Password1!x', 'password_confirmation' => 'Password1!x', 'role' => 'admin',
        ])->assertSessionHasErrors('role');
        $this->actingAs($manager)->post(route('users.reset-password.store', $admin), [
            'password' => 'Password1!x', 'password_confirmation' => 'Password1!x',
        ])->assertForbidden();
    }

    // -------------------------------------------------- email and export

    public function test_qa018_staff_cannot_email_suppliers_and_managers_are_rate_limited(): void
    {
        Mail::fake();
        $po = $this->orderedPurchaseOrder($this->makeUser(User::ROLE_ADMIN), 5);
        $payload = ['to_email' => 'anyone@outside.test', 'subject' => 'Subject', 'body' => 'Body'];

        $this->actingAs($this->makeUser(User::ROLE_STAFF))
            ->post(route('purchase-orders.email.send', $po), $payload)->assertForbidden();

        $manager = $this->makeUser(User::ROLE_MANAGER);
        for ($i = 0; $i < 12; $i++) {
            $this->actingAs($manager)->post(route('purchase-orders.email.send', $po), $payload);
        }

        $this->assertCount(10, Mail::sent(PurchaseOrderMail::class));
    }

    public function test_qa019_csv_export_neutralises_formula_cells(): void
    {
        $this->stocked(0, ['name' => '=HYPERLINK("http://evil.test","Click")']);

        $csv = $this->actingAs($this->makeUser(User::ROLE_ADMIN))
            ->get(route('reports.low-stock.export'))->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDoesNotMatchRegularExpression('/(^|,)"?=HYPERLINK/m', $csv);
    }

    public function test_transfer_receiver_role_comes_from_the_receiver_account(): void
    {
        $s = $this->stocked(10);
        $destination = $this->makeLocation($s['company'], ['name' => 'Branch 2']);
        $receiver = $this->makeUser(User::ROLE_STAFF);

        $this->actingAs($this->makeUser(User::ROLE_MANAGER))->post(route('inventory-transfers.store'), [
            'destination_location_id' => $destination->id,
            'receiver_id' => $receiver->id,
            'receiver_role' => 'admin',
            'items' => [['source_inventory_id' => $s['inventory']->id, 'product_unit_id' => $s['unit']->id, 'quantity' => 2]],
        ])->assertSessionHasNoErrors();

        $this->assertSame('staff', \App\Models\InventoryTransfer::latest('id')->value('receiver_role'));
    }

    private function orderedPurchaseOrder(User $creator, float $qty): PurchaseOrder
    {
        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $this->makeCategory(), $unit, ['sku' => 'PO-' . uniqid()]);
        $this->makeProductUnit($product, $unit);
        $supplier = Supplier::create(['company_id' => $company->id, 'name' => 'Sup', 'email' => 'sup@example.com', 'is_active' => true]);

        $this->actingAs($creator)->post(route('purchase-orders.store'), [
            'supplier_id' => $supplier->id, 'location_id' => $location->id,
            'items' => [[
                'product_id' => $product->id, 'product_unit_id' => $product->productUnits()->first()->id,
                'quantity_ordered' => $qty, 'unit_price' => 5,
            ]],
        ]);

        $po = PurchaseOrder::query()->latest('id')->firstOrFail();
        $po->update(['status' => PurchaseOrder::STATUS_ORDERED]);

        return $po;
    }
}

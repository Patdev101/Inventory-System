<?php

namespace Tests\Feature;

use App\Notifications\StockAlertNotification;
use App\Services\StockAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\CreatesInventoryFixtures;
use Tests\TestCase;

class StockAlertServiceTest extends TestCase
{
    use RefreshDatabase;
    use CreatesInventoryFixtures;

    private function inventoryWithReorderPoint(float $baseQuantity, float $reorderPoint)
    {
        $company = $this->makeCompany();
        $location = $this->makeLocation($company);
        $category = $this->makeCategory();
        $unit = $this->makeUnit();
        $product = $this->makeProduct($company, $category, $unit, ['reorder_point' => $reorderPoint]);
        $productUnit = $this->makeProductUnit($product, $unit);

        return $this->makeInventory($product, $location, $productUnit, $baseQuantity, $baseQuantity);
    }

    public function test_synchronize_opens_an_alert_for_out_of_stock_inventory(): void
    {
        $inventory = $this->inventoryWithReorderPoint(0, 100);

        (new StockAlertService())->synchronize();

        $this->assertDatabaseHas('stock_alerts', [
            'inventory_id' => $inventory->id,
            'severity' => 'out_of_stock',
            'status' => 'open',
        ]);
    }

    public function test_synchronize_does_not_duplicate_alerts_for_unchanged_severity(): void
    {
        $this->inventoryWithReorderPoint(0, 100);

        $service = new StockAlertService();
        $service->synchronize();
        $service->synchronize();

        $this->assertDatabaseCount('stock_alerts', 1);
    }

    public function test_synchronize_resolves_the_previous_alert_when_severity_changes(): void
    {
        $inventory = $this->inventoryWithReorderPoint(0, 100);

        $service = new StockAlertService();
        $service->synchronize();

        $inventory->update(['base_quantity' => 60]);
        $service->synchronize();

        $this->assertDatabaseHas('stock_alerts', [
            'inventory_id' => $inventory->id,
            'severity' => 'out_of_stock',
            'status' => 'resolved',
        ]);

        $this->assertDatabaseHas('stock_alerts', [
            'inventory_id' => $inventory->id,
            'severity' => 'low',
            'status' => 'open',
        ]);
    }

    public function test_synchronize_resolves_the_alert_when_stock_returns_to_normal(): void
    {
        $inventory = $this->inventoryWithReorderPoint(0, 100);

        $service = new StockAlertService();
        $service->synchronize();

        $inventory->update(['base_quantity' => 500]);
        $service->synchronize();

        $this->assertDatabaseHas('stock_alerts', [
            'inventory_id' => $inventory->id,
            'severity' => 'out_of_stock',
            'status' => 'resolved',
        ]);

        $this->assertDatabaseCount('stock_alerts', 1);
    }

    public function test_synchronize_sends_notifications_when_requested(): void
    {
        Notification::fake();

        $this->makeUser(\App\Models\User::ROLE_ADMIN);
        $this->inventoryWithReorderPoint(0, 100);

        config(['stockalerts.email_enabled' => true]);

        (new StockAlertService())->synchronize(notify: true);

        Notification::assertSentTo(
            \App\Models\User::all(),
            StockAlertNotification::class
        );
    }

    public function test_acknowledge_only_affects_open_alerts(): void
    {
        $inventory = $this->inventoryWithReorderPoint(0, 100);
        $service = new StockAlertService();
        $service->synchronize();

        $alert = \App\Models\StockAlert::where('inventory_id', $inventory->id)->first();

        $admin = $this->makeUser(\App\Models\User::ROLE_ADMIN);

        $service->acknowledge($alert, $admin->id);

        $this->assertSame('acknowledged', $alert->fresh()->status);
        $this->assertSame($admin->id, $alert->fresh()->acknowledged_by);
    }

    public function test_resolve_marks_active_alerts_resolved(): void
    {
        $inventory = $this->inventoryWithReorderPoint(0, 100);
        $service = new StockAlertService();
        $service->synchronize();

        $alert = \App\Models\StockAlert::where('inventory_id', $inventory->id)->first();

        $service->resolve($alert);

        $this->assertSame('resolved', $alert->fresh()->status);
        $this->assertNotNull($alert->fresh()->resolved_at);
    }
}

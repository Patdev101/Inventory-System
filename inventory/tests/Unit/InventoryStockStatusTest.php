<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use PHPUnit\Framework\TestCase;

class InventoryStockStatusTest extends TestCase
{
    public function test_positive_stock_above_reorder_point_is_in_stock(): void
    {
        $this->assertSame('In Stock', $this->inventory(200, 100)->getStockStatus());
    }

    public function test_stock_at_reorder_point_is_low_stock(): void
    {
        $this->assertSame('Low Stock', $this->inventory(80, 100)->getStockStatus());
    }

    public function test_stock_at_half_reorder_point_is_critical(): void
    {
        $this->assertSame('Critical', $this->inventory(50, 100)->getStockStatus());
    }

    public function test_zero_stock_is_out_of_stock(): void
    {
        $this->assertSame('Out of Stock', $this->inventory(0, 100)->getStockStatus());
    }

    public function test_zero_reorder_point_does_not_alert_positive_stock(): void
    {
        $this->assertSame('In Stock', $this->inventory(3, 0)->getStockStatus());
    }

    private function inventory(float $baseQuantity, float $reorderPoint): Inventory
    {
        $product = new Product(['reorder_point' => $reorderPoint]);
        $inventory = new Inventory(['base_quantity' => $baseQuantity]);
        $inventory->setRelation('product', $product);

        return $inventory;
    }
}

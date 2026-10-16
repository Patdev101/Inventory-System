<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Works out which deliveries of an expiring product are still on the shelf.
 *
 * Stock is kept as one total per product per location, and each stock-in
 * remembers its expiry date. Assuming the oldest stock leaves first, the
 * stock on hand is made of the most recent deliveries: walk the stock-ins
 * from newest to oldest until they add up to what is on hand. Those are the
 * batches still there, with how much of each is left.
 */
class ExpiryService
{
    /**
     * Batches still on hand, oldest first:
     * [['expiry_date' => Carbon|null, 'base_quantity' => float, 'received_at' => Carbon]].
     */
    public function remainingBatches(Inventory $inventory, ?float $onHand = null): Collection
    {
        $onHand ??= (float) $inventory->base_quantity;

        if ($onHand <= 0) {
            return collect();
        }

        $batches = collect();
        $left = $onHand;

        InventoryTransaction::withoutGlobalScopes()
            ->where('inventory_id', $inventory->id)
            ->where('type', 'in')
            ->orderByDesc('id')
            ->select(['id', 'base_quantity', 'expiry_date', 'created_at'])
            ->lazy(200)
            ->each(function (InventoryTransaction $in) use (&$left, $batches) {
                $take = min($left, (float) $in->base_quantity);

                if ($take > 0) {
                    $batches->push([
                        'expiry_date' => $in->expiry_date,
                        'base_quantity' => $take,
                        'received_at' => $in->created_at,
                    ]);
                }

                $left -= $take;

                return $left > 0.0000001;
            });

        return $batches->reverse()->values();
    }

    /**
     * The earliest expiry among the stock about to leave (the oldest batches).
     * Used so a transfer carries an expiry date to its destination.
     */
    public function expiryOfStockLeaving(Inventory $inventory, float $baseQuantity): ?string
    {
        $left = $baseQuantity;
        $earliest = null;

        foreach ($this->remainingBatches($inventory) as $batch) {
            if ($left <= 0.0000001) {
                break;
            }

            if ($batch['expiry_date'] && (! $earliest || $batch['expiry_date']->lt($earliest))) {
                $earliest = $batch['expiry_date'];
            }

            $left -= $batch['base_quantity'];
        }

        return $earliest?->toDateString();
    }

    /**
     * Every batch with an expiry date that has expired or expires within
     * the warning window, soonest first. Respects the signed-in person's
     * location access (Inventory's global scope).
     */
    public function expiringBatches(?int $withinDays = null): Collection
    {
        $withinDays ??= (int) config('stockalerts.expiry_warning_days', 30);
        $today = Carbon::today();
        $limit = $today->copy()->addDays($withinDays);

        return Inventory::query()
            ->with(['product.baseUnit', 'location'])
            ->where('base_quantity', '>', 0)
            // Any stock record that has ever received stock with an expiry date.
            ->whereExists(fn ($query) => $query->selectRaw('1')
                ->from('inventory_transactions')
                ->whereColumn('inventory_transactions.inventory_id', 'inventories.id')
                ->whereNotNull('inventory_transactions.expiry_date'))
            ->get()
            ->flatMap(function (Inventory $inventory) use ($today, $limit) {
                return $this->remainingBatches($inventory)
                    ->filter(fn ($batch) => $batch['expiry_date'] && $batch['expiry_date']->lte($limit))
                    ->map(fn ($batch) => [
                        'inventory' => $inventory,
                        'expiry_date' => $batch['expiry_date'],
                        'base_quantity' => $batch['base_quantity'],
                        'days_left' => (int) $today->diffInDays($batch['expiry_date'], false),
                        'expired' => $batch['expiry_date']->lt($today),
                    ]);
            })
            ->sortBy(fn ($row) => $row['expiry_date']->timestamp)
            ->values();
    }
}

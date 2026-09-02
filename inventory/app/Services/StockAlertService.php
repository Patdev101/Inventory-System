<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\StockAlertNotification;

class StockAlertService
{
    public function synchronize(bool $notify = false): int
    {
        $created = 0;

        Inventory::with('product')->chunkById(100, function ($inventories) use (&$created, $notify) {
            foreach ($inventories as $inventory) {
                $severity = $this->severityFor($inventory);

                $activeAlert = StockAlert::active()
                    ->where('inventory_id', $inventory->id)
                    ->latest('id')
                    ->first();

                if ($severity === null) {
                    if ($activeAlert) {
                        $activeAlert->update([
                            'status' => 'resolved',
                            'resolved_at' => now(),
                        ]);
                    }

                    continue;
                }

                if ($activeAlert && $activeAlert->severity === $severity) {
                    $activeAlert->update([
                        'base_quantity' => $inventory->getBaseQuantityValue(),
                        'reorder_point' => $inventory->getReorderPointValue(),
                    ]);

                    continue;
                }

                if ($activeAlert) {
                    $activeAlert->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ]);
                }

                $alert = StockAlert::create([
                    'inventory_id' => $inventory->id,
                    'severity' => $severity,
                    'status' => 'open',
                    'base_quantity' => $inventory->getBaseQuantityValue(),
                    'reorder_point' => $inventory->getReorderPointValue(),
                ]);

                $created++;

                if ($notify && config('stockalerts.email_enabled')) {
                    User::query()->whereNotNull('email')->each(function (User $user) use ($alert) {
                        $user->notify(new StockAlertNotification($alert));
                    });
                }
            }
        });

        return $created;
    }

    public function acknowledge(StockAlert $alert, ?int $userId = null): void
    {
        if ($alert->status !== 'open') {
            return;
        }

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);
    }

    public function resolve(StockAlert $alert): void
    {
        if (!$alert->isActive()) {
            return;
        }

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    private function severityFor(Inventory $inventory): ?string
    {
        return match ($inventory->getStockStatus()) {
            'Out of Stock' => 'out_of_stock',
            'Critical' => 'critical',
            'Low Stock' => 'low',
            default => null,
        };
    }
}

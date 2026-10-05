<?php

namespace App\Models\Scopes;

use App\Support\UserAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Limits a model's rows to what the signed-in staff or manager may see.
 * Does nothing for admins, for the POS API (no signed-in user) and for
 * console commands. Because it is a global scope it also covers lists,
 * reports, the dashboard counters and route-model binding (an
 * out-of-scope id is a 404).
 *
 * Modes:
 *   location    rows whose location_id is the user's location
 *   company     rows whose company_id is the user's company
 *   self        the user's own company row (id)
 *   inventory   rows tied to an inventory record at the user's location
 *   transfer    transfers going out of or into the user's location
 *   request     stock requests for, or into, the user's location
 */
class UserAccessScope implements Scope
{
    public function __construct(private readonly string $mode)
    {
    }

    public function apply(Builder $builder, Model $model): void
    {
        if (!UserAccess::restricted()) {
            return;
        }

        $locationId = UserAccess::locationId();
        $companyId = UserAccess::companyId();

        // Assigned to nothing: sees nothing.
        if ($locationId === null || $companyId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        match ($this->mode) {
            'location' => $builder->where($model->qualifyColumn('location_id'), $locationId),
            'company' => $builder->where($model->qualifyColumn('company_id'), $companyId),
            'self' => $builder->where($model->qualifyColumn('id'), $companyId),
            'inventory' => $builder->whereIn(
                $model->qualifyColumn('inventory_id'),
                fn ($q) => $q->select('id')->from('inventories')->where('location_id', $locationId)
            ),
            'transfer' => $builder->where(function ($q) use ($model, $locationId) {
                $atLocation = fn ($sub) => $sub->select('id')->from('inventories')->where('location_id', $locationId);

                $q->whereIn($model->qualifyColumn('source_inventory_id'), $atLocation)
                    ->orWhereIn($model->qualifyColumn('destination_inventory_id'), $atLocation);
            }),
            'request' => $builder->where(function ($q) use ($model, $locationId) {
                $q->where($model->qualifyColumn('location_id'), $locationId)
                    ->orWhere($model->qualifyColumn('destination_location_id'), $locationId);
            }),
        };
    }
}

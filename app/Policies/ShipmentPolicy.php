<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir', 'courier', 'manager', 'customer'], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->role === 'customer') {
            return false;
        }

        if ($user->role === 'courier') {
            return (int) $shipment->courier_id === (int) $user->id;
        }

        if ($user->role === 'manager' || $user->role === 'kasir') {
            return (int) $shipment->branch_id === (int) $user->branch_id;
        }

        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir', 'manager'], true);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shipment $shipment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'courier') {
            return (int) $shipment->courier_id === (int) $user->id;
        }

        return in_array($user->role, ['kasir', 'manager'], true)
            && (int) $shipment->branch_id === (int) $user->branch_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->role === 'admin' || $user->role === 'manager';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Shipment $shipment): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shipment $shipment): bool
    {
        return $user->role === 'admin';
    }
}

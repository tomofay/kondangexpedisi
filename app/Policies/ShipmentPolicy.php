<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

class ShipmentPolicy
{
    /**
     * Admin (kantor pusat) bisa lihat semua data, tapi TIDAK bisa create/update/delete.
     * Manager bisa CRUD hanya di cabangnya.
     * Kasir bisa create dan view di cabangnya, tapi update butuh approval manager.
     */

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir', 'courier', 'manager'], true);
    }

    public function view(User $user, Shipment $shipment): bool
    {
        if ($user->role === 'admin') {
            return true; // Admin bisa lihat semua
        }

        if ($user->role === 'courier') {
            return (int) $shipment->courier_id === (int) $user->id;
        }

        // Manager & kasir hanya bisa lihat shipment di cabangnya
        if (in_array($user->role, ['manager', 'kasir'], true)) {
            return (int) $shipment->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Admin TIDAK bisa create — dia admin pusat
        // Manager & kasir bisa create di cabangnya
        return in_array($user->role, ['manager', 'kasir'], true);
    }

    public function update(User $user, Shipment $shipment): bool
    {
        // Admin TIDAK bisa update shipment
        if ($user->role === 'admin') {
            return false;
        }

        // Manager bisa update langsung di cabangnya
        if ($user->role === 'manager') {
            return (int) $shipment->branch_id === (int) $user->branch_id;
        }

        // Kasir bisa "update" tapi controller akan redirect ke approval flow
        if ($user->role === 'kasir') {
            return (int) $shipment->branch_id === (int) $user->branch_id;
        }

        // Courier bisa update status via mobile API
        if ($user->role === 'courier') {
            return (int) $shipment->courier_id === (int) $user->id;
        }

        return false;
    }

    public function transition(User $user, Shipment $shipment): bool
    {
        // Admin bisa melakukan transition (terutama untuk force/override)
        if ($user->role === 'admin') {
            return true;
        }

        // Selebihnya sama dengan update permission
        return $this->update($user, $shipment);
    }

    public function requestPricingOverride(User $user, Shipment $shipment): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $this->update($user, $shipment);
    }

    public function approvePricingOverride(User $user, Shipment $shipment): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        // Hanya manager di cabangnya yang bisa delete
        if ($user->role === 'manager') {
            return (int) $shipment->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public function restore(User $user, Shipment $shipment): bool
    {
        return $user->role === 'manager'
            && (int) $shipment->branch_id === (int) $user->branch_id;
    }

    public function forceDelete(User $user, Shipment $shipment): bool
    {
        return false; // Tidak ada yang boleh force delete
    }
}

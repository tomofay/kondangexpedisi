<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Admin (kantor pusat) hanya bisa lihat data — TIDAK bisa create/update/delete.
     * Manager bisa CRUD di cabangnya.
     * Kasir bisa create & view di cabangnya, tapi update butuh approval manager.
     */

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'kasir', 'manager'], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === 'admin') {
            return true; // Admin bisa lihat semua
        }

        // Manager & kasir hanya bisa lihat payment di cabangnya
        if (in_array($user->role, ['manager', 'kasir'], true)) {
            return (int) $payment->shipment?->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Admin TIDAK bisa create payment
        // Manager & kasir bisa create
        return in_array($user->role, ['manager', 'kasir'], true);
    }

    public function update(User $user, Payment $payment): bool
    {
        // Admin TIDAK bisa update payment
        if ($user->role === 'admin') {
            return false;
        }

        // Manager bisa update langsung di cabangnya
        if ($user->role === 'manager') {
            return (int) $payment->shipment?->branch_id === (int) $user->branch_id;
        }

        // Kasir bisa "update" tapi controller akan redirect ke approval flow
        if ($user->role === 'kasir') {
            return (int) $payment->shipment?->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Hanya manager di cabangnya
        if ($user->role === 'manager') {
            return (int) $payment->shipment?->branch_id === (int) $user->branch_id;
        }

        return false;
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->role === 'manager'
            && (int) $payment->shipment?->branch_id === (int) $user->branch_id;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}

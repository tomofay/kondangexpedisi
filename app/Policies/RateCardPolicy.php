<?php

namespace App\Policies;

use App\Models\RateCard;
use App\Models\User;

class RateCardPolicy
{
    /**
     * Rate card: admin bisa direct CRUD.
     * Manager bisa view, tapi create/update harus via approval ke admin.
     * Kasir hanya bisa view.
     */

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'kasir'], true);
    }

    public function view(User $user, RateCard $rateCard): bool
    {
        return in_array($user->role, ['admin', 'manager', 'kasir'], true);
    }

    public function create(User $user): bool
    {
        // Admin direct, manager via approval (handled in controller)
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function update(User $user, RateCard $rateCard): bool
    {
        // Admin direct, manager via approval (handled in controller)
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function delete(User $user, RateCard $rateCard): bool
    {
        return in_array($user->role, ['admin', 'manager'], true);
    }

    public function restore(User $user, RateCard $rateCard): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, RateCard $rateCard): bool
    {
        return $user->role === 'admin';
    }
}

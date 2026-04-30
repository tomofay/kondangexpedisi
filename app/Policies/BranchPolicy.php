<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * Admin bisa lihat & kelola semua cabang.
     * Manager hanya bisa lihat & update cabangnya sendiri.
     * Kasir hanya bisa lihat cabangnya sendiri.
     */

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'manager', 'kasir'], true);
    }

    public function view(User $user, Branch $branch): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Manager & kasir hanya cabangnya sendiri
        return in_array($user->role, ['manager', 'kasir'], true)
            && (int) $user->branch_id === (int) $branch->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Branch $branch): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Manager bisa update cabangnya sendiri
        return $user->role === 'manager'
            && (int) $user->branch_id === (int) $branch->id;
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->role === 'admin';
    }

    public function restore(User $user, Branch $branch): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Branch $branch): bool
    {
        return $user->role === 'admin';
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'photo',
        'role',
        'branch_id',
        'is_active',
        'password',
        'last_login_at',
        'last_activity_at',
        'last_login_ip',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'permissions' => 'array',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    public function assignedShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'courier_id');
    }

    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(AdminTask::class, 'created_by');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(AdminTask::class, 'assigned_to');
    }

    public function createdApprovals(): HasMany
    {
        return $this->hasMany(RateCardApproval::class, 'requested_by');
    }

    public function recordLogin(string $ip = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_activity_at' => now(),
            'last_login_ip' => $ip,
        ]);
    }

    public function recordActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function getPermissions(): array
    {
        return $this->permissions ?? $this->getDefaultPermissionsByRole();
    }

    public function getDefaultPermissionsByRole(): array
    {
        return match ($this->role) {
            'admin' => [
                'view_dashboard', 'manage_users', 'approve_rate_cards', 'manage_branches',
                'view_reports', 'export_data', 'manage_roles', 'audit_logs', 'manage_vehicles',
                'manage_zones', 'manage_shipments', 'process_payments', 'broadcast_messages',
            ],
            'manager' => [
                'view_dashboard', 'view_team_performance', 'assign_shipments',
                'manage_couriers', 'view_reports', 'export_data',
            ],
            'kasir' => [
                'view_dashboard', 'process_payments', 'view_shipments', 'print_labels',
            ],
            'courier' => [
                'view_assigned_shipments', 'update_shipment_status', 'view_earnings',
            ],
            'customer' => [
                'view_own_shipments', 'track_shipments', 'manage_profile',
            ],
            default => [],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions());
    }
}

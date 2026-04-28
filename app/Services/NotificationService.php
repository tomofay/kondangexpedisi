<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\Shipment;
use App\Models\User;

class NotificationService
{
    public function notifyAdmins(string $type, string $title, string $message, array $data = [], string $priority = 'high'): void
    {
        $adminIds = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            AppNotification::query()->create([
                'recipient_user_id' => $adminId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'priority' => $priority,
                'data' => $data,
            ]);
        }
    }

    public function notifyUser(User $recipient, string $type, string $title, string $message, array $data = [], string $priority = 'medium'): AppNotification
    {
        return AppNotification::query()->create([
            'recipient_user_id' => $recipient->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority,
            'data' => $data,
        ]);
    }

    public function notifyShipmentCustomer(Shipment $shipment, string $type, string $title, string $message, array $data = [], string $priority = 'medium'): ?AppNotification
    {
        $shipment->loadMissing('customer.user');
        $customerUser = $shipment->customer?->user;

        if (! $customerUser || $customerUser->role !== 'customer') {
            return null;
        }

        return $this->notifyUser($customerUser, $type, $title, $message, $data, $priority);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\DashboardAnalyticsService;
use Illuminate\Support\Facades\Cache;

class DashboardDataController extends Controller
{
    protected DashboardAnalyticsService $analyticsService;

    public function __construct(DashboardAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user, 401);

        return response()->json(match ($user->role) {
            'admin' => Cache::remember('dashboard_admin_payload', 60, fn() => $this->analyticsService->adminPayload($user)),
            'manager' => Cache::remember('dashboard_manager_payload_branch_' . $user->branch_id, 60, fn() => $this->analyticsService->staffPayload($user)),
            'kasir' => Cache::remember('dashboard_kasir_payload_branch_' . $user->branch_id, 60, fn() => $this->analyticsService->staffPayload($user)),
            default => ['message' => 'Role tidak dikenali.'],
        });
    }
}

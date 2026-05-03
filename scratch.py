import os
import re

controller_path = 'app/Http/Controllers/DashboardDataController.php'
service_path = 'app/Services/DashboardAnalyticsService.php'

with open(controller_path, 'r', encoding='utf-8') as f:
    content = f.read()

imports = r"""<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\ShipmentTracking;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\ShipmentStatus;
use App\Models\RateCard;
use App\Models\RateCardApproval;
use App\Models\IntegrationStatus;
use App\Models\LandingPageContent;
use App\Models\ErrorLog;
use App\Models\AdminTask;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardAnalyticsService
{
"""

admin_payload_start = content.find('    private function adminPayload(User $user): array')

methods_content = content[admin_payload_start:content.rfind('}')]
methods_content = methods_content.replace('    private function ', '    public function ')

service_content = imports + methods_content + '\n}\n'

with open(service_path, 'w', encoding='utf-8') as f:
    f.write(service_content)

new_controller = r"""<?php

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
            'manager' => Cache::remember('dashboard_staff_payload_branch_' . $user->branch_id, 60, fn() => $this->analyticsService->staffPayload($user)),
            'kasir' => Cache::remember('dashboard_staff_payload_branch_' . $user->branch_id, 60, fn() => $this->analyticsService->staffPayload($user)),
            default => ['message' => 'Role tidak dikenali.'],
        });
    }
}
"""

with open(controller_path, 'w', encoding='utf-8') as f:
    f.write(new_controller)

print('Refactoring complete')

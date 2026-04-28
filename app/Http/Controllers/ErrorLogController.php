<?php

namespace App\Http\Controllers;

use App\Jobs\RetryMidtransCallbackJob;
use App\Jobs\RetryTrackingSyncJob;
use App\Models\AdminTask;
use App\Models\ErrorLog;
use App\Services\OperationalIssueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ErrorLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ErrorLog::query()->with(['user'])->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->string('module'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity'));
        }

        if ($request->filled('resolved')) {
            $resolved = filter_var($request->input('resolved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($resolved === true) {
                $query->whereNotNull('resolved_at');
            } elseif ($resolved === false) {
                $query->whereNull('resolved_at');
            }
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('message', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('error_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from'));
        }

        if ($request->filled('until')) {
            $query->whereDate('created_at', '<=', $request->string('until'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(ErrorLog $errorLog): JsonResponse
    {
        return response()->json($errorLog->load(['user']));
    }

    public function unresolvedQueue(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 25), 5), 100);

        $query = ErrorLog::query()
            ->with('user')
            ->whereNull('resolved_at')
            ->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->string('module'));
        }

        $paginator = $query->paginate($perPage);
        $summary = $this->buildUnresolvedQueueSummary();

        $items = collect($paginator->items())->map(function (ErrorLog $errorLog) {
            $retryType = $this->resolveRetryType($errorLog);

            return [
                'id' => $errorLog->id,
                'module' => $errorLog->module,
                'error_type' => $errorLog->error_type,
                'severity' => $errorLog->severity,
                'message' => $errorLog->message,
                'context' => $errorLog->context,
                'created_at' => $errorLog->created_at,
                'retry' => [
                    'capable' => $retryType !== null,
                    'type' => $retryType,
                    'endpoint' => $retryType ? route('error-logs.retry', $errorLog) : null,
                ],
                'actions' => [
                    'resolve_endpoint' => route('error-logs.resolve', $errorLog),
                    'detail_endpoint' => route('error-logs.show', $errorLog),
                ],
                'resolved_at' => $errorLog->resolved_at,
                'user' => $errorLog->user,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_next_page' => $paginator->hasMorePages(),
            ],
            'summary' => $summary,
            'items' => $items,
        ]);
    }

    public function deadLetterMonitoring(): JsonResponse
    {
        if (! Schema::hasTable('failed_jobs')) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_failed_jobs' => 0,
                    'retry_jobs' => [
                        'midtrans_callback' => [
                            'job_class' => RetryMidtransCallbackJob::class,
                            'failed_count' => 0,
                        ],
                        'tracking_sync' => [
                            'job_class' => RetryTrackingSyncJob::class,
                            'failed_count' => 0,
                        ],
                    ],
                    'recent_failed_jobs' => [],
                ],
            ]);
        }

        $failedJobsQuery = DB::table('failed_jobs');

        $midtransFailedCount = (clone $failedJobsQuery)
            ->where('payload', 'like', '%RetryMidtransCallbackJob%')
            ->count();

        $trackingFailedCount = (clone $failedJobsQuery)
            ->where('payload', 'like', '%RetryTrackingSyncJob%')
            ->count();

        $recentFailedJobs = (clone $failedJobsQuery)
            ->latest('failed_at')
            ->limit(20)
            ->get(['id', 'uuid', 'queue', 'payload', 'exception', 'failed_at'])
            ->map(function ($job) {
                $payload = json_decode((string) $job->payload, true);

                return [
                    'id' => $job->id,
                    'uuid' => $job->uuid,
                    'queue' => $job->queue,
                    'display_name' => $payload['displayName'] ?? null,
                    'failed_at' => $job->failed_at,
                    'is_retry_job' => str_contains((string) $job->payload, 'RetryMidtransCallbackJob')
                        || str_contains((string) $job->payload, 'RetryTrackingSyncJob'),
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_failed_jobs' => $failedJobsQuery->count(),
                'retry_jobs' => [
                    'midtrans_callback' => [
                        'job_class' => RetryMidtransCallbackJob::class,
                        'failed_count' => $midtransFailedCount,
                    ],
                    'tracking_sync' => [
                        'job_class' => RetryTrackingSyncJob::class,
                        'failed_count' => $trackingFailedCount,
                    ],
                ],
                'recent_failed_jobs' => $recentFailedJobs,
            ],
        ]);
    }

    public function manualDeadLetterQueue(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->integer('per_page', 25), 5), 100);

        $query = AdminTask::query()
            ->where('task_type', 'retry_dead_letter')
            ->whereIn('status', ['pending', 'in_progress'])
            ->with(['assignee:id,name,email', 'creator:id,name,email'])
            ->latest();

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $paginator = $query->paginate($perPage);

        $errorLogIds = collect($paginator->items())
            ->map(fn (AdminTask $task) => (int) ($task->action_data['error_log_id'] ?? 0))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $errorLogs = ErrorLog::query()
            ->whereIn('id', $errorLogIds)
            ->get()
            ->keyBy('id');

        $items = collect($paginator->items())->map(function (AdminTask $task) use ($errorLogs) {
            $errorLogId = (int) ($task->action_data['error_log_id'] ?? 0);
            $errorLog = $errorLogId > 0 ? $errorLogs->get($errorLogId) : null;

            return [
                'task_id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'created_at' => $task->created_at,
                'assignee' => $task->assignee,
                'creator' => $task->creator,
                'action_data' => $task->action_data,
                'error_log' => $errorLog ? [
                    'id' => $errorLog->id,
                    'module' => $errorLog->module,
                    'error_type' => $errorLog->error_type,
                    'severity' => $errorLog->severity,
                    'message' => $errorLog->message,
                    'resolved_at' => $errorLog->resolved_at,
                    'detail_endpoint' => route('error-logs.show', $errorLog),
                ] : null,
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'has_next_page' => $paginator->hasMorePages(),
            ],
            'summary' => [
                'pending_total' => AdminTask::query()->where('task_type', 'retry_dead_letter')->where('status', 'pending')->count(),
                'in_progress_total' => AdminTask::query()->where('task_type', 'retry_dead_letter')->where('status', 'in_progress')->count(),
                'high_priority_total' => AdminTask::query()->where('task_type', 'retry_dead_letter')->where('priority', 'high')->whereIn('status', ['pending', 'in_progress'])->count(),
            ],
            'items' => $items,
        ]);
    }

    public function retry(Request $request, ErrorLog $errorLog): JsonResponse
    {
        if ($errorLog->resolved_at !== null) {
            return response()->json([
                'message' => 'Error log sudah terselesaikan.',
            ], 422);
        }

        $retryType = $this->resolveRetryType($errorLog);

        if ($retryType === null) {
            return response()->json([
                'message' => 'Error log ini tidak mendukung retry otomatis.',
            ], 422);
        }

        if ($retryType === 'midtrans_callback') {
            RetryMidtransCallbackJob::dispatch($errorLog->id);
        }

        if ($retryType === 'tracking_sync') {
            RetryTrackingSyncJob::dispatch($errorLog->id);
        }

        return response()->json([
            'message' => 'Retry job berhasil dijadwalkan.',
            'data' => [
                'error_log_id' => $errorLog->id,
                'retry_type' => $retryType,
                'queued_at' => now(),
            ],
        ], 202);
    }

    public function escalateToManual(Request $request, ErrorLog $errorLog, OperationalIssueService $operationalIssueService): JsonResponse
    {
        if ($errorLog->resolved_at !== null) {
            return response()->json([
                'message' => 'Error log sudah terselesaikan.',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $task = $operationalIssueService->createManualDeadLetterTask(
            $errorLog,
            $validated['reason'],
            null,
            null,
            $request->user()
        );

        if (! $task) {
            return response()->json([
                'message' => 'Task manual tidak bisa dibuat. Pastikan ada user admin/manager aktif.',
            ], 422);
        }

        return response()->json([
            'message' => 'Error log berhasil dieskalasi ke antrian manual.',
            'data' => [
                'error_log_id' => $errorLog->id,
                'task_id' => $task->id,
                'task_status' => $task->status,
                'manual_queue_endpoint' => route('error-logs.manual-dead-letter-queue'),
            ],
        ], 201);
    }

    public function resolve(Request $request, ErrorLog $errorLog, OperationalIssueService $operationalIssueService): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $wasResolved = $errorLog->resolved_at !== null;
        $resolvedErrorLog = $operationalIssueService->resolveErrorLog($errorLog, $request->user(), $validated['reason'] ?? null);

        return response()->json([
            'message' => $wasResolved ? 'Error log sudah terselesaikan sebelumnya.' : 'Error log berhasil diselesaikan.',
            'data' => $resolvedErrorLog->load('user'),
        ]);
    }

    private function resolveRetryType(ErrorLog $errorLog): ?string
    {
        $context = is_array($errorLog->context) ? $errorLog->context : [];

        if ($errorLog->module === 'integration.midtrans' && isset($context['payload']) && is_array($context['payload'])) {
            return 'midtrans_callback';
        }

        if ($errorLog->module === 'shipment' && str_contains(strtolower($errorLog->message), 'tracking shipment gagal')) {
            $operation = $context['operation'] ?? null;

            if (in_array($operation, ['create', 'update', 'delete'], true)) {
                return 'tracking_sync';
            }
        }

        return null;
    }

    private function buildUnresolvedQueueSummary(): array
    {
        $baseQuery = ErrorLog::query()->whereNull('resolved_at');

        $total = (clone $baseQuery)->count();
        $critical = (clone $baseQuery)->where('severity', 'critical')->count();
        $retryable = (clone $baseQuery)
            ->get()
            ->filter(fn (ErrorLog $log) => $this->resolveRetryType($log) !== null)
            ->count();

        $byModule = (clone $baseQuery)
            ->selectRaw('module, COUNT(*) as total')
            ->groupBy('module')
            ->orderByDesc('total')
            ->get();

        return [
            'total_unresolved' => $total,
            'critical_unresolved' => $critical,
            'retryable_unresolved' => $retryable,
            'by_module' => $byModule,
        ];
    }
}
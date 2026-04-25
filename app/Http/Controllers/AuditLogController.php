<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->with(['actor'])->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->string('action'));
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->integer('actor_id'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->string('source'));
        }

        if ($request->filled('is_manual_correction')) {
            $isManualCorrection = filter_var($request->input('is_manual_correction'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            if ($isManualCorrection !== null) {
                $query->where('is_manual_correction', $isManualCorrection);
            }
        }

        if ($request->filled('correction_reference')) {
            $query->where('correction_reference', 'like', '%'.$request->string('correction_reference').'%');
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('action', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%");
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

    public function manualCorrections(Request $request): JsonResponse
    {
        $query = AuditLog::query()
            ->with(['actor'])
            ->where('is_manual_correction', true)
            ->latest();

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->string('subject_type'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->integer('subject_id'));
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->integer('actor_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->string('from'));
        }

        if ($request->filled('until')) {
            $query->whereDate('created_at', '<=', $request->string('until'));
        }

        return response()->json($query->paginate(25));
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        return response()->json($auditLog->load('actor'));
    }
}

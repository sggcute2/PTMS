<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Mendapatkan log aktivitas dengan filter dan paginasi
     */
    public function getUserLogs(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = ActivityLog::where('user_id', $user->id)
            ->with(['user', 'subject']);

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['type'])) {
            $query->where('subject_type', 'like', '%' . $filters['type'] . '%');
        }

        if (!empty($filters['start_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if (!empty($filters['end_date'])) {
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        return $query->latest('id')->paginate(10)->withQueryString();
    }

    /**
     * Catat log aktivitas secara manual
     */
    public function log(User $user, string $action, ?string $subjectType = null, ?int $subjectId = null, array $payload = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload,
        ]);
    }

    /**
     * Mendapatkan daftar aksi unik milik user
     */
    public function getAvailableActions(User $user): Collection
    {
        return ActivityLog::where('user_id', $user->id)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');
    }
}

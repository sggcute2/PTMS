<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityLogController extends Controller
{
    public function show(ActivityLog $activityLog)
    {
        Gate::authorize('view', $activityLog);

        return view('activity_logs.show', compact('activityLog'));
    }

    public function index(Request $request)
    {
        $user = $request->user();

        // Validasi input filter
        $request->validate([
            'action' => ['nullable', 'string', 'max:50'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'type' => ['nullable', 'string', 'max:50'],
        ], [
            'end_date.after_or_equal' => 'Tanggal akhir harus sama dengan atau setelah tanggal mulai.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.date' => 'Format tanggal akhir tidak valid.',
        ]);

        // Eager load subject (polymorphic withTrashed) dan user untuk mencegah N+1
        $query = ActivityLog::where('user_id', $user->id)
            ->with(['user', 'subject']);

        // 1. Filter berdasarkan jenis aksi (contoh: PROJECT_CREATED, TASK_STATUS_UPDATED, dll.)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // 2. Filter berdasarkan jenis subjek/model (opsional: Project, Task)
        if ($request->filled('type')) {
            $query->where('subject_type', 'like', '%' . $request->type . '%');
        }

        // 3. Filter rentang tanggal (start_date sampai end_date)
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        // Pagination 10 item per halaman dengan mempertahankan query string filter
        $logs = $query->latest('id')->paginate(10)->withQueryString();

        // Dapatkan daftar aksi unik yang pernah dilakukan pengguna untuk dropdown filter
        $availableActions = ActivityLog::where('user_id', $user->id)
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('activity_logs.index', compact('logs', 'availableActions'));
    }
}

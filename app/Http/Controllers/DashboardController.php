<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Ringkasan Global Metrik
        $totalProjects = Project::where('user_id', $user->id)->count();

        // Agregat global tugas per status dalam 1 query efisien
        $globalTaskStatusCounts = Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count
            ")
            ->first();

        $totalTasks = (int) ($globalTaskStatusCounts->total ?? 0);
        $pendingTasks = (int) ($globalTaskStatusCounts->pending_count ?? 0);
        $inProgressTasks = (int) ($globalTaskStatusCounts->in_progress_count ?? 0);
        $completedTasks = (int) ($globalTaskStatusCounts->completed_count ?? 0);

        // 2. Query Agregat Efisien: Jumlah Tugas per Status di SETIAP Proyek milik pengguna
        // Menggunakan withCount dengan subquery bersyarat (mencegah N+1 query)
        $projectTaskSummaries = Project::where('user_id', $user->id)
            ->withCount([
                'tasks as total_tasks_count',
                'tasks as pending_tasks_count' => fn($q) => $q->where('status', 'pending'),
                'tasks as in_progress_tasks_count' => fn($q) => $q->where('status', 'in_progress'),
                'tasks as completed_tasks_count' => fn($q) => $q->where('status', 'completed'),
            ])
            ->latest('id')
            ->get();

        // 3. Tugas mendekati batas waktu yang belum selesai (eager load project untuk cegah N+1)
        $upcomingTasks = Task::whereHas('project', fn($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->with('project')
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // 4. Riwayat audit trail terbaru
        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->latest('id')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'totalProjects',
            'totalTasks',
            'pendingTasks',
            'inProgressTasks',
            'completedTasks',
            'projectTaskSummaries',
            'upcomingTasks',
            'recentActivities'
        ));
    }
}

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard & Ringkasan Proyek</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau status distribusi tugas di setiap proyek Anda secara real-time.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Proyek Baru
            </a>
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Tugas Baru
            </a>
        </div>
    </div>

    <!-- Stat Cards (Global Overview) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Proyek</p>
                    <p class="text-3xl font-bold text-slate-900 mt-2">{{ $totalProjects }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500">
                Total proyek aktif Anda
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Pending</p>
                    <p class="text-3xl font-bold text-slate-700 mt-2">{{ $pendingTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500">
                Menunggu untuk dikerjakan
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sedang Dikerjakan</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $inProgressTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500">
                Status In Progress
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tugas Selesai</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ $completedTasks }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-4 text-xs text-slate-500">
                {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% dari {{ $totalTasks }} total tugas
            </div>
        </div>
    </div>

    <!-- Section: Ringkasan Tugas per Status di Setiap Proyek (Agregat Efisien) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h2 class="font-bold text-base text-slate-900">Distribusi Status Tugas per Proyek</h2>
                <p class="text-xs text-slate-500 mt-0.5">Rincian jumlah tugas per status (Pending, In Progress, Completed) yang dikalkulasi secara efisien melalui query agregat.</p>
            </div>
            <a href="{{ route('projects.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                Kelola Semua Proyek &rarr;
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Nama Proyek</th>
                        <th class="px-6 py-4 text-center">Pending</th>
                        <th class="px-6 py-4 text-center">In Progress</th>
                        <th class="px-6 py-4 text-center">Completed</th>
                        <th class="px-6 py-4 text-center">Total Tugas</th>
                        <th class="px-6 py-4 min-w-[200px]">Progress Penyelesaian</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projectTaskSummaries as $project)
                        @php
                            $total = $project->total_tasks_count;
                            $pctCompleted = $total > 0 ? round(($project->completed_tasks_count / $total) * 100) : 0;
                            $pctInProgress = $total > 0 ? round(($project->in_progress_tasks_count / $total) * 100) : 0;
                            $pctPending = $total > 0 ? round(($project->pending_tasks_count / $total) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4">
                                <a href="{{ route('projects.show', $project) }}" class="font-semibold text-slate-900 hover:text-indigo-600 block text-sm">
                                    {{ $project->name }}
                                </a>
                                @if($project->description)
                                    <span class="text-xs text-slate-500 line-clamp-1 mt-0.5">{{ $project->description }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 min-w-8">
                                    {{ $project->pending_tasks_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 min-w-8">
                                    {{ $project->in_progress_tasks_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700 min-w-8">
                                    {{ $project->completed_tasks_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-800">
                                {{ $total }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <div class="flex justify-between text-xs text-slate-500 font-medium">
                                        <span>{{ $project->completed_tasks_count }}/{{ $total }} Selesai</span>
                                        <span class="font-bold text-slate-700">{{ $pctCompleted }}%</span>
                                    </div>
                                    <!-- Segmented Progress Bar -->
                                    <div class="w-full bg-slate-100 rounded-full h-2.5 flex overflow-hidden">
                                        @if($total > 0)
                                            <div class="bg-emerald-500 h-2.5 transition-all" style="width: {{ $pctCompleted }}%" title="Completed: {{ $project->completed_tasks_count }}"></div>
                                            <div class="bg-blue-500 h-2.5 transition-all" style="width: {{ $pctInProgress }}%" title="In Progress: {{ $project->in_progress_tasks_count }}"></div>
                                            <div class="bg-slate-300 h-2.5 transition-all" style="width: {{ $pctPending }}%" title="Pending: {{ $project->pending_tasks_count }}"></div>
                                        @else
                                            <div class="bg-slate-200 h-2.5 w-full"></div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 text-xs">
                                <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="text-slate-600 hover:text-slate-900 font-medium">+ Tugas</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500 text-sm">
                                Belum ada proyek terdaftar.
                                <div class="mt-2">
                                    <a href="{{ route('projects.create') }}" class="text-indigo-600 font-semibold hover:underline">Buat proyek pertama Anda</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2 Columns: Upcoming Tasks and Recent Activity Log -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Upcoming Tasks -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-base text-slate-900">Tugas Mendekati Batas Waktu</h2>
                <a href="{{ route('tasks.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Lihat Semua &rarr;</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($upcomingTasks as $task)
                    <div class="p-5 hover:bg-slate-50/70 transition flex items-center justify-between">
                        <div class="space-y-1">
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-slate-900 hover:text-indigo-600 transition">
                                {{ $task->title }}
                            </a>
                            <div class="flex items-center space-x-2 text-xs text-slate-500">
                                <span class="text-indigo-600">{{ $task->project->name ?? 'Tanpa Proyek' }}</span>
                                <span>&bull;</span>
                                <span class="text-rose-600 font-medium">Batas: {{ $task->due_date ? $task->due_date->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                        @php
                            $taskStatusBadge = [
                                'pending' => 'bg-slate-100 text-slate-700',
                                'in_progress' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full {{ $taskStatusBadge[$task->status] ?? 'bg-slate-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 text-sm">
                        Tidak ada tugas mendesak yang belum selesai.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity Log (Audit Trail) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-bold text-base text-slate-900">Audit Trail Terbaru</h2>
                <a href="{{ route('activity_logs.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Semua Log &rarr;</a>
            </div>
            <div class="p-6">
                <div class="flow-root">
                    <ul class="-mb-8">
                        @forelse($recentActivities as $index => $log)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            @php
                                                $actionBadge = 'bg-slate-500 text-white';
                                                if (str_contains($log->action, 'CREATED')) {
                                                    $actionBadge = 'bg-emerald-500 text-white';
                                                } elseif (str_contains($log->action, 'STATUS_UPDATED')) {
                                                    $actionBadge = 'bg-purple-500 text-white';
                                                } elseif (str_contains($log->action, 'UPDATED')) {
                                                    $actionBadge = 'bg-blue-500 text-white';
                                                } elseif (str_contains($log->action, 'DELETED')) {
                                                    $actionBadge = 'bg-rose-500 text-white';
                                                }
                                            @endphp
                                            <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white {{ $actionBadge }} text-xs font-bold font-mono">
                                                {{ strtoupper(substr($log->action, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                            <div>
                                                <p class="text-xs font-mono font-semibold text-slate-800">{{ $log->action }}</p>
                                                <p class="text-[11px] text-slate-500 mt-0.5">
                                                    {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                                    @if(!empty($log->payload['field']) && $log->payload['field'] === 'status')
                                                        ({{ $log->payload['old'] }} &rarr; {{ $log->payload['new'] }})
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="text-right text-[11px] whitespace-nowrap text-slate-400">
                                                {{ $log->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center text-sm text-slate-500 py-4">Belum ada riwayat aktivitas.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

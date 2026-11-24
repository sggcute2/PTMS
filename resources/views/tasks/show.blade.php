@extends('layouts.app')

@section('title', 'Tugas: ' . $task->title)

@section('content')
<div class="space-y-8 max-w-4xl mx-auto">
    <!-- Task Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="space-y-3 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    @php
                        $statusBadge = [
                            'pending' => 'bg-slate-100 text-slate-700',
                            'in_progress' => 'bg-blue-100 text-blue-700 font-semibold',
                            'completed' => 'bg-emerald-100 text-emerald-700 font-semibold',
                        ];
                    @endphp
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusBadge[$task->status] ?? 'bg-slate-100' }}">
                        Status: {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                    <span class="text-xs text-slate-500">
                        Proyek:
                        @if($task->project)
                            <a href="{{ route('projects.show', $task->project) }}" class="font-semibold text-indigo-600 hover:underline">
                                {{ $task->project->name }}
                            </a>
                        @else
                            <span class="text-slate-400">Tanpa Proyek</span>
                        @endif
                    </span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $task->title }}</h1>

                <div class="pt-2 text-xs text-slate-500 flex items-center space-x-4">
                    <div>
                        <span class="font-medium text-slate-700">Batas Waktu:</span>
                        {{ $task->due_date ? $task->due_date->format('d F Y') : 'Tidak ditentukan' }}
                    </div>
                    <div>&bull;</div>
                    <div>
                        <span class="font-medium text-slate-700">Dibuat:</span>
                        {{ $task->created_at->format('d M Y H:i') }}
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <!-- Advance Status Button -->
                @if($task->status === 'pending')
                    <form action="{{ route('tasks.advanceStatus', $task) }}" method="POST">
                        @csrf
                        <input type="hidden" name="target_status" value="in_progress">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl shadow-xs transition cursor-pointer">
                            Mulai Kerjakan (In Progress) &rarr;
                        </button>
                    </form>
                @elseif($task->status === 'in_progress')
                    <form action="{{ route('tasks.advanceStatus', $task) }}" method="POST">
                        @csrf
                        <input type="hidden" name="target_status" value="completed">
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-xl shadow-xs transition cursor-pointer">
                            Selesaikan Tugas (Completed) &check;
                        </button>
                    </form>
                @endif

                <a href="{{ route('tasks.edit', $task) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition text-center">
                    Edit Tugas
                </a>
                <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Hapus tugas ini? (Soft delete & audit trail akan dicatat)');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-medium rounded-xl transition cursor-pointer">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Audit Trail for this specific task -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-900">Riwayat Perubahan Tugas (Audit Trail)</h2>
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6">
            <div class="flow-root">
                <ul class="-mb-6">
                    @forelse($activityLogs as $log)
                        <li>
                            <div class="relative pb-6">
                                @if(!$loop->last)
                                    <span class="absolute top-3 left-3 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3 text-xs">
                                    @php
                                        $actionBadge = 'bg-slate-400';
                                        if (str_contains($log->action, 'CREATED')) {
                                            $actionBadge = 'bg-emerald-500';
                                        } elseif (str_contains($log->action, 'STATUS_UPDATED')) {
                                            $actionBadge = 'bg-purple-500';
                                        } elseif (str_contains($log->action, 'UPDATED')) {
                                            $actionBadge = 'bg-blue-500';
                                        } elseif (str_contains($log->action, 'DELETED')) {
                                            $actionBadge = 'bg-rose-500';
                                        }
                                    @endphp
                                    <div class="w-6 h-6 rounded-full {{ $actionBadge }} text-white flex items-center justify-center font-bold text-[10px] shrink-0 ring-4 ring-white font-mono">
                                        {{ strtoupper(substr($log->action, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 space-y-1">
                                        <p class="font-mono font-semibold text-slate-800">{{ $log->action }}</p>

                                        @if(!empty($log->payload['field']) && $log->payload['field'] === 'status')
                                            <div class="text-[11px] bg-slate-50 p-2.5 rounded-lg border border-slate-100 text-slate-700 mt-1 inline-flex items-center space-x-2">
                                                <span class="font-medium text-slate-500">Status:</span>
                                                <span class="text-rose-500 line-through">{{ $log->payload['old'] }}</span>
                                                <span>&rarr;</span>
                                                <span class="text-emerald-600 font-bold">{{ $log->payload['new'] }}</span>
                                            </div>
                                        @elseif(!empty($log->payload['old']) || !empty($log->payload['new']))
                                            <div class="text-[11px] bg-slate-50 p-3 rounded-xl border border-slate-100 text-slate-700 space-y-1 mt-1">
                                                @foreach(($log->payload['new'] ?? []) as $field => $newVal)
                                                    @php $oldVal = $log->payload['old'][$field] ?? null; @endphp
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-1">
                                                        <span class="font-semibold text-slate-600">{{ $field }}:</span>
                                                        <span class="text-rose-500 line-through">{{ is_array($oldVal) ? json_encode($oldVal) : ($oldVal ?? 'null') }}</span>
                                                        <span class="text-emerald-600 font-medium">&rarr; {{ is_array($newVal) ? json_encode($newVal) : ($newVal ?? 'null') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="text-[10px] text-slate-400">
                                            {{ $log->created_at->format('d M Y H:i:s') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="text-xs text-slate-500 text-center py-4">Belum ada riwayat aktivitas tugas ini.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', $project->name)

@section('content')
    <div class="space-y-8">
        <!-- Project Header & Actions -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <span class="text-xs text-slate-500">Dibuat {{ $project->created_at->format('d M Y') }}</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight">{{ $project->name }}</h1>
                    <p class="text-slate-600 text-sm max-w-3xl leading-relaxed whitespace-pre-line">
                        {{ $project->description ?: 'Tidak ada deskripsi rinci.' }}</p>
                </div>
                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('projects.edit', $project) }}"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition">
                        Edit Proyek
                    </a>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST"
                        onsubmit="return confirm('Hapus proyek ini? Seluruh tugas di dalamnya juga akan terhapus.');"
                        class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm font-medium rounded-xl transition cursor-pointer">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2 Columns: Tasks List and Audit Trail -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Tasks Column -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Daftar Tugas ({{ $project->tasks->count() }})</h2>
                        <p class="text-xs text-slate-500">Klik tombol aksi status untuk memajukan status tugas satu per satu
                            tahap.</p>
                    </div>
                    <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}"
                        class="inline-flex items-center px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Tugas
                    </a>
                </div>

                <div class="space-y-3" id="tasks-container">
                    <!-- Toast Notification for AJAX feedback -->
                    <div id="ajax-toast"
                        class="hidden transition-all duration-300 p-4 rounded-xl text-sm font-medium items-center justify-between shadow-sm">
                        <div class="flex items-center space-x-2">
                            <span id="ajax-toast-icon"></span>
                            <span id="ajax-toast-message"></span>
                        </div>
                        <button type="button" onclick="hideToast()"
                            class="text-xs opacity-70 hover:opacity-100 ml-4 font-bold cursor-pointer">&times;</button>
                    </div>

                    @forelse($project->tasks as $task)
                        <div id="task-card-{{ $task->id }}"
                            class="bg-white p-5 rounded-2xl border {{ $task->is_overdue ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200/80' }} shadow-xs hover:border-indigo-200 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1.5 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @php
                                        $taskStatusBadge = [
                                            'pending' => 'bg-slate-100 text-slate-700',
                                            'in_progress' => 'bg-blue-100 text-blue-700 font-semibold',
                                            'completed' => 'bg-emerald-100 text-emerald-700 font-semibold',
                                        ];
                                    @endphp
                                    <span id="task-badge-{{ $task->id }}"
                                        class="px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $taskStatusBadge[$task->status] ?? 'bg-slate-100' }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    @if($task->is_overdue)
                                        <span id="task-overdue-{{ $task->id }}"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-1 animate-pulse"></span>
                                            LEWAT JATUH TEMPO
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('tasks.show', $task) }}"
                                    class="font-semibold text-slate-900 hover:text-indigo-600 text-base block">
                                    {{ $task->title }}
                                </a>
                                <div class="text-[11px] {{ $task->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-400' }}">
                                    Tenggat: {{ $task->due_date ? $task->due_date->format('d M Y') : 'Tanpa batas' }}
                                    @if($task->is_overdue)
                                        <span class="ml-1 text-[10px] font-medium text-rose-500">(Terlambat)</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Advance Status Button & Actions (AJAX powered) -->
                            <div class="flex items-center space-x-2 shrink-0">
                                <div id="task-action-wrapper-{{ $task->id }}">
                                    @if($task->status === 'pending')
                                        <button type="button" onclick="updateTaskStatusAjax({{ $task->id }}, 'in_progress', this)"
                                            class="inline-flex items-center px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-xl border border-blue-200 transition cursor-pointer shadow-2xs"
                                            title="Mulai kerjakan tugas">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                            Mulai Kerjakan &rarr;
                                        </button>
                                    @elseif($task->status === 'in_progress')
                                        <button type="button" onclick="updateTaskStatusAjax({{ $task->id }}, 'completed', this)"
                                            class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-xl border border-emerald-200 transition cursor-pointer shadow-2xs"
                                            title="Tandai tugas telah selesai">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            Selesaikan &check;
                                        </button>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 bg-slate-50 text-slate-400 text-xs font-medium rounded-xl border border-slate-200">
                                            <svg class="w-3.5 h-3.5 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Tahap Akhir
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ route('tasks.show', $task) }}"
                                    class="p-1.5 text-slate-500 hover:text-indigo-600 rounded-lg hover:bg-slate-100"
                                    title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <form action="{{ route('tasks.destroy', $task) }}" method="POST"
                                    onsubmit="return confirm('Hapus tugas ini?');"
                                    class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 cursor-pointer"
                                        title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-2xl border border-slate-200/80 p-8 text-center text-slate-500 text-sm">
                            Belum ada tugas di dalam proyek ini.
                            <div class="mt-2">
                                <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}"
                                    class="text-indigo-600 font-semibold hover:underline">
                                    Tambahkan tugas pertama
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Project Audit Trail Column -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-slate-900">Riwayat Proyek (Audit Trail)</h2>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                    <div class="flow-root">
                        <ul class="-mb-6">
                            @forelse($activityLogs as $log)
                                <li>
                                    <div class="relative pb-6">
                                        @if(!$loop->last)
                                            <span class="absolute top-3 left-3 -ml-px h-full w-0.5 bg-slate-200"
                                                aria-hidden="true"></span>
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
                                            <div
                                                class="w-6 h-6 rounded-full {{ $actionBadge }} text-white flex items-center justify-center font-bold text-[10px] shrink-0 ring-4 ring-white font-mono">
                                                {{ strtoupper(substr($log->action, 0, 1)) }}
                                            </div>
                                            <div class="flex-1 space-y-1">
                                                <p class="font-mono font-semibold text-slate-800">{{ $log->action }}</p>
                                                @if(!empty($log->payload['field']) && $log->payload['field'] === 'status')
                                                    <div
                                                        class="text-[11px] bg-slate-50 p-1.5 rounded-md border border-slate-100 text-slate-600">
                                                        Status: <span
                                                            class="line-through text-rose-500">{{ $log->payload['old'] }}</span>
                                                        &rarr; <span
                                                            class="font-bold text-emerald-600">{{ $log->payload['new'] }}</span>
                                                    </div>
                                                @elseif(!empty($log->payload['new']))
                                                    <div
                                                        class="text-[11px] bg-slate-50 p-2 rounded-lg border border-slate-100 text-slate-600 space-y-0.5">
                                                        @foreach($log->payload['new'] as $field => $val)
                                                            <div><span class="font-medium">{{ $field }}:</span>
                                                                {{ is_array($val) ? json_encode($val) : $val }}</div>
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
                                <li class="text-xs text-slate-500 text-center py-4">Belum ada riwayat tercatat.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showToast(message, isSuccess = true) {
            const toast = document.getElementById('ajax-toast');
            const icon = document.getElementById('ajax-toast-icon');
            const text = document.getElementById('ajax-toast-message');

            toast.className = 'transition-all duration-300 p-4 rounded-xl text-sm font-medium flex items-center justify-between shadow-sm ' +
                (isSuccess ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200');

            icon.innerHTML = isSuccess
                ? '<svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                : '<svg class="w-5 h-5 text-rose-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>';

            text.textContent = message;
            toast.classList.remove('hidden');

            clearTimeout(window.toastTimeout);
            window.toastTimeout = setTimeout(() => {
                hideToast();
            }, 5000);
        }

        function hideToast() {
            const toast = document.getElementById('ajax-toast');
            if (toast) toast.classList.add('hidden');
        }

        async function updateTaskStatusAjax(taskId, targetStatus, btnElement) {
            const originalContent = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = `
                <svg class="animate-spin -ml-0.5 mr-1.5 h-3.5 w-3.5 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses...
            `;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                const response = await fetch(`/api/tasks/${taskId}/status`, {
                    method: 'PATCH',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        status: targetStatus
                    })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    showToast(data.message || 'Status tugas berhasil diperbarui.', true);

                    // Perbarui badge status
                    const badge = document.getElementById(`task-badge-${taskId}`);
                    if (badge) {
                        if (targetStatus === 'in_progress') {
                            badge.className = 'px-2.5 py-0.5 text-xs font-semibold rounded-full bg-blue-100 text-blue-700';
                            badge.textContent = 'In progress';
                        } else if (targetStatus === 'completed') {
                            badge.className = 'px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700';
                            badge.textContent = 'Completed';

                            // Jika overdue tag ada dan tugas sudah completed, hilangkan penanda overdue
                            const overdueTag = document.getElementById(`task-overdue-${taskId}`);
                            if (overdueTag) {
                                overdueTag.remove();
                            }
                        }
                    }

                    // Perbarui tombol aksi
                    const actionWrapper = document.getElementById(`task-action-wrapper-${taskId}`);
                    if (actionWrapper) {
                        if (targetStatus === 'in_progress') {
                            actionWrapper.innerHTML = `
                                <button type="button"
                                    onclick="updateTaskStatusAjax(${taskId}, 'completed', this)"
                                    class="inline-flex items-center px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-xl border border-emerald-200 transition cursor-pointer shadow-2xs"
                                    title="Tandai tugas telah selesai">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Selesaikan &check;
                                </button>
                            `;
                        } else if (targetStatus === 'completed') {
                            actionWrapper.innerHTML = `
                                <span class="inline-flex items-center px-2.5 py-1 bg-slate-50 text-slate-400 text-xs font-medium rounded-xl border border-slate-200">
                                    <svg class="w-3.5 h-3.5 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Tahap Akhir
                                </span>
                            `;
                        }
                    }
                } else {
                    let errorMsg = data.message || 'Terjadi kesalahan saat memperbarui status tugas.';
                    if (response.status === 429) {
                        errorMsg = 'Terlalu banyak permintaan perubahan status. Silakan coba beberapa saat lagi (Rate limit exceeded).';
                    }
                    showToast(errorMsg, false);
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('AJAX Error:', error);
                showToast('Gagal menghubungi server. Periksa koneksi internet Anda.', false);
                btnElement.disabled = false;
                btnElement.innerHTML = originalContent;
            }
        }
    </script>
@endsection
@extends('layouts.app')

@section('title', 'Daftar Tugas')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Tugas</h1>
            <p class="text-sm text-slate-500 mt-1">Pantau dan kelola seluruh tugas dengan pencarian judul, filter status, dan deteksi keterlambatan batas waktu (overdue).</p>
        </div>
        <div class="flex items-center gap-3">
            @if($overdueCount > 0)
                <a href="{{ route('tasks.index', ['overdue' => '1']) }}" class="inline-flex items-center px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-xl border border-rose-200 transition">
                    <span class="w-2 h-2 rounded-full bg-rose-500 mr-2 animate-pulse"></span>
                    {{ $overdueCount }} Tugas Terlambat (Overdue)
                </a>
            @endif
            <a href="{{ route('tasks.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-xs transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Tugas
            </a>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('tasks.index') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Pencarian berdasarkan Judul Tugas -->
                <div class="lg:col-span-5">
                    <label for="search" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Cari Judul Tugas
                    </label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik kata kunci judul tugas..."
                            class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                </div>

                <!-- Filter berdasarkan Status -->
                <div class="lg:col-span-3">
                    <label for="status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Status Tugas
                    </label>
                    <select name="status" id="status" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Filter berdasarkan Proyek -->
                <div class="lg:col-span-2">
                    <label for="project_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Proyek
                    </label>
                    <select name="project_id" id="project_id" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition cursor-pointer shadow-2xs">
                        Cari
                    </button>
                    @if(request('search') || request('status') || request('project_id') || request('overdue'))
                        <a href="{{ route('tasks.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition text-center" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Quick Filter Checkbox: Lewat Batas Waktu (Overdue) -->
            <div class="flex items-center space-x-2 pt-2 border-t border-slate-100 text-xs">
                <input type="checkbox" name="overdue" id="overdue" value="1" {{ request('overdue') === '1' ? 'checked' : '' }} onchange="this.form.submit()"
                    class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-slate-300 cursor-pointer">
                <label for="overdue" class="text-slate-700 font-medium cursor-pointer flex items-center">
                    <span>Hanya tampilkan tugas yang <strong class="text-rose-600">melewati batas waktu (Overdue)</strong> dan belum selesai</span>
                </label>
            </div>
        </form>
    </div>

    <!-- Tasks List Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Tugas</th>
                        <th class="px-6 py-4">Proyek</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Batas Waktu (Due Date)</th>
                        <th class="px-6 py-4 text-center">Tahapan Aksi</th>
                        <th class="px-6 py-4 text-right">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tasks as $task)
                        <tr class="hover:bg-slate-50/70 transition {{ $task->is_overdue ? 'bg-rose-50/30' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('tasks.show', $task) }}" class="font-semibold text-slate-900 hover:text-indigo-600 block">
                                        {{ $task->title }}
                                    </a>
                                    @if($task->is_overdue)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                            <svg class="w-3 h-3 mr-1 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            TERLAMBAT
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                @if($task->project)
                                    <a href="{{ route('projects.show', $task->project) }}" class="text-indigo-600 hover:underline">
                                        {{ $task->project->name }}
                                    </a>
                                    @if($task->project->trashed())
                                        <span class="text-rose-500 text-[10px] block">(Proyek Terhapus)</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusBadge = [
                                        'pending' => 'bg-slate-100 text-slate-700',
                                        'in_progress' => 'bg-blue-100 text-blue-700 font-semibold',
                                        'completed' => 'bg-emerald-100 text-emerald-700 font-semibold',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-xs rounded-full {{ $statusBadge[$task->status] ?? 'bg-slate-100' }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs">
                                @if($task->due_date)
                                    <div class="{{ $task->is_overdue ? 'text-rose-600 font-bold' : 'text-slate-600' }}">
                                        {{ $task->due_date->format('d M Y') }}
                                    </div>
                                    @if($task->is_overdue)
                                        <div class="text-[10px] text-rose-500 font-medium">
                                            Lewat {{ $task->due_date->diffInDays(now()) }} hari yang lalu
                                        </div>
                                    @endif
                                @else
                                    <span class="text-slate-400">Tanpa batas</span>
                                @endif
                            </td>
                            <!-- Action Tombol Transisi Maju Satu Tahap -->
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                @if($task->status === 'pending')
                                    <form action="{{ route('tasks.advanceStatus', $task) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="in_progress">
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg border border-blue-200 transition cursor-pointer">
                                            Mulai Kerjakan &rarr;
                                        </button>
                                    </form>
                                @elseif($task->status === 'in_progress')
                                    <form action="{{ route('tasks.advanceStatus', $task) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="target_status" value="completed">
                                        <button type="submit" class="inline-flex items-center px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-200 transition cursor-pointer">
                                            Selesaikan &check;
                                        </button>
                                    </form>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 text-slate-400 text-xs">
                                        &check; Tuntas
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 text-xs whitespace-nowrap">
                                <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                                <a href="{{ route('tasks.edit', $task) }}" class="text-slate-600 hover:text-slate-900 font-medium">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Tidak ada tugas yang sesuai dengan kriteria pencarian / filter.
                                @if(request('search') || request('status') || request('overdue'))
                                    <div class="mt-2">
                                        <a href="{{ route('tasks.index') }}" class="text-indigo-600 font-medium hover:underline">Reset semua filter</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $tasks->links() }}
    </div>
</div>
@endsection

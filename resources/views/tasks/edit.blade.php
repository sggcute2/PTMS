@extends('layouts.app')

@section('title', 'Edit Tugas: ' . $task->title)

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Tugas</h1>
                <p class="text-sm text-slate-500 mt-1">Perubahan akan tercatat secara rinci di audit trail.</p>
            </div>
            <a href="{{ route('tasks.show', $task) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                &larr; Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('tasks.update', $task) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="project_id" class="block text-sm font-semibold text-slate-700 mb-1">Pilih Proyek <span
                            class="text-rose-500">*</span></label>
                    <select name="project_id" id="project_id" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id', $task->project_id) == $p->id ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-1">Judul Tugas <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $task->title) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700 mb-1">Status <span
                                class="text-rose-500">*</span></label>
                        <select name="status" id="status" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="pending" {{ old('status', $task->status) === 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>
                    </div>
                    <div>
                        <label for="due_date" class="block text-sm font-semibold text-slate-700 mb-1">Batas Waktu</label>
                        <input type="date" name="due_date" id="due_date"
                            value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}"
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button"
                        onclick="if(confirm('Apakah Anda yakin ingin menghapus tugas ini?')) { document.getElementById('delete-task-form').submit(); }"
                        class="text-sm font-medium text-rose-600 hover:text-rose-800 cursor-pointer">
                        Hapus Tugas
                    </button>

                    <div class="flex space-x-3">
                        <a href="{{ route('tasks.show', $task) }}"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition cursor-pointer">
                            Perbarui Tugas
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-task-form" action="{{ route('tasks.destroy', $task) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection
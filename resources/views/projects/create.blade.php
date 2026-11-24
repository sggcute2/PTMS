@extends('layouts.app')

@section('title', 'Buat Proyek Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Buat Proyek Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Isi rincian proyek. Anda juga dapat menambahkan beberapa tugas awal sekaligus dalam 1 transaksi.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">
            &larr; Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
        <form action="{{ route('projects.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Proyek <span class="text-rose-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Contoh: Redesain Landing Page 2026"
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi Proyek</label>
                <textarea name="description" id="description" rows="3" placeholder="Jelaskan ringkasan tujuan atau ruang lingkup proyek..."
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description') }}</textarea>
            </div>

            <!-- Optional Initial Tasks (Atomic Transaction Showcase) -->
            <div class="border-t border-slate-150 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Tugas Awal Sekaligus (Atomic Multi-table)</h3>
                        <p class="text-xs text-slate-500">Buat beberapa tugas sekaligus bersamaan dengan pembuatan proyek di dalam 1 DB Transaction.</p>
                    </div>
                    <button type="button" onclick="addTaskRow()" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-lg transition cursor-pointer">
                        + Tambah Baris
                    </button>
                </div>

                <div id="tasks-container" class="space-y-3">
                    <div class="task-row flex flex-col sm:flex-row gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div class="flex-1">
                            <input type="text" name="tasks[0][title]" placeholder="Judul tugas (opsional)" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
                        </div>
                        <div class="w-full sm:w-36">
                            <select name="tasks[0][status]" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-36">
                            <input type="date" name="tasks[0][due_date]" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('projects.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition cursor-pointer">
                    Simpan Proyek
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let taskIndex = 1;
    function addTaskRow() {
        const container = document.getElementById('tasks-container');
        const row = document.createElement('div');
        row.className = 'task-row flex flex-col sm:flex-row gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200';
        row.innerHTML = `
            <div class="flex-1">
                <input type="text" name="tasks[${taskIndex}][title]" placeholder="Judul tugas" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
            </div>
            <div class="w-full sm:w-36">
                <select name="tasks[${taskIndex}][status]" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="w-full sm:w-36">
                <input type="date" name="tasks[${taskIndex}][due_date]" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 bg-white">
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="px-2 text-rose-500 hover:text-rose-700 text-xs">Hapus</button>
        `;
        container.appendChild(row);
        taskIndex++;
    }
</script>
@endsection

@extends('layouts.app')

@section('title', 'Edit Proyek: ' . $project->name)

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Edit Proyek</h1>
            </div>
            <a href="{{ route('projects.show', $project) }}"
                class="text-sm font-medium text-slate-600 hover:text-slate-900">
                &larr; Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-6 sm:p-8">
            <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Proyek <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" required
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-1">Deskripsi
                        Proyek</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">{{ old('description', $project->description) }}</textarea>
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                    <button type="button"
                        onclick="if(confirm('Apakah Anda yakin ingin menghapus proyek ini? Seluruh tugas di dalamnya juga akan terhapus.')) { document.getElementById('delete-project-form').submit(); }"
                        class="text-sm font-medium text-rose-600 hover:text-rose-800 cursor-pointer">
                        Hapus Proyek Ini
                    </button>

                    <div class="flex space-x-3">
                        <a href="{{ route('projects.show', $project) }}"
                            class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm transition cursor-pointer">
                            Perbarui Proyek
                        </button>
                    </div>
                </div>
            </form>

            <form id="delete-project-form" action="{{ route('projects.destroy', $project) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection
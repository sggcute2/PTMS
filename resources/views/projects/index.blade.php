@extends('layouts.app')

@section('title', 'Daftar Proyek')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Proyek</h1>
            <p class="text-sm text-slate-500 mt-1">Kelola semua proyek dan pantau kemajuan tugas Anda.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl shadow-xs transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Proyek
        </a>
    </div>

    <!-- Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('projects.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau deskripsi proyek..."
                    class="w-full px-4 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-xl transition">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Project Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($projects as $project)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div class="p-6">
                    <div class="mb-3">
                        <a href="{{ route('projects.show', $project) }}" class="font-bold text-lg text-slate-900 hover:text-indigo-600 transition leading-snug">
                            {{ $project->name }}
                        </a>
                    </div>
                    <p class="text-sm text-slate-600 line-clamp-3 mb-4">
                        {{ $project->description ?: 'Tidak ada deskripsi.' }}
                    </p>

                    <!-- Progress Bar -->
                    @php
                        $percentage = $project->tasks_count > 0 ? round(($project->completed_tasks_count / $project->tasks_count) * 100) : 0;
                    @endphp
                    <div class="space-y-1.5 mt-4">
                        <div class="flex justify-between text-xs font-medium text-slate-600">
                            <span>Kemajuan Tugas</span>
                            <span>{{ $project->completed_tasks_count }} / {{ $project->tasks_count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl flex items-center justify-between text-xs text-slate-500">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Dibuat: {{ $project->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('projects.show', $project) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Detail</a>
                        <span>&bull;</span>
                        <a href="{{ route('projects.edit', $project) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 p-12 text-center">
                <svg class="w-12 h-12 text-slate-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <h3 class="font-medium text-slate-900">Tidak ada proyek yang ditemukan</h3>
                <p class="text-sm text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau buat proyek baru.</p>
                <div class="mt-4">
                    <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-medium hover:bg-indigo-700">Buat Proyek Baru</a>
                </div>
            </div>
        @endforelse
    </div>

    <div>
        {{ $projects->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Riwayat Aktivitas')

@section('content')
<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Detail Riwayat Aktivitas</h1>
            <p class="mt-1 text-sm text-slate-500">Log ID #{{ $activityLog->id }}</p>
        </div>
        <a href="{{ route('activity_logs.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    {{-- Main Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
        {{-- Header Badge --}}
        <div class="px-6 py-5 bg-gradient-to-r from-indigo-50 to-slate-50 border-b border-slate-200/80 flex items-center gap-4">
            <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold tracking-widest uppercase
                    {{ str_starts_with($activityLog->action, 'PROJECT') ? 'bg-blue-100 text-blue-700' : (str_starts_with($activityLog->action, 'TASK') ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                    {{ $activityLog->action }}
                </span>
                <p class="mt-1 text-xs text-slate-500">{{ $activityLog->created_at->format('d M Y, H:i:s') }} WIB</p>
            </div>
        </div>

        {{-- Detail Rows --}}
        <dl class="divide-y divide-slate-100">
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-slate-500">Jenis Aksi</dt>
                <dd class="col-span-2 text-sm font-semibold text-slate-900">{{ $activityLog->action }}</dd>
            </div>

            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-slate-500">Dilakukan Oleh</dt>
                <dd class="col-span-2 text-sm text-slate-900">
                    {{ $activityLog->user?->name ?? '—' }}
                    <span class="ml-2 text-xs text-slate-400">({{ $activityLog->user?->email ?? '' }})</span>
                </dd>
            </div>

            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-slate-500">Objek Terkait</dt>
                <dd class="col-span-2 text-sm text-slate-900">
                    @php
                        $subjectClass = class_basename($activityLog->subject_type ?? '');
                        $subjectLabel = match($subjectClass) {
                            'Project' => 'Proyek',
                            'Task'    => 'Tugas',
                            default   => $subjectClass ?: '—',
                        };
                    @endphp
                    {{ $subjectLabel }}
                    @if($activityLog->subject_id)
                        <span class="ml-1 text-slate-400">#{{ $activityLog->subject_id }}</span>
                    @endif
                    @if($activityLog->subject)
                        — <span class="text-slate-600">{{ $activityLog->subject->name ?? $activityLog->subject->title ?? '' }}</span>
                    @endif
                </dd>
            </div>

            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-slate-500">Waktu</dt>
                <dd class="col-span-2 text-sm text-slate-900">
                    {{ $activityLog->created_at->translatedFormat('l, d F Y') }} pukul {{ $activityLog->created_at->format('H:i:s') }}
                    <span class="ml-2 text-xs text-slate-400">({{ $activityLog->created_at->diffForHumans() }})</span>
                </dd>
            </div>

            @if($activityLog->payload && count($activityLog->payload))
            <div class="px-6 py-4">
                <dt class="text-sm font-medium text-slate-500 mb-3">Data Perubahan</dt>
                <dd>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-100 text-slate-600">
                                <tr>
                                    <th class="px-4 py-2 text-left font-semibold">Field</th>
                                    <th class="px-4 py-2 text-left font-semibold">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($activityLog->payload as $key => $value)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-indigo-600 text-xs font-semibold">{{ $key }}</td>
                                    <td class="px-4 py-2 text-slate-700 font-mono text-xs">
                                        @if(is_array($value))
                                            <span class="text-slate-400 italic">[array]</span>
                                        @elseif(is_null($value))
                                            <span class="text-slate-400 italic">null</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </dd>
            </div>
            @else
            <div class="px-6 py-4 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-slate-500">Data Perubahan</dt>
                <dd class="col-span-2 text-sm text-slate-400 italic">Tidak ada data tambahan</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection

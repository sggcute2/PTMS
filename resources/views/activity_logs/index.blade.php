@extends('layouts.app')

@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Aktivitas (Audit Trail)</h1>
            <p class="text-sm text-slate-500 mt-1">Catatan lengkap seluruh aktivitas proyek dan tugas Anda dengan filter jenis aksi dan rentang tanggal.</p>
        </div>
        <div class="text-xs text-slate-500 bg-white px-3 py-2 rounded-xl border border-slate-200/80 shadow-2xs">
            Total tercatat: <span class="font-bold text-slate-800">{{ $logs->total() }}</span> aktivitas
        </div>
    </div>

    <!-- Filter Form: Jenis Aksi & Rentang Tanggal -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
        <form action="{{ route('activity_logs.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Filter Jenis Aksi -->
                <div>
                    <label for="action" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Jenis Aksi
                    </label>
                    <select name="action" id="action" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                        <option value="">Semua Jenis Aksi</option>
                        @foreach($availableActions as $act)
                            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                {{ $act }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Tanggal Mulai -->
                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Dari Tanggal
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>

                <!-- Filter Tanggal Akhir -->
                <div>
                    <label for="end_date" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Sampai Tanggal
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                        class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white">
                </div>

                <!-- Tombol Filter & Reset -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition cursor-pointer shadow-2xs">
                        Terapkan Filter
                    </button>
                    @if(request('action') || request('start_date') || request('end_date') || request('type'))
                        <a href="{{ route('activity_logs.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium rounded-xl transition text-center" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Filter Badges Display (jika ada filter yang diterapkan) -->
            @if(request('action') || request('start_date') || request('end_date'))
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="font-medium text-slate-700">Filter Aktif:</span>
                    @if(request('action'))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-medium">
                            Aksi: {{ request('action') }}
                        </span>
                    @endif
                    @if(request('start_date'))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                            Mulai: {{ \Carbon\Carbon::parse(request('start_date'))->format('d M Y') }}
                        </span>
                    @endif
                    @if(request('end_date'))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700">
                            Sampai: {{ \Carbon\Carbon::parse(request('end_date'))->format('d M Y') }}
                        </span>
                    @endif
                </div>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 font-semibold text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Jenis Aksi</th>
                        <th class="px-6 py-4">Entitas Terkait</th>
                        <th class="px-6 py-4">Rincian Perubahan (Payload)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                                <div class="font-medium text-slate-800">{{ $log->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $log->created_at->format('H:i:s') }} ({{ $log->created_at->diffForHumans() }})</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $actionClass = 'bg-slate-100 text-slate-800';
                                    if (str_contains($log->action, 'CREATED')) {
                                        $actionClass = 'bg-emerald-50 text-emerald-800 border border-emerald-200';
                                    } elseif (str_contains($log->action, 'STATUS_UPDATED')) {
                                        $actionClass = 'bg-purple-50 text-purple-800 border border-purple-200';
                                    } elseif (str_contains($log->action, 'UPDATED')) {
                                        $actionClass = 'bg-blue-50 text-blue-800 border border-blue-200';
                                    } elseif (str_contains($log->action, 'DELETED')) {
                                        $actionClass = 'bg-rose-50 text-rose-800 border border-rose-200 font-semibold';
                                    }
                                @endphp
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-mono font-semibold {{ $actionClass }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-semibold text-slate-800">{{ class_basename($log->subject_type) }} #{{ $log->subject_id }}</span>
                                @if($log->subject)
                                    <span class="text-slate-600 block text-[11px] mt-0.5">
                                        {{ $log->subject->name ?? ($log->subject->title ?? '') }}
                                    </span>
                                @else
                                    <span class="text-rose-400 block text-[10px] italic mt-0.5">(Data telah dihapus)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if(!empty($log->payload['field']) && $log->payload['field'] === 'status')
                                    <div class="inline-flex items-center space-x-2 bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200">
                                        <span class="font-medium text-slate-500">Perubahan Status:</span>
                                        <span class="text-rose-600 font-semibold line-through">{{ $log->payload['old'] ?? '-' }}</span>
                                        <span class="text-slate-400">&rarr;</span>
                                        <span class="text-emerald-600 font-bold">{{ $log->payload['new'] ?? '-' }}</span>
                                    </div>
                                @elseif(!empty($log->payload['old']) || !empty($log->payload['new']))
                                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/70 space-y-1">
                                        @foreach(($log->payload['new'] ?? []) as $f => $nVal)
                                            <div>
                                                <span class="font-semibold text-slate-600">{{ $f }}:</span>
                                                <span class="text-rose-500 line-through">{{ is_array($log->payload['old'][$f] ?? '') ? json_encode($log->payload['old'][$f]) : ($log->payload['old'][$f] ?? 'null') }}</span>
                                                <span class="text-emerald-600 font-medium">&rarr; {{ is_array($nVal) ? json_encode($nVal) : ($nVal ?? 'null') }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif(!empty($log->payload['attributes']))
                                    <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200/70 space-y-0.5">
                                        @foreach(array_slice($log->payload['attributes'], 0, 4) as $k => $v)
                                            <div class="text-[11px]"><span class="font-semibold text-slate-500">{{ $k }}:</span> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">{{ json_encode($log->payload) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500 text-sm">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Tidak ada aktivitas yang sesuai dengan filter yang dipilih.
                                @if(request('action') || request('start_date') || request('end_date'))
                                    <div class="mt-2">
                                        <a href="{{ route('activity_logs.index') }}" class="text-indigo-600 font-medium hover:underline">Reset semua filter</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Link -->
    <div class="pt-2">
        {{ $logs->links() }}
    </div>
</div>
@endsection

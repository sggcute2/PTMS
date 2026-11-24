<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Ditolak - 403 Forbidden</title>
    <!-- Tailwind CSS v4 via CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    @php
        $message = $exception->getMessage() ?: 'Anda tidak memiliki hak akses untuk melihat atau mengelola data ini.';
        $isTask    = str_contains(strtolower($message), 'tugas')        || request()->is('*tasks*');
        $isAudit   = str_contains(strtolower($message), 'riwayat audit') || str_contains(strtolower($message), 'activity') || request()->is('*activity-logs*');
        $isProject = !$isTask && !$isAudit && (str_contains(strtolower($message), 'proyek') || request()->is('*projects*'));

        $entityName = $isTask ? 'Tugas' : ($isAudit ? 'Riwayat Audit' : ($isProject ? 'Proyek' : 'Data'));
        $title = "Akses ke {$entityName} Dibatasi";
    @endphp

    <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl border border-slate-200/90 p-8 sm:p-12 text-center relative overflow-hidden">
        <!-- Background accent subtle blur -->
        <div class="absolute -top-16 -right-16 w-40 h-40 bg-rose-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
        <div class="absolute -bottom-16 -left-16 w-40 h-40 bg-indigo-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

        <!-- Warning / Shield Icon -->
        <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-rose-50 text-rose-600 mb-6 shadow-inner ring-8 ring-rose-50/50">
            @if($isTask)
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            @elseif($isAudit)
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            @else
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            @endif
        </div>

        <!-- Error Code & Title -->
        <div class="inline-block px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-rose-100 text-rose-700 mb-3">
            Error 403 &bull; Hak Akses Ditolak
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
            {{ $title }}
        </h1>

        <!-- Detail Explanation Message -->
        <p class="text-sm sm:text-base text-slate-600 leading-relaxed mb-6">
            {{ $message }}
        </p>

        <!-- Information Card -->
        <div class="bg-slate-50 rounded-2xl p-4 text-left border border-slate-200/70 mb-8 space-y-2 text-xs text-slate-600">
            <div class="flex items-center space-x-2 text-slate-800 font-semibold">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <span>Mengapa Anda melihat halaman ini?</span>
            </div>
            <p class="pl-6 text-slate-500 leading-normal">
                @if($isTask)
                    Tugas ini terhubung ke proyek milik pengguna lain. Sesuai kebijakan keamanan data (*TaskPolicy*), Anda hanya memiliki hak akses terhadap tugas yang berada di dalam proyek milik akun Anda sendiri.
                @elseif($isAudit)
                    Catatan riwayat audit bersifat pribadi dan hanya dapat diakses oleh pemilik akun yang bersangkutan. Sesuai kebijakan keamanan (*ActivityLogPolicy*), Anda tidak diizinkan melihat riwayat aktivitas milik pengguna lain.
                @else
                    Data ini dilindungi oleh aturan keamanan *Laravel Policy*. Setiap pengguna dibatasi hanya dapat mengelola data milik sendiri, termasuk saat URL atau ID diubah secara manual.
                @endif
            </p>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : ($isTask ? route('tasks.index') : ($isAudit ? route('activity_logs.index') : route('projects.index'))) }}"
                class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition cursor-pointer">
                &larr; Kembali
            </a>
            @if($isTask)
                <a href="{{ route('tasks.index') }}"
                    class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition cursor-pointer">
                    Daftar Tugas Saya
                </a>
            @elseif($isAudit)
                <a href="{{ route('activity_logs.index') }}"
                    class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition cursor-pointer">
                    Riwayat Aktivitas Saya
                </a>
            @else
                <a href="{{ route('projects.index') }}"
                    class="w-full sm:w-auto px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow transition cursor-pointer">
                    Daftar Proyek Saya
                </a>
            @endif
            <a href="{{ route('dashboard') }}"
                class="w-full sm:w-auto px-5 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-semibold rounded-xl transition cursor-pointer">
                Dashboard
            </a>
        </div>
    </div>
</body>
</html>

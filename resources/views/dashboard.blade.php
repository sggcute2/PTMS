@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard & Ringkasan Proyek</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau status distribusi tugas di setiap proyek Anda secara
                    real-time.</p>
            </div>
        </div>
    </div>
@endsection
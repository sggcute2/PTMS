<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Endpoint API JSON dengan middleware autentikasi dan Rate Limiting.
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    // API Endpoint JSON untuk mengubah status tugas dengan rate limiting 10 req/menit
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus'])
        ->middleware('throttle:task-status-update')
        ->name('api.tasks.updateStatus');
});

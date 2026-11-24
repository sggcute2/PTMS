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

});

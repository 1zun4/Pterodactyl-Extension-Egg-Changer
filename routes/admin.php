<?php

declare(strict_types=1);

use EggChanger\Http\Controllers\GroupController;
use Illuminate\Support\Facades\Route;

Route::get('/groups', [GroupController::class, 'index']);
Route::post('/groups', [GroupController::class, 'store']);
Route::put('/groups/{group}', [GroupController::class, 'update']);
Route::delete('/groups/{group}', [GroupController::class, 'destroy']);

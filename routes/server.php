<?php

declare(strict_types=1);

use EggChanger\Http\Controllers\ServerController;
use Illuminate\Support\Facades\Route;

Route::get('/options', [ServerController::class, 'options']);
Route::post('/change', [ServerController::class, 'change']);

<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index']);
    Route::post('/', [TicketController::class, 'store']);
    Route::get('/{ticket}', [TicketController::class, 'show']);
    Route::patch('/{ticket}/status', [TicketController::class, 'updateStatus']);
});

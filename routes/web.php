<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketWebController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', [TicketWebController::class, 'index'])->name('tickets.web.index');
Route::post('/tickets', [TicketWebController::class, 'store'])->name('tickets.web.store');
Route::patch('/tickets/{ticket}/status', [TicketWebController::class, 'updateStatus'])->name('tickets.web.update-status');


Route::patch('/tickets/{ticket}/priority', [TicketWebController::class, 'updatePriority'])
    ->name('tickets.web.update-priority');

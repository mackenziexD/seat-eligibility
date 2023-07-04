<?php

use Illuminate\Support\Facades\Route;
use Busa\Seat\Http\Controllers\ApiController;

use Busa\Seat\Http\Controllers\BUSAController;

Route::get('/busa', [BUSAController::class, 'busa'])->name('busa.dashboard');
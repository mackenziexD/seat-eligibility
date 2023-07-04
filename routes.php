<?php

use Illuminate\Support\Facades\Route;
use Busa\Seat\Http\Controllers\ApiController;

Route::group([
    'namespace' => 'Busa\Seat\Http\Controllers',
    'prefix' => 'api',
], function () {
    Route::get('/', [ApiController::class, 'index'])
    ;
    Route::get('/busa', [BUSAController::class, 'busa']);
});
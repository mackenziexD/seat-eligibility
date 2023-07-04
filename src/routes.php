<?php

Route::group([
    'namespace' => 'Busa\Seat\Http\Controllers',
    'prefix' => 'busa-seat',
    'middleware' => [
        'web',
        'auth',
    ],
], function()
{
    Route::get('/', [
        'uses' => 'BUSAController@busa',
        'as' => 'busa-seat.busa'
    ]);

});
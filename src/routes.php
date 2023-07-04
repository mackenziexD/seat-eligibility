<?php

Route::group([
    'namespace' => 'busa\seat\Http\Controllers',
    'prefix' => 'busa-seat',
    'middleware' => [
        'web',
        'auth',
    ],
], function()
{
    Route::get('/busa', [
        'uses' => 'BUSAController@about',
        'as' => 'busa-seat.busa'
    ]);

});
<?php

Route::group([
    'namespace' => 'Busa\Seat\Http\Controllers\Character',
    'prefix' => 'characters',
    'middleware' => [
        'web',
        'auth',
    ],
], function()
{
    Route::get('/{character}/eligibility', [
        'uses' => 'EligibilityController@index',
        'as' => 'seat-busa::eligibility.index',
        'middleware' => 'can:character.sheet,character',
    ]);

});
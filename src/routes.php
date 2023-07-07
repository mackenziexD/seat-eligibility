<?php

Route::group([
    'namespace' => 'helious\SeatEligibility\Http\Controllers\Character',
    'prefix' => 'characters',
    'middleware' => [
        'web',
        'auth',
    ],
], function()
{
    Route::get('/{character}/eligibility', [
        'uses' => 'EligibilityController@index',
        'as' => 'seat-eligibility::eligibility',
        'middleware' => 'can:character.sheet,character',
    ]);

});
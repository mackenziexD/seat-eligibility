<?php

namespace Busa\Seat\Http\Controllers;

use \Seat\Web\Http\Controllers\Controller;

class BUSAController extends Controller
{
    public function busa()
    {
        return view('busa-seat::about');
    }
}

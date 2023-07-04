<?php

namespace Busa\Seat\Http\Controllers;

use Illuminate\Routing\Controller;

class BUSAController extends Controller
{
    public function busa()
    {
        return view('busa.dashboard');
    }
}

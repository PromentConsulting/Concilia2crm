<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class IntegracionController extends Controller
{
    public function index(): View
    {
        return view('integraciones.index');
    }
}

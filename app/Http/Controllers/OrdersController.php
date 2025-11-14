<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {
        return Inertia::render('order/index');
    }
}

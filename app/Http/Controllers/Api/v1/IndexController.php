<?php

namespace App\Http\Controllers\Api\v1;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function menu(Request $request)
    {
        return view('api.menus.main');
    }

    public function menuInvoice(Request $request)
    {
        return view('api.menus.invoice');
    }
}
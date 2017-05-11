<?php

namespace App\Http\Controllers\Legacy;


use App\Http\Controllers\Controller;

class WartungsplanerController extends Controller
{
    public function index() {
        return view('wartungsplaner');
    }

    public function ajax() {
        return response()->legacy('legacy/wartungsplaner/ajax.php');
    }

    public function indexAjax() {
        return response()->legacy('legacy/wartungsplaner/index_ajax.php')->withHeaders(
            ['Content-Type' => 'text/plain']
        );
    }
}
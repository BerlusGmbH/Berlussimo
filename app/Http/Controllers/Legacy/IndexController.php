<?php

namespace App\Http\Controllers\Legacy;


class IndexController extends LegacyController
{

    public function request()
    {
        return view('layouts.app');
    }

    public function ajax()
    {
        return response()->legacy('legacy/ajax/ajax_info.php')->withHeaders(
            ['Content-Type' => 'text/plain']
        );
    }

    public function pie()
    {
        return response()->legacy('legacy/graph/examples/myPieGraph.php');
    }

    public function line()
    {
        return response()->legacy('legacy/graph/examples/myLineGraph.php');
    }
}
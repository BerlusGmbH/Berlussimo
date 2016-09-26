<?php

namespace App\Http\Controllers\Legacy;


class IndexController extends LegacyController
{

    public function request()
    {
        return view('berlussimo');
    }

    public function ajax()
    {
        return response()->legacy('legacy/ajax/ajax_info.php');
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
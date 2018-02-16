<?php

namespace App;

use DB;
use TestCase;

class TestTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testDB()
    {
        $result = DB::select("SELECT *, DATE_FORMAT(VON, '%H:%i') AS VON, DATE_FORMAT(BIS, '%H:%i') AS BIS FROM W_TEAM_PROFILE WHERE BENUTZER_ID='21' && AKTUELL = '1'");
        $this->assertCount(1, $result);
    }
}
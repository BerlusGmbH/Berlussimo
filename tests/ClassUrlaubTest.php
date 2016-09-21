<?php

namespace App;

use TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use urlaub;


class ClassUrlaubTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetMitarbeiterArr()
    {
        $u = new urlaub();
        $users = $u->mitarbeiter_arr(2016);
        $this->assertCount(1, $users);
    }
}
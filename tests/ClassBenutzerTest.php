<?php

namespace App;

use TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use benutzer;


class ClassBenutzerTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetBenutzerID()
    {
        $benutzer = new benutzer();
        $id = $benutzer->get_benutzer_id('admin');
        $this->assertEquals('1', $id);

        $id = $benutzer->get_benutzer_id('test');
        $this->assertNotEquals('1',$id);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetAllUsersArray()
    {
        $benutzer = new benutzer();
        $users = $benutzer->get_all_users_arr();
        $this->assertCount(1, $users);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetAllUsersArray2()
    {
        $benutzer = new benutzer();
        $users = $benutzer->get_all_users_arr2(1);
        $this->assertCount(93, $users);
        $users = $benutzer->get_all_users_arr2(0);
        $this->assertCount(1, $users);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetUserInfo()
    {
        $benutzer = new benutzer();
        $user = $benutzer->get_user_info(1);
        $this->assertEquals(1, $user['id']);
    }
}
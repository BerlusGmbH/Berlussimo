<?php

namespace App;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Hash;

class UserTest extends \TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUser()
    {
        $user = new User();
        $user->name = 'test';
        $user->email = 'test@berlussimo';
        $user->password = Hash::make(str_random(60));
        $user->api_token = str_random(60);
        $user->save();
    }
}

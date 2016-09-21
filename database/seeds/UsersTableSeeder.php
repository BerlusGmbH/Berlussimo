<?php

use Illuminate\Database\Seeder;
use App\Models\User;
//use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = array(
            ['id' => 73, 'name' => 'Cristian Müller', 'email' => 'mueller@berlus.de', 'password' => Hash::make('mueller'), 'api_token' => str_random(60)]
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            User::create($user);
        }
    }
}
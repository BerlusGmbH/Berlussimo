<?php

use App\Models\Person;
use Illuminate\Database\Seeder;

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
            ['id' => 73, 'name' => 'Cristian Müller', 'email' => 'mueller@berlus.de', 'password' => Hash::make('mueller')]
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            Person::create($user);
        }
    }
}
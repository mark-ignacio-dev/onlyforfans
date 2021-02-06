<?php

namespace Database\Seeders;

use Faker\Factory;
use App\Conversation;

class ConversationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Populate dummy conversations
        Conversation::factory()->count(40)->create()
        ->each(function ($conversation) {
            $faker = Factory::create();


            $views = $faker->randomElements(['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20'], 2);

            $users = [2, $faker->numberBetween($min = 1, $max = 38)];
            $conversation->users()->sync($users);

            // $conversation->users()->sync(array('2'));
        });
    }
}

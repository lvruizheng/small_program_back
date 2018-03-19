<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        // $this->call(ProjectSeeder::class);
        // $this->call(TaskSeeder::class);
        $this->call(UserSeeder::class);
    }
}

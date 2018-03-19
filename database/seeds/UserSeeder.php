<?php

use Illuminate\Database\Seeder;
use App\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->name = 'admin';
        $user->password = bcrypt('9ol./;p0');
        $user->is_admin = true;
        $user->is_manager = true;
        $user->is_Operator = true;
        $user->save();
    }
}

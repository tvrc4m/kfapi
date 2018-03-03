<?php

use Illuminate\Database\Seeder;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminUser::create([
            'username' => 'admin',
            'password' => Hash::make('admin'),
            'email' => str_random(10).'@gmail.com',
        ]);
    }
}

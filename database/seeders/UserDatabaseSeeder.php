<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserDatabaseSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    DB::table('users')->insert([
      [
        'name' => 'superadmin',
        'email' => 'niyamulahsan@mail.com',
        'mobile' => '+8801711061131',
        'password' => Hash::make('mowsumii007'),
        'forget_password' => NULL,
        'role_id' => '1',
        'status' => '1',
        'remember_token' => '',
        'created_at' => Carbon::now()
      ],
      [
        'name' => 'admin',
        'email' => 'vatmatebd@gmail.com',
        'mobile' => '+8801719950891',
        'password' => Hash::make('123@sylhet'),
        'forget_password' => '123@sylhet',
        'role_id' => '2',
        'status' => '1',
        'remember_token' => '',
        'created_at' => Carbon::now()
      ]
    ]);
  }
}
